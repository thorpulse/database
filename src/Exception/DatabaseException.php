<?php
namespace Recoil\Database\Exception;

use Exception;
use PDOException;

class DatabaseException extends PDOException
{
    /**
     * @param string      $message   The error message.
     * @param string|null $code      The error code.
     * @param array|null  $errorInfo The PDO error information, if available.
     */
    public function __construct(
        $message,
        $code = null,
        $errorInfo = null
    ) {
        if (null === $code) {
            $code = 0;
        }

        parent::__construct($message, $code);

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
