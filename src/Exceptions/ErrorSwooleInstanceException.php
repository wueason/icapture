<?php

namespace Icapture\Exceptions;

class ErrorSwooleInstanceException extends IcaptureException
{
    public function __construct($message, $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
