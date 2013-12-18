<?php
namespace Icecave\Engage;

use Icecave\Engage\Detail\Client;
use Icecave\Engage\Detail\Request\Connect;
use Icecave\Recoil\Channel\BidirectionalChannelAdaptor;
use Icecave\Recoil\Channel\Stream\ReadableStreamChannel;
use Icecave\Recoil\Channel\Stream\WritableStreamChannel;
use Icecave\Recoil\Recoil;
use React\ChildProcess\Process;

/**
 * Creates new connections by spawning sub-processes to handle synchronous
 * database access.
 */
class ConnectionFactory
{
    /**
     * [CO-ROUTINE] Create a new connection.
     *
     * @return ConnectionInterface
     */
    public function connect($dsn, $username = null, $password = null, array $driverOptions = [])
    {
        $process = (yield $this->createProcess());
        $strand = (yield Recoil::strand());

        $serviceClient = new Client(
            $strand->kernel(),
            new BidirectionalChannelAdaptor(
                new ReadableStreamChannel($process->stdout),
                new WritableStreamChannel($process->stdin)
            )
        );

        yield $serviceClient->send(
            new Connect($dsn, $username, $password, $driverOptions)
        );

        yield Recoil::return_(
            new Connection($serviceClient)
        );
    }

    protected function createProcess()
    {
        $script = __DIR__ . '/../../../bin/engage-service';

        $process = new Process(
            'php --define display_errors=stderr ' . escapeshellarg($script)
        );

        $process->start(yield Recoil::eventLoop());

        // ----------
        $buffer = '';
        $process->stderr->on('data', function ($data) use ($process, &$buffer) {
            $buffer .= $data;

            $pos = strpos($buffer, PHP_EOL);

            if (false !== $pos) {
                $line = substr($buffer, 0, $pos + 1);
                $buffer = substr($buffer, $pos + 1);
                echo 'ERR [' . $process->getPid() . ']: ' . $line;
            }
        });

        $process->stderr->on('end', function () use ($process, &$buffer) {
            echo 'ERR [' . $process->getPid() . ']: ' . $buffer . PHP_EOL;
        });
        // ----------

        yield Recoil::return_($process);
    }
}
