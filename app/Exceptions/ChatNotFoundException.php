<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Support\Facades\Log;

final class ChatNotFoundException extends Exception
{
    public function __construct(string $message = "Chat not found", int $code = 500, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    public function report(): void
    {
        Log::error(
            message: $this->message,
            context: ['response' => $this->getMessage()]
        );
    }
}
