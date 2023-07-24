<?php

namespace App\Controller;

use App\Entity\Room;
use App\Entity\User;
use App\Exception\RoomClosedException;
use App\Exception\UserAlreadyInRoomException;
use App\Exception\UserNotFoundException;
use App\Repository\RoomRepository;
use App\Repository\UserRepository;
use App\Service\UserMessagePublisher;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

class RoomController extends AbstractController
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly RoomRepository $roomRepository,
        private readonly RequestStack $requestStack,
        private readonly UserMessagePublisher $userMessagePublisher,
        private readonly SerializerInterface $serializer,
    )
    {
    }

    /**
     * @throws UserNotFoundException
     * @throws RoomClosedException
     */
    #[Route('/room/{id}', name: 'room_index')]
    public function index(
        Room $room,
    ): Response
    {
        $userId = $this->requestStack->getSession()->get('user_id');
        if ($userId === null) {
            $this->createAccessDeniedException('User not found!');
        }

        if ($room->isClosed()) {
            throw new RoomClosedException();
        }

        $user = $this->userRepository->findById($userId);

        try {
            $room->addUser($user);
            $this->roomRepository->save($room);

            $this->userMessagePublisher->sendRoomUsers($room);
        } catch (UserAlreadyInRoomException) {}

        return $this->render('room/index.html.twig', [
            'controller_name' => 'RoomController',
            'user' => $user,
            'room_id' => $room->getId(),
            'owner_id' => $room->getOwner()->getId(),
            'users' => $this->serializer->serialize($room->getUsers()->toArray(), 'json', [
                AbstractNormalizer::IGNORED_ATTRIBUTES => ['rooms']
            ])
        ]);
    }

    #[Route('/room/{id}/users/{userId}', name: 'room_remove_user', methods: ['DELETE'])]
    public function removeUser(
        Room $room,
        #[MapEntity(expr: 'repository.find(userId)')]
        User $user,
    ): Response
    {
        $currentUserId = $this->requestStack->getSession()->get('user_id');
        if ($currentUserId === null || ($currentUserId !== $user->getId() && $currentUserId !== $room->getOwner()->getId())) {
            throw new AccessDeniedHttpException();
        }

        if ($user->getId() === $room->getOwner()->getId()) {
            $this->closeRoom($room);
        } else {
            $this->removeUserFromRoom($room, $user, $user->getId() === $currentUserId);
        }

        return new Response();
    }

    private function closeRoom(Room $room): void
    {
        $room->setIsClosed(true);
        $this->roomRepository->save($room);

        $this->userMessagePublisher->sendRoomClosed($room);
    }

    private function removeUserFromRoom(Room $room, User $user, bool $isInitiatedByUser): void
    {
        $room->removeUser($user);
        $this->roomRepository->save($room);

        $this->userMessagePublisher->sendExitRequest($room, $user, $isInitiatedByUser);
        $this->userMessagePublisher->sendRoomUsers($room);
    }
}
