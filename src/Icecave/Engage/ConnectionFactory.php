<?php
namespace Icecave\Engage;

use Icecave\Recoil\Channel\BidirectionalChannelAdaptor;
use Icecave\Recoil\Channel\Stream\ReadableStreamChannel;
use Icecave\Recoil\Channel\Stream\WritableStreamChannel;
use Icecave\Recoil\Recoil;
use React\ChildProcess\Process;

class ConnectionFactory
{
    public function create()
    {
        $eventLoop = (yield Recoil::eventLoop());

        $process = $this->createProcess();
        $process->start($eventLoop);

        $process->stderr->on('data', function ($data) {
            echo 'ERR: ' . $data;
        });

        $channel = new BidirectionalChannelAdaptor(
            new ReadableStreamChannel($process->stdout),
            new WritableStreamChannel($process->stdin)
        );

        yield Recoil::return_(
            new Connection($channel)
        );
    }

    protected function createProcess()
    {
        $script = __DIR__ . '/../../../bin/engage-service';

        return new Process(
            'php --define display_errors=stderr ' . escapeshellarg($script)
        );
    }
}
