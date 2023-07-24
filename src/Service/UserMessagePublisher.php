<?php

namespace App\Service;

use App\Entity\Room;
use App\Entity\User;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

readonly class UserMessagePublisher
{
    public function __construct(
        private HubInterface $hub,
        private SerializerInterface $serializer,
    )
    {
    }

    public function sendRoomUsers(Room $room): void
    {
        $update = new Update(
            sprintf('room-%d', $room->getId()),
            $this->serializer->serialize(
                [
                    'type' => 'room.users',
                    'data' => $room->getUsers()->toArray()
                ], 'json', [
                AbstractNormalizer::IGNORED_ATTRIBUTES => ['rooms']
            ]),
        );

        $this->hub->publish($update);
    }

    public function sendRoomClosed(Room $room): void
    {
        $update = new Update(
            sprintf('room-%d', $room->getId()),
            json_encode([
                'type' => 'room.close',
                'data' => sprintf('The room %d has been closed!', $room->getId()),
            ]),
        );

        $this->hub->publish($update);
    }

    public function sendExitRequest(Room $room, User $user, bool $isInitiatedByUser): void
    {
        $message = $isInitiatedByUser ? 'You left room #%d!' : 'You have been kicked from room #%d!';

        $update = new Update(
            sprintf('room-%d-%d', $room->getId(), $user->getId()),
            sprintf($message, $room->getId()),
            true,
        );

        $this->hub->publish($update);
    }
}
