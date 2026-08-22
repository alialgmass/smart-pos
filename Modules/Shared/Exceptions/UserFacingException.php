<?php

namespace Modules\Shared\Exceptions;

use RuntimeException;

class UserFacingException extends RuntimeException
{
    public function __construct(
        string $message,
        protected readonly int $status = 422,
    ) {
        parent::__construct($message, $status);
    }

    public function status(): int
    {
        return $this->status;
    }
}
