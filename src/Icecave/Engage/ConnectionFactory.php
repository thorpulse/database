<?php
namespace Icecave\Engage;

use Icecave\Engage\Detail\Client;
use Icecave\Engage\Detail\Request\Connect;
use Icecave\Recoil\Channel\BidirectionalChannelAdaptor;
use Icecave\Recoil\Channel\ReadableStreamChannel;
use Icecave\Recoil\Channel\WritableStreamChannel;
use Icecave\Recoil\Recoil;
use Icecave\Recoil\Stream\ReadableReactStream;
use Icecave\Recoil\Stream\WritableReactStream;
use React\ChildProcess\Process;

/**
 * Creates new connections by spawning sub-processes to handle synchronous
 * database access.
 */
class ConnectionFactory
{
    /**
     * @param string|null $commandLine The command line used to execute the engage service, or null to use the default.
     */
    public function __construct($commandLine = null)
    {
        if (null === $commandLine) {
            $script = realpath(__DIR__ . '/../../../bin/engage-service');
            $commandLine = 'php --define display_errors=stderr ' . escapeshellarg($script);
        }

        $this->commandLine = $commandLine;
    }

    /**
     * [CO-ROUTINE] Create a new connection.
     *
     * @param string $dsn The data-source name for the connection.
     * @param string|null $username The username to use for the DSN.
     * @param string|null $password The password to use for the DSN.
     * @param array|null $driverOptions Driver-specific configuration options.
     *
     * @return ConnectionInterface The database connection.
     */
    public function connect($dsn, $username = null, $password = null, array $driverOptions = [])
    {
        $serviceProcess = (yield $this->startServiceProcess());
        $serviceClient  = (yield $this->createServiceClient($serviceProcess));

        yield $serviceClient->send(
            new Connect($dsn, $username, $password, $driverOptions)
        );

        yield Recoil::return_(
            new Connection($serviceClient)
        );
    }

    protected function startServiceProcess()
    {
        $process = new Process($this->commandLine);
        $process->start(yield Recoil::eventLoop());

        yield Recoil::return_($process);
    }

    protected function createServiceClient(Process $serviceProcess)
    {
        yield Recoil::return_(
            new Client(
                (yield Recoil::kernel()),
                new BidirectionalChannelAdaptor(
                    new ReadableStreamChannel(new ReadableReactStream($serviceProcess->stdout)),
                    new WritableStreamChannel(new WritableReactStream($serviceProcess->stdin))
                )
            )
        );
    }

    private $commandLine;
}



        // // ----------
        // $buffer = '';
        // $process->stderr->on('data', function ($data) use ($process, &$buffer) {
        //     $buffer .= $data;

        //     $pos = strpos($buffer, PHP_EOL);

        //     if (false !== $pos) {
        //         $line = substr($buffer, 0, $pos + 1);
        //         $buffer = substr($buffer, $pos + 1);
        //         echo 'ERR [' . $process->getPid() . ']: ' . $line;
        //     }
        // });

        // $process->stderr->on('end', function () use ($process, &$buffer) {
        //     echo 'ERR [' . $process->getPid() . ']: ' . $buffer . PHP_EOL;
        // });
        // // ----------

        // yield Recoil::return_($process);
