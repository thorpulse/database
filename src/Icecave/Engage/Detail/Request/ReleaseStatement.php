<?php
namespace Icecave\Engage\Detail\Request;

use Icecave\Engage\Detail\Service;

class ReleaseStatement implements RequestInterface
{
    public function __construct($statementId)
    {
        $this->statementId = null;
    }

    public function execute(Service $service)
    {
        $statement = $service->getStatement($this->statementId);
        $statement->closeCursor();

        $service->removeStatement($statement);
    }

    public function isNotification()
    {
        return true;
    }

    private $statementId;
}
