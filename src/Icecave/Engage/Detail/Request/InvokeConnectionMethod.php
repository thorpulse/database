<?php
namespace Icecave\Engage\Detail\Request;

use Icecave\Engage\Detail\Service;
use PDOStatement;


class InvokeConnectionMethod implements RequestInterface
{
    public function __construct($method, array $arguments = [])
    {
        $this->method = $method;
        $this->arguments = $arguments;
    }

    public function execute(Service $service)
    {
        $result = call_user_func_array(
            [$service->connection(), $this->method],
            $this->arguments
        );

        if ($result instanceof PDOStatement) {
            $result = $service->addStatement($result);
        }

        return $result;
    }

    public function isNotification()
    {
        return false;
    }

    private $method;
    private $arguments;
}
