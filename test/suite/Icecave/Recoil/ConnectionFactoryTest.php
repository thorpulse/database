<?php
namespace Icecave\Recoil\Database;

use Icecave\Recoil\Channel\BidirectionalChannelInterface;
use Icecave\Recoil\Database\Detail\Connection;
use Icecave\Recoil\Recoil;
use Phake;
use PHPUnit_Framework_TestCase;
use React\ChildProcess\Process;
use React\EventLoop\StreamSelectLoop;

class ConnectionFactoryTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->factory = Phake::partialMock(ConnectionFactory::CLASS);
    }

    public function testConnect()
    {
        Recoil::run(
            function () {
                $connection = (yield $this->factory->connect('sqlite::memory:'));

                $this->assertInstanceOf(Connection::CLASS, $connection);

                $connection->__destruct(); // Mocked factory is holding reference to connection.
            }
        );
    }

    public function testConnectLogic()
    {
        $process = Phake::mock(Process::CLASS);
        $channel = Phake::mock(BidirectionalChannelInterface::CLASS);
        $connection = Phake::mock(Connection::CLASS);

        Phake::when($this->factory)
            ->createProcess(Phake::anyParameters())
            ->thenReturn($process);

        Phake::when($this->factory)
            ->createChannel(Phake::anyParameters())
            ->thenReturn($channel);

        Phake::when($this->factory)
            ->createConnection(Phake::anyParameters())
            ->thenReturn($connection);

        $eventLoop = new StreamSelectLoop;

        Recoil::run(
            function () use ($connection) {
                $con = (yield $this->factory->connect(
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
            Phake::verify($this->factory)->createProcess(),
            Phake::verify($process)->start($eventLoop),
            Phake::verify($this->factory)->createChannel($process),
            Phake::verify($this->factory)->createConnection($channel),
            Phake::verify($connection)->connect(
                'dsn',
                'username',
                'password',
                ['driver' => 'options']
            )
        );

    }

    // public function testConnectFunctional()
    // {
    //     Recoil::run(
    //         function () {
    //             $connection = (yield $this->factory->connect('sqlite::memory:'));

    //             $this->assertInstanceOf(ConnectionInterface::CLASS, $connection);
    //         }
    //     );
    // }

    // public function testConnect()
    // {
    //     $process = Phake::mock(Process::CLASS);

    //     Phake::when($this->factory)
    //         ->createServiceProcess()
    //         ->thenGetResultByLambda(
    //             function () use ($process) {
    //                 yield Recoil::return_($process);
    //             }
    //         );

    //     Phake::when($this->factory)
    //         ->createServiceProcess()
    //         ->thenGetResultByLambda(
    //             function () use ($process) {
    //                 yield Recoil::return_($process);
    //             }
    //         );

    //     $coroutine = function () {
    //         $connection = (yield $this->factory->connect('sqlite::memory:'));

    //         $this->assertInstanceOf(ConnectionInterface::CLASS, $connection);
    //     };

    //     $connection = $this->factory->connect(
    //         'dsn',
    //         'username',
    //         'password',
    //         ['driver' => 'options']
    //     );

    //     $this->kernel->execute($coroutine());
    //     $this->kernel->eventLoop()->run();
    // }
}
