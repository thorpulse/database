<?php
namespace Icecave\Engage\Detail;

use Icecave\Engage\Detail\Request\ReleaseConnection;
use Icecave\Engage\Detail\Request\ReleaseStatement;
use Icecave\Engage\Detail\Request\RequestInterface;
use Icecave\Recoil\Channel\BidirectionalChannelInterface;
use Icecave\Recoil\Kernel\KernelInterface;
use Icecave\Recoil\Recoil;
use LogicException;

class Client
{
    public function __construct(KernelInterface $kernel, BidirectionalChannelInterface $channel)
    {
        $this->kernel  = $kernel;
        $this->channel = $channel;
    }

    public function kernel()
    {
        return $this->kernel;
    }

    public function channel()
    {
        return $this->channel;
    }

    public function send(RequestInterface $request)
    {
        if (!$this->kernel) {
            throw new LogicException('Client is disconnected.');
        }

        yield $this->channel->write($request);

        $response = (yield $this->channel->read());

        yield Recoil::return_(
            $response->get($this)
        );
    }

    public function releaseConnection()
    {
        $this->kernel->execute(
            $this->channel->write(new ReleaseConnection)
        );

        $this->kernel = null;
    }

    public function releaseStatement($statementId)
    {
        if (!$this->kernel) {
            return;
        }

        $this->kernel->execute(
            $this->channel->write(new ReleaseStatement($statementId))
        );
    }

    private $kernel;
    private $channel;
}
