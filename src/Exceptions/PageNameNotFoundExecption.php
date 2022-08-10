<?php

namespace Mediconesystems\LivewireDatatables\Exceptions;

use Exception;

class PageNameNotFoundException extends Exception
{
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        $message = 'Property [$pageName] or [$name] not found on DataTableComponent ' .
            $message;
        parent::__construct($message, $code, $previous);
    }
}
