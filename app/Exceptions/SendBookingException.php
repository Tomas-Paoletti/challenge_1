<?php
namespace App\Exceptions;

use Exception;

class SendBookingException extends Exception
{

    public function __construct($message = 'Error sending booking email', $code = 500, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
