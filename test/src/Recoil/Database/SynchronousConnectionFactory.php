<?php
namespace Recoil\Database;

use Recoil\Channel\BidirectionalChannelAdaptor;
use Recoil\Channel\Channel;
use Recoil\Database\Detail\Connection;
use Recoil\Database\Detail\ServiceManager;
use Recoil\Recoil;

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
