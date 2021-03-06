<?php
namespace Recoil\Database;

use Recoil\Channel\BidirectionalChannelAdaptor;
use Recoil\Channel\BidirectionalChannelInterface;
use Recoil\Channel\ReadableStreamChannel;
use Recoil\Channel\WritableStreamChannel;
use Recoil\Database\Detail\Connection;
use Recoil\Recoil;
use Recoil\Stream\ReadableReactStream;
use Recoil\Stream\WritableReactStream;
use React\ChildProcess\Process;

/**
 * Creates new connections by spawning sub-processes to handle synchronous
 * database access.
 */
class ConnectionFactory
{
    /**
     * @param string|null $commandLine The command line used to execute the database service, or null to use the default.
     */
    public function __construct($commandLine = null)
    {
        if (null === $commandLine) {
            $commandLine = 'php --define display_errors=stderr ' . escapeshellarg(__DIR__ . '/../bin/database-service');
        }

        $this->commandLine = $commandLine;
    }

    /**
     * [COROUTINE] Establish a database connection.
     *
     * The parameters are the same as {@see PDO::__construct()}.
     *
     * @param string      $dsn           The data-source name for the connection.
     * @param string|null $username      The username to use for the DSN.
     * @param string|null $password      The password to use for the DSN.
     * @param array|null  $driverOptions Driver-specific configuration options.
     *
     * @return ConnectionInterface The database connection.
     */
    public function connect($dsn, $username = null, $password = null, array $driverOptions = [])
    {
        $process = $this->createProcess();
        $process->start(yield Recoil::eventLoop());
        $channel = $this->createChannel($process);
        $connection = $this->createConnection($channel);

        // $process->stderr->on(
        //     'data',
        //     function ($data) {
        //         if ($data) {
        //             echo 'ERR: ' . $data;
        //             ob_flush();
        //         }
        //     }
        // );

        // $process->stdout->on(
        //     'data',
        //     function ($data) {
        //         if ($data) {
        //             echo 'OUT: ' . $data;
        //             ob_flush();
        //         }
        //     }
        // );

        yield $connection->connect($dsn, $username, $password, $driverOptions);
        yield Recoil::return_($connection);
    // @codeCoverageIgnoreStart
    }
    // @codeCoverageIgnoreEnd

    protected function createProcess()
    {
        return new Process($this->commandLine);
    }

    protected function createChannel(Process $process)
    {
        return new BidirectionalChannelAdaptor(
            new ReadableStreamChannel(
                new ReadableReactStream($process->stdout)
            ),
            new WritableStreamChannel(
                new WritableReactStream($process->stdin)
            )
        );
    }

    protected function createConnection(BidirectionalChannelInterface $channel)
    {
        return new Connection($channel);
    }

    private $commandLine;
}
