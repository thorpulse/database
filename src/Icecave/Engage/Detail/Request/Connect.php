<?php
namespace Icecave\Engage\Detail\Request;

use Icecave\Engage\Detail\Service;

class Connect implements RequestInterface
{
    public function __construct($dsn, $username = null, $password = null, array $driverOptions = null)
    {
        $this->dsn = $dsn;
        $this->username = $username;
        $this->password = $password;
        $this->driverOptions = $driverOptions;
    }

    public function execute(Service $service)
    {
        $service->connect(
            $this->dsn,
            $this->username,
            $this->password,
            $this->driverOptions
        );
    }

    public function isNotification()
    {
        return false;
    }
}
