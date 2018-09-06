<?php

namespace Coconuts\Mail\Exceptions;

use Exception;

class PostmarkException extends Exception
{
    public static function secretNotSet()
    {
        return new static('The Postmark secret is not set. Make sure that the `postmark.secret` config key is set.');
    }
}
