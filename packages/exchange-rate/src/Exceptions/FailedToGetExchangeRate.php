<?php

namespace AlhajiAki\ExchangeRate\Exceptions;

use Exception;

final class FailedToGetExchangeRate extends Exception
{
    public static function because(string $reason): self
    {
        return new static($reason);
    }
}
