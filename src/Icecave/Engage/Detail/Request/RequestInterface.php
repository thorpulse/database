<?php
namespace Icecave\Engage\Detail\Request;

use Icecave\Engage\Detail\Service;

interface RequestInterface
{
    public function execute(Service $service);

    public function isNotification();
}
