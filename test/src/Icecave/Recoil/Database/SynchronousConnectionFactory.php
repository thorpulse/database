<?php
namespace Icecave\Recoil\Database;

use Icecave\Recoil\Channel\BidirectionalChannelAdaptor;
use Icecave\Recoil\Channel\Channel;
use Icecave\Recoil\Database\Detail\Connection;
use Icecave\Recoil\Database\Detail\ServiceManager;
use Icecave\Recoil\Recoil;

class SynchronousConnectionFactory implements ConnectionFactoryInterface
{
    public function connect($dsn, $username = null, $password = null, array $driverOptions = [])
    {
        $a = new Channel;
        $b = new Channel;

        $serviceManager = new ServiceManager(
            new BidirectionalChannelAdaptor($a, $b)
        );

        $connection = new Connection(
            new BidirectionalChannelAdaptor($b, $a)
        );

        yield Recoil::execute($serviceManager());
        yield $connection->connect($dsn, $username, $password, $driverOptions);
        yield Recoil::return_($connection);
    // @codeCoverageIgnoreStart
    }
    // @codeCoverageIgnoreEnd
}
