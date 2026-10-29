<?php

namespace App\Exceptions;

use RuntimeException;

class PharmacyOperationException extends RuntimeException
{
    public function __construct(
        string $message,
        public readonly int $statusCode = 409,
    ) {
        parent::__construct($message);
    }
}
