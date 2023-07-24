<?php

namespace App\Exception;

use Exception;
use Psr\Log\LogLevel;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\WithHttpStatus;
use Symfony\Component\HttpKernel\Attribute\WithLogLevel;

#[WithHttpStatus(Response::HTTP_FORBIDDEN)]
#[WithLogLevel(LogLevel::ERROR)]
class RoomClosedException extends Exception
{
}