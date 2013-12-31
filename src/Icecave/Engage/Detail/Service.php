<?php
namespace Icecave\Engage\Detail;

use Exception;
use Icecave\Engage\Detail\Response\ExceptionResponse;
use Icecave\Engage\Detail\Response\ValueResponse;
use Icecave\Engage\Exception\PdoException;
use Icecave\Recoil\Channel\BidirectionalChannelInterface;
use LogicException;
use PDO;
use PDOStatement;

class Service
{
    /**
     * @param BidirectionalChannelInterface $channel The channel used for RPC communication.
     */
    public function __construct(BidirectionalChannelInterface $channel)
    {
        $this->channel = $channel;
        $this->statements = [];
    }

    public function connect($dsn, $username = null, $password = null, array $driverOptions = null)
    {
        $this->connection = new PDO($dsn, $username, $password, $driverOptions);
    }

    public function disconnect()
    {
        $this->connection = null;
    }

    public function connection()
    {
        if (!$this->connection) {
            throw new PdoException('Connection has not been established.', '08003');
        }

        return $this->connection;
    }

    public function addStatement(PDOStatement $statement)
    {
        $statementId = spl_object_hash($statement);

        $this->statements[$statementId] = $statement;

        return $statementId;
    }

    public function removeStatement(PDOStatement $statement)
    {
        $statementId = spl_object_hash($statement);

        unset($this->statements[$statementId]);
    }

    public function getStatement($statementId)
    {
        return $this->statements[$statementId];
    }

    public function __invoke()
    {
        do {
            $request = (yield $this->channel->read());

            try {
                $response = new ValueResponse(
                    $request->execute($this)
                );
            } catch (Exception $e) {
                $response = new ExceptionResponse($e);
            }

            yield $this->channel->write($response);

        } while ($this->connection);

        yield $this->channel->close();
    }

    private $channel;
    private $connection;
    private $statements;
}
