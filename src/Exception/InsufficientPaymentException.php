<?php

namespace App\Exception;

use Exception;

final class InsufficientPaymentException extends Exception
{
    public function __construct(string $message = "Insufficient payment amount", int $code = 0, ?Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
