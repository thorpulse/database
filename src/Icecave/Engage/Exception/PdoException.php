<?php
namespace Icecave\Engage\Exception;

use Exception;
use PDOException as NativePdoException;

class PdoException extends NativePdoException
{
    /**
     * Construct a new PDO exception.
     *
     * @param string         $message   The message.
     * @param string|null    $code      The code.
     * @param array|null     $errorInfo The error information.
     * @param Exception|null $previous  The cause, if available.
     */
    public function __construct(
        $message,
        $code = null,
        $errorInfo = null,
        Exception $previous = null
    ) {
        if (null === $code) {
            $code = 0;
        }

        parent::__construct($message, 0, $previous);

        if (null === $errorInfo) {
            if (0 === $code) {
                $errorInfo = null;
            } else {
                $errorInfo = [$code, $code, $message];
            }
        }

        $this->code = $code;
        $this->errorInfo = $errorInfo;
    }

    public $errorInfo;
}
