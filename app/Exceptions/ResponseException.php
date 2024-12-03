<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Support\Facades\Log;

final class ResponseException extends Exception
{
    public function __construct(
        string $message = 'Response data is invalid. Response must contain key "message" or "callback_data"',
        int $code = 500,
        Exception $previous = null
    ) {
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
