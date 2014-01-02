<?php
namespace Icecave\Recoil\Database\Detail;

use Icecave\Recoil\Channel\BidirectionalChannelInterface;
use Icecave\Recoil\Database\ConnectionInterface;
use Icecave\Recoil\Database\Exception\DatabaseException;
use Icecave\Recoil\Recoil;
use PDO;
use PDOException;

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
     * @throws PDOException       If the statement cannot be prepared.
     */
    public function prepare($statement, $attributes = [])
    {
        return $this->serviceRequest(__FUNCTION__, func_get_args());
    }

    /**
     * [COROUTINE] Execute an SQL statement and return the result set.
     *
     * There are a number of valid ways to call this method. See the PHP manual
     * entry for PDO::query() for more information.
     *
     * @link http://php.net/pdo.query
     *
     * @param string $statement    The statement to execute.
     * @param mixed  $argument,... Arguments.
     *
     * @return StatementInterface The result set.
     * @throws PDOException       If the statement cannot be executed.
     */
    public function query($statement)
    {
        $statement = (yield $this->serviceRequest(__FUNCTION__, [$statement]));

        if ($statement && func_num_args() > 1) {
            yield call_user_func_array(
                [$statement, 'setFetchMode'],
                array_slice(func_get_args(), 1)
            );
        }

        yield Recoil::return_($statement);
    }

    /**
     * [COROUTINE] Execute an SQL statement and return the number of rows
     * affected.
     *
     * @link http://php.net/pdo.exec
     *
     * @param string $statement The statement to execute.
     *
     * @return integer      The number of affected rows.
     * @throws PDOException If the statement cannot be executed.
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
     * @return boolean      True if a transaction was started.
     * @throws PDOException If the transaction cannot be started.
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
     * @return boolean      True if the transaction was successfully committed.
     * @throws PDOException If the transaction cannot be committed.
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
     * @return boolean      True if the transaction was successfully rolled back.
     * @throws PDOException If the transaction cannot be rolled back.
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
     * @return string       The quoted string.
     * @throws PDOException If the parameter type is not supported.
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
     * @return boolean      True if the attribute was successfully set.
     * @throws PDOException If the attribute could not be set.
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
     * @return mixed        The attribute value.
     * @throws PDOException If the attribute could not be read.
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

            case ResponseType::EXCEPTION:
                throw new DatabaseException($response[1], $response[2], $response[3]);
        }

        throw new RuntimeException('Invalid response type.');
    }

    private $channel;
    private $kernel;
}
