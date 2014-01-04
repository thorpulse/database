<?php
namespace Recoil\Database;

use Recoil\Channel\BidirectionalChannelInterface;
use Recoil\Database\Detail\Connection;
use Recoil\Recoil;
use Phake;
use PHPUnit_Framework_TestCase;
use React\ChildProcess\Process;
use React\EventLoop\StreamSelectLoop;

class ConnectionFactoryTest extends PHPUnit_Framework_TestCase
{
    public function testConnect()
    {
        Recoil::run(
            function () {
                $factory = new ConnectionFactory;
                $connection = (yield $factory->connect('sqlite::memory:'));

                $this->assertInstanceOf(Connection::CLASS, $connection);
            }
        );
    }

    public function testConnectLogic()
    {
        $factory = Phake::partialMock(ConnectionFactory::CLASS);
        $process = Phake::mock(Process::CLASS);
        $channel = Phake::mock(BidirectionalChannelInterface::CLASS);
        $connection = Phake::mock(Connection::CLASS);

        Phake::when($factory)
            ->createProcess(Phake::anyParameters())
            ->thenReturn($process);

        Phake::when($factory)
            ->createChannel(Phake::anyParameters())
            ->thenReturn($channel);

        Phake::when($factory)
            ->createConnection(Phake::anyParameters())
            ->thenReturn($connection);

        $eventLoop = new StreamSelectLoop;

        Recoil::run(
            function () use ($factory, $connection) {
                $con = (yield $factory->connect(
                    'dsn',
                    'username',
                    'password',
                    ['driver' => 'options']
                ));

                $this->assertSame($connection, $con);
            },
            $eventLoop
        );

        Phake::inOrder(
            Phake::verify($factory)->createProcess(),
            Phake::verify($process)->start($eventLoop),
            Phake::verify($factory)->createChannel($process),
            Phake::verify($factory)->createConnection($channel),
            Phake::verify($connection)->connect(
                'dsn',
                'username',
                'password',
                ['driver' => 'options']
            )
        );

    }
}
