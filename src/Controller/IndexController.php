<?php

namespace App\Controller;

use App\Dto\UserLoginDto;
use App\Entity\Room;
use App\Entity\User;
use App\Exception\UserNotFoundException;
use App\Normalizer\UserLoginDtoDenormalizer;
use App\Repository\RoomRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class IndexController extends AbstractController
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly RoomRepository $roomRepository,
        private readonly ValidatorInterface $validator,
        private readonly RequestStack $requestStack,
    )
    {
    }

    #[Route('/', name: 'app_index')]
    public function index(
        #[MapQueryParameter(filter: FILTER_VALIDATE_INT)] ?int $roomId
    ): Response
    {
        return $this->render('index/index.html.twig', [
            'controller_name' => 'IndexController',
            'room_id' => $roomId,
        ]);
    }

    #[Route('/login', name: 'app_login', methods: 'POST')]
    public function login(
        #[MapRequestPayload(
            serializationContext: [UserLoginDtoDenormalizer::class]
        )] UserLoginDto $userLoginDto
    ): Response
    {
        $userId = $userLoginDto->user_id;
        if ($userId === null) {
            $userId = $this->saveUser($userLoginDto->username);
        }

        try {
            $user = $this->userRepository->findById($userId);
        } catch (UserNotFoundException) {
            throw new HttpException(
                Response::HTTP_INTERNAL_SERVER_ERROR,
                sprintf('Error saving/retrieving the user "%s".', $userLoginDto->username)
            );
        }

        $roomId = $userLoginDto->room_id;
        if ($roomId === null) {
            $roomId = $this->saveRoom($user);
        }

        $this->requestStack->getSession()->set('username', $user->getUsername());
        $this->requestStack->getSession()->set('user_id', $user->getId());

        return new RedirectResponse(
            sprintf('/room/%d', $roomId)
        );
    }

    /**
     * @throws UnprocessableEntityHttpException
     */
    private function saveUser(string $username): int
    {
        $userObj = new User();
        $userObj->setUsername($username);

        $errors = $this->validator->validate($userObj);
        if (count($errors) > 0) {
            throw new UnprocessableEntityHttpException('Cannot create user: ' . $errors);
        }

        return $this->userRepository->save($userObj);
    }

    /**
     * @throws UnprocessableEntityHttpException
     */
    private function saveRoom(User $user): int
    {
        $roomObj = new Room();
        $roomObj->setOwner($user);
        $roomObj->setIsClosed(false);

        $errors = $this->validator->validate($roomObj);
        if (count($errors) > 0) {
            throw new UnprocessableEntityHttpException('Cannot create room: ' . $errors);
        }

        return $this->roomRepository->save($roomObj);
    }
}
