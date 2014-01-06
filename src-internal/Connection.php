<?php
namespace Recoil\Database\Detail;

use Recoil\Channel\BidirectionalChannelInterface;
use Recoil\Database\ConnectionInterface;
use Recoil\Database\Exception\DatabaseException;
use Recoil\Recoil;
use PDO;

/**
 * An asynchronous PDO-like database connection.
 */
class Connection implements ConnectionInterface
{
    public function __construct(BidirectionalChannelInterface $channel)
    {
        $this->channel = $channel;
    }

    public function __destruct()
    {
        if ($this->kernel && !$this->channel->isClosed()) {
            $this->kernel->execute(
                $this->channel->write([0, 'disconnect'])
            );
        }
    }

    public function channel()
    {
        return $this->channel;
    }

    public function kernel()
    {
        return $this->kernel;
    }

    /**
     * [COROUTINE] Establish the database connection.
     *
     * The parameters are the same as {@see PDO::__construct()}.
     *
     * @param string      $dsn           The data-source name for the connection.
     * @param string|null $username      The username to use for the DSN.
     * @param string|null $password      The password to use for the DSN.
     * @param array|null  $driverOptions Driver-specific configuration options.
     */
    public function connect($dsn, $username = null, $password = null, array $driverOptions = null)
    {
        $this->kernel = (yield Recoil::kernel());

        yield $this->serviceRequest(__FUNCTION__, func_get_args());
    }

    /**
     * [COROUTINE] Prepare an SQL statement to be executed.
     *
     * @link http://php.net/pdo.prepare
     *
     * @param string               $statement  The statement to prepare.
     * @param array<integer,mixed> $attributes The connection attributes to use.
     *
     * @return StatementInterface The prepared PDO statement.
     */
    public function prepare($statement, $attributes = [])
    {
        return $this->serviceRequest(__FUNCTION__, func_get_args());
    }

    /**
     * [COROUTINE] Execute an SQL statement and return the result set.
     *
     * @link http://php.net/pdo.query
     *
     * @param string             $statement            The statement to execute.
     * @param integer|null       $mode                 The fetch mode (one of the PDO::FETCH_* constants), or null to use the default.
     * @param string|object|null $fetchArgument        The class name for PDO::FETCH_CLASS, or object for PDO::FETCH_OBJECT.
     * @param array|null         $constructorArguments The constructor arguments for PDO::FETCH_OBJECT.
     *
     * @return StatementInterface The result set.
     */
    public function query($statement, $mode = null, $fetchArgument = null, array $constructorArguments = null)
    {
        $statement = (yield $this->serviceRequest(__FUNCTION__, [$statement]));

        if ($statement && null !== $mode) {
            yield $statement->setFetchMode($mode, $fetchArgument, $constructorArguments);
        }

        yield Recoil::return_($statement);
    // @codeCoverageIgnoreStart
    }
    // @codeCoverageIgnoreEnd

    /**
     * [COROUTINE] Execute an SQL statement and return the number of rows
     * affected.
     *
     * @link http://php.net/pdo.exec
     *
     * @param string $statement The statement to execute.
     *
     * @return integer The number of affected rows.
     */
    public function exec($statement)
    {
        return $this->serviceRequest(__FUNCTION__, func_get_args());
    }

    /**
     * [COROUTINE] Returns true if there is an active transaction.
     *
     * @link http://php.net/pdo.intransaction
     *
     * @return boolean True if there is an active transaction.
     */
    public function inTransaction()
    {
        return $this->serviceRequest(__FUNCTION__);
    }

    /**
     * [COROUTINE] Start a transation.
     *
     * @link http://php.net/pdo.begintransaction
     *
     * @return boolean True if a transaction was started.
     */
    public function beginTransaction()
    {
        return $this->serviceRequest(__FUNCTION__);
    }

    /**
     * [COROUTINE] Commit the active transaction.
     *
     * @link http://php.net/pdo.commit
     *
     * @return boolean True if the transaction was successfully committed.
     */
    public function commit()
    {
        return $this->serviceRequest(__FUNCTION__);
    }

    /**
     * [COROUTINE] Roll back the active transaction.
     *
     * @link http://php.net/pdo.rollback
     *
     * @return boolean True if the transaction was successfully rolled back.
     */
    public function rollBack()
    {
        return $this->serviceRequest(__FUNCTION__);
    }

    /**
     * [COROUTINE] Get the ID of the last inserted row.
     *
     * @link http://php.net/pdo.lastinsertid
     *
     * @param string|null $name The name of the sequence object to query.
     *
     * @return string The last inserted ID.
     */
    public function lastInsertId($name = null)
    {
        return $this->serviceRequest(__FUNCTION__, func_get_args());
    }

    /**
     * [COROUTINE] Get the most recent status code for this connection.
     *
     * @link http://php.net/pdo.errorcode
     *
     * @return string|null The status code, or null if no statement has been run on this connection.
     */
    public function errorCode()
    {
        return $this->serviceRequest(__FUNCTION__);
    }

    /**
     * [COROUTINE] Get status information about the last operation performed on
     * this connection.
     *
     * For details of the status information returned, see the PHP manual entry
     * for PDO::errorInfo().
     *
     * @link http://php.net/pdo.errorinfo
     *
     * @return array The status information.
     */
    public function errorInfo()
    {
        return $this->serviceRequest(__FUNCTION__);
    }

    /**
     * [COROUTINE] Quotes a string using an appropriate quoting style for the
     * underlying driver.
     *
     * @link http://php.net/pdo.quote
     *
     * @param string  $string        The string to quote.
     * @param integer $parameterType The parameter type.
     *
     * @return string The quoted string.
     */
    public function quote($string, $parameterType = PDO::PARAM_STR)
    {
        return $this->serviceRequest(__FUNCTION__, func_get_args());
    }

    /**
     * [COROUTINE] Set the value of an attribute.
     *
     * @link http://php.net/pdo.setattribute
     *
     * @param integer $attribute The attribute to set.
     * @param mixed   $value     The value to set the attribute to.
     *
     * @return boolean True if the attribute was successfully set.
     */
    public function setAttribute($attribute, $value)
    {
        return $this->serviceRequest(__FUNCTION__, func_get_args());
    }

    /**
     * [COROUTINE] Get the value of an attribute.
     *
     * @link http://php.net/pdo.getattribute
     *
     * @param integer $attribute The attribute to get.
     *
     * @return mixed The attribute value.
     */
    public function getAttribute($attribute)
    {
        return $this->serviceRequest(__FUNCTION__, func_get_args());
    }

    private function serviceRequest($method, array $arguments = [])
    {
        $request = [0, $method];

        if ($arguments) {
            $request[] = $arguments;
        }

        yield $this->channel->write($request);

        $response = (yield $this->channel->read());

        switch ($response[0]) {
            case ResponseType::VALUE:
                yield Recoil::return_($response[1]);

            case ResponseType::STATEMENT:
                yield Recoil::return_(new Statement($this, $response[1]));
        }

        throw new DatabaseException($response[1], $response[2], $response[3]);
    }

    private $channel;
    private $kernel;
}
