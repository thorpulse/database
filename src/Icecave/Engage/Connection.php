<?php
namespace Icecave\Engage;

use Icecave\Recoil\Channel\BidirectionalChannelInterface;
use Icecave\Recoil\Recoil;
use PDO;
use PDOException;
use ReflectionClass;

/**
 * An asynchronous PDO-like database connection.
 */
class Connection implements ConnectionInterface
{
    /**
     * @param BidirectionalChannelInterface $channel The channel used for RPC communication.
     */
    public function __construct(BidirectionalChannelInterface $channel)
    {
        $this->channel = $channel;
    }

    public function __destruct()
    {
        if ($this->kernel) {
            $this->kernel->execute($this->disconnect());
        }
    }

    /**
     * [CO-ROUTINE] Establish a connection.
     *
     * @param string      $dsn           The data source name.
     * @param string|null $username      The username for the DSN, this parameter is optional for some drivers.
     * @param string|null $password      The password for the DSN, this parameter is optional for some drivers.
     * @param array|null  $driverOptions An associative array of driver-specific connection options.
     */
    public function connect($dsn, $username = null, $password = null, array $driverOptions = null)
    {
        if ($this->kernel) {
            return;
        }

        yield $this->rpc(
            'connect',
            [
                $dsn,
                $username,
                $password,
                $driverOptions,
            ]
        );

        $strand = (yield Recoil::strand());

        $this->kernel = $strand->kernel();
    }

    /**
     * [CO-ROUTINE] Disconnect from the database.
     */
    public function disconnect()
    {
        if (!$this->kernel) {
            return;
        }

        yield $this->rpc('disconnect');

        $this->kernel = null;
    }

    /**
     * [CO-ROUTINE] Prepare an SQL statement to be executed.
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
        $statementId = (yield $this->rpc('prepare', [$statement, $attributes]));

        yield Recoil::return_(
            new Statement($this->channel, $statementId)
        );
    }

    /**
     * [CO-ROUTINE] Execute an SQL statement and return the result set.
     *
     * There are a number of valid ways to call this method. See the PHP manual
     * entry for PDO::query() for more information.
     *
     * @link http://php.net/pdo.query
     *
     * @param string $statement The statement to execute.
     * @param mixed $argument,... Arguments.
     *
     * @return StatementInterface The result set.
     * @throws PDOException       If the statement cannot be executed.
     */
    public function query($statement)
    {
        $statementId = (yield $this->rpc('query', $statement));

        $statement = new Statement($this->channel, $statementId);

        if (func_num_args() > 1) {
            call_user_func_array(
                [$statement, 'setFetchMode'],
                array_slice(func_get_args(), 1)
            );
        }

        yield Recoil::return_($statement);
    }

    /**
     * [CO-ROUTINE] Execute an SQL statement and return the number of rows
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
        return $this->rpc('exec', [$statement]);
    }

    /**
     * Returns true if there is an active transaction.
     *
     * @link http://php.net/pdo.intransaction
     *
     * @return boolean True if there is an active transaction.
     */
    public function inTransaction()
    {
        return $this->rpc('inTransaction');
    }

    /**
     * [CO-ROUTINE] Start a transation.
     *
     * @link http://php.net/pdo.begintransaction
     *
     * @return boolean      True if a transaction was started.
     * @throws PDOException If the transaction cannot be started.
     */
    public function beginTransaction()
    {
        return $this->rpc('beginTransaction');
    }

    /**
     * [CO-ROUTINE] Commit the active transaction.
     *
     * @link http://php.net/pdo.commit
     *
     * @return boolean      True if the transaction was successfully committed.
     * @throws PDOException If the transaction cannot be committed.
     */
    public function commit()
    {
        return $this->rpc('commit');
    }

    /**
     * [CO-ROUTINE] Roll back the active transaction.
     *
     * @link http://php.net/pdo.rollback
     *
     * @return boolean      True if the transaction was successfully rolled back.
     * @throws PDOException If the transaction cannot be rolled back.
     */
    public function rollBack()
    {
        return $this->rpc('rollBack');
    }

    /**
     * [CO-ROUTINE] Get the ID of the last inserted row.
     *
     * @link http://php.net/pdo.lastinsertid
     *
     * @param string|null $name The name of the sequence object to query.
     *
     * @return string The last inserted ID.
     */
    public function lastInsertId($name = null)
    {
        return $this->rpc('lastInsertId', [$name]);
    }

    /**
     * [CO-ROUTINE] Get the most recent status code for this connection.
     *
     * @link http://php.net/pdo.errorcode
     *
     * @return string|null The status code, or null if no statement has been run on this connection.
     */
    public function errorCode()
    {
        return $this->rpc('errorCode');
    }

    /**
     * [CO-ROUTINE] Get status information about the last operation performed on
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
        return $this->rpc('errorInfo');
    }

    /**
     * [CO-ROUTINE] Quotes a string using an appropriate quoting style for the
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
        return $this->rpc('quote', [$string, $parameterType]);
    }

    /**
     * [CO-ROUTINE] Set the value of an attribute.
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
        return $this->rpc('setAttribute', [$attribute, $value]);
    }

    /**
     * [CO-ROUTINE] Get the value of an attribute.
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
        return $this->rpc('getAttribute', [$attribute]);
    }

    protected function rpc($name, array $arguments = [])
    {
        yield $this->channel->write(
            [
                'connection',
                $name,
                $arguments
            ]
        );

        list($value, $error) = $x = (yield $this->channel->read());

        if (null === $error) {
            yield Recoil::return_($value);
        }

        list($class, $arguments) = $error;

        $reflector = new ReflectionClass($class);

        throw $reflector->newInstanceArgs($arguments);
    }

    private $channel;
    private $dsn;
    private $username;
    private $password;
    private $driverOptions;
    private $kernel;
}
