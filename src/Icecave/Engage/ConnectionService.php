<?php
namespace Icecave\Engage;

use Exception;
use Icecave\Engage\Exception\PdoException;
use Icecave\Recoil\Channel\BidirectionalChannelInterface;
use PDO;
use PDOException as NativePdoException;

/**
 * Responds to RPC calls from the parent process, and dispatches them to a real
 * PDO connection.
 */
class ConnectionService
{
    /**
     * @param BidirectionalChannelInterface $channel The channel used for RPC communication.
     */
    public function __construct(BidirectionalChannelInterface $channel)
    {
        $this->channel = $channel;
    }

    /**
     * [CO-ROUTINE]
     */
    public function run()
    {
        do {
            list($object, $method, $arguments) = $x = (yield $this->channel->read());

            fwrite(STDERR, 'CALL: ' . $method . '(' . json_encode($arguments) . ')' . PHP_EOL);

            $response = $this->dispatch($object, $method, $arguments);

            fwrite(STDERR, 'RESPOND: ' . json_encode($response) . PHP_EOL);

            yield $this->channel->write($response);
        } while ($this->connection);

        yield $this->channel->close();
    }

    /**
     * Establish the database connection.
     */
    public function handleConnect($dsn, $username = null, $password = null, $driverOptions = null)
    {
        $this->connection = new PDO($dsn, $username, $password, $driverOptions);
    }

    /**
     * Disconnect the connection.
     */
    public function handleDisconnect()
    {
        $this->connection = null;
    }

    /**
     * Dispatch an RPC.
     *
     * @param string $method
     * @param array  $arguments
     *
     * @return mixed
     */
    protected function dispatch($object, $method, array $arguments)
    {
        $value = null;

        try {
            if ($method === 'connect') {
                list($dsn, $username, $password, $driverOptions) = $arguments;
                $this->handleConnect($dsn, $username, $password, $driverOptions);
            } elseif ($method === 'disconnect') {
                $this->handleDisconnect();
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
                $value = call_user_func_array($method, $arguments);
            }

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

        return [$value, null];
    }

    private $channel;
    private $connection;
    private $statements;
}
