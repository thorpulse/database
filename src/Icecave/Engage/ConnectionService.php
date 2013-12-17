<?php
namespace Icecave\Engage;

use Exception;
use Icecave\Engage\Exception\PdoException;
use Icecave\Recoil\Channel\BidirectionalChannelInterface;
use PDO;
use PDOException as NativePdoException;

class ConnectionService
{
    public function __construct(BidirectionalChannelInterface $channel)
    {
        $this->channel = $channel;
    }

    public function run()
    {
        do {
            list($method, $arguments) = $x = (yield $this->channel->read());

            fwrite(STDERR, 'CALL: ' . $method . '(' . json_encode($arguments) . ')' . PHP_EOL);

            $response = $this->dispatch($method, $arguments);

            fwrite(STDERR, 'RESPOND: ' . json_encode($response) . PHP_EOL);

            yield $this->channel->write($response);
        } while ($this->connection);

        yield $this->channel->close();
    }

    public function handleConnect($dsn, $username = null, $password = null, $driverOptions = null)
    {
        $this->connection = new PDO($dsn, $username, $password, $driverOptions);
    }

    public function handleDisconnect()
    {
        $this->connection = null;
    }

    protected function dispatch($method, array $arguments)
    {
        if ($method === 'connect') {
            $method = [$this, 'handleConnect'];
        } elseif ($method === 'disconnect') {
            $method = [$this, 'handleDisconnect'];
        } elseif (!$this->connection) {
            return [
                null,
                [
                    PdoException::CLASS,
                    [
                        'Connection has not been established.',
                        '08003',
                    ]
                ]
            ];
        } else {
            $method = [$this->connection, $method];
        }

        try {
            $value = call_user_func_array($method, $arguments);
        } catch (NativePdoException $e) {
            return [
                null,
                [
                    PdoException::CLASS,
                    [
                        $e->getMessage(),
                        $e->getCode(),
                        $e->errorInfo,
                    ]
                ]
            ];
        } catch (Exception $e) {
            return [
                null,
                [
                    get_class($e),
                    [
                        $e->getMessage(),
                        $e->getCode(),
                    ]
                ]
            ];
        }

        if (is_array($value)) {
            return [$value, null];
        }

        return $value;
    }

    private $channel;
    private $connection;
}
