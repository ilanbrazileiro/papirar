<?php

namespace App\Exceptions;

use RuntimeException;

class GeminiExtractionException extends RuntimeException
{
    public function __construct(
        string $message,
        public readonly ?int $httpStatus = null,
        public readonly ?string $errorCode = null,
        public readonly bool $quotaExceeded = false,
        public readonly bool $temporarilyUnavailable = false,
    ) {
        parent::__construct($message);
    }
}
