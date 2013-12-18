<?php
namespace Icecave\Engage\Detail\Request;

use Icecave\Engage\Detail\Service;

class ReleaseConnection implements RequestInterface
{
    public function execute(Service $service)
    {
        $service->disconnect();
    }

    public function isNotification()
    {
        return true;
    }
}
