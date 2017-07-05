<?php
namespace AgileGeeks\Rotld;

class RotldApiException extends \Exception
{

    private $error_message;
    private $error_code;

    public function __construct($error_message = null, $error_code = null)
    {
        $this->error_message = $error_message;
        $this->error_code = $error_code;
    }

    public function getErrorMessage()
    {
        return $this->error_message;
    }

    public function getErrorCode()
    {
        return $this->error_code;
    }
}
