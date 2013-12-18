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
        $statement = $service->getStatement($this->statementId);

        if ($this->method === 'debugDumpParams') {
            ob_start();
            $statement->debugDumpParams();
            $output = ob_get_contents();
            ob_end_clean();

            return $output;
        }

        return call_user_func_array(
            [$statement, $this->method],
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
