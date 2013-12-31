<?php
namespace Icecave\Engage;

use Icecave\Recoil\Kernel\Kernel;
use Icecave\Recoil\Recoil;
use Phake;
use PHPUnit_Framework_TestCase;
use React\ChildProcess\Process;

class ConnectionFactoryTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->factory = Phake::partialMock(ConnectionFactory::CLASS);
    }

    public function testConnectFunctional()
    {
        Recoil::run(
            function () {
                $connection = (yield $this->factory->connect('sqlite::memory:'));

                $this->assertInstanceOf(ConnectionInterface::CLASS, $connection);
            }
        );
    }

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
