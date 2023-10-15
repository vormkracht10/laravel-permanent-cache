<?php

namespace Vormkracht10\PermanentCache\Exceptions;

use Exception;

class CouldNotUpdatePermanentCache extends Exception
{
    public static function make($class, Exception $exception): self
    {
        return new self(
            message: "Could not update permanent cache for `{$class->getName()}` did not complete. An exception was thrown with this message: `".get_class($exception).": {$exception->getMessage()}`",
            previous: $exception,
        );
    }
}
