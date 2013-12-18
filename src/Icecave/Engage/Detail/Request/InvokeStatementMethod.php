<?php
namespace Icecave\Engage\Detail\Request;

use Icecave\Engage\Detail\Service;

class InvokeStatementMethod implements RequestInterface
{
    public function __construct($statementId, $method, array $arguments = [])
    {
        $this->statementId = $statementId;
        $this->method = $method;
        $this->arguments = $arguments;
    }

    public function execute(Service $service)
    {
        return call_user_func_array(
            [$service->getStatement($this->statementId), $this->method],
            $this->arguments
        );
    }

    public function isNotification()
    {
        return false;
    }

    private $method;
    private $arguments;
}
