<?php

namespace App\Exception;

use Exception;

final class InvalidCandyTypeException extends Exception
{
    public function __construct(string $candyType, int $code = 0, ?Exception $previous = null)
    {
        $message = "Invalid candy type: {$candyType}";
        parent::__construct($message, $code, $previous);
    }
}
