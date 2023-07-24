<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints\NotBlank;

final readonly class UserLoginDto
{
    public function __construct(
        #[NotBlank]
        public string $username,
        public ?int $user_id,
        public ?int $room_id,
    )
    {
    }
}
