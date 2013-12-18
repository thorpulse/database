<?php
namespace Icecave\Engage\Detail\Response;

use Exception;
use PDOException;
use ReflectionClass;

class ExceptionResponse implements ResponseInterface
{
    public function __construct(Exception $e)
    {
        if ($e instanceof PDOException) {
            $this->className = 'Icecave\Engage\Exception\PdoException';
            $this->arguments = [$e->getMessage(), $e->getCode(), $e->errorInfo];
        } else {
            $this->className = 'RuntimeException';
            $this->arguments = [$e->getMessage(), $e->getCode()];
        }
    }

    public function get()
    {
        $reflector = new ReflectionClass($this->className);

        throw $reflector->newInstanceArgs($this->arguments);
    }

    private $className;
    private $arguments;
}
