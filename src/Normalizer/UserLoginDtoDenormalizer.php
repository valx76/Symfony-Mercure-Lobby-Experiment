<?php

namespace App\Normalizer;

use App\Dto\UserLoginDto;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class UserLoginDtoDenormalizer implements DenormalizerInterface
{
    public function denormalize(mixed $data, string $type, string $format = null, array $context = []): UserLoginDto
    {
        $username = $data['username'];
        $user_id = array_key_exists('user_id', $data) && is_numeric($data['user_id']) ? intval($data['user_id']) : null;
        $room_id = array_key_exists('room_id', $data) && is_numeric($data['room_id']) ? intval($data['room_id']) : null;

        return new UserLoginDto($username, $user_id, $room_id);
    }

    public function supportsDenormalization(mixed $data, string $type, string $format = null): bool
    {
        return $type === UserLoginDto::class;
    }
}
