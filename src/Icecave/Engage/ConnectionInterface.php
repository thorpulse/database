<?php
namespace Icecave\Engage;

use PDO;
use PDOException;

/**
 * An asynchronous PDO-like database connection.
 */
interface ConnectionInterface
{
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
    public function prepare($statement, $attributes = []);

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
    public function query($statement);

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
    public function exec($statement);

    /**
     * Returns true if there is an active transaction.
     *
     * @link http://php.net/pdo.intransaction
     *
     * @return boolean True if there is an active transaction.
     */
    public function inTransaction();

    /**
     * [CO-ROUTINE] Start a transation.
     *
     * @link http://php.net/pdo.begintransaction
     *
     * @return boolean      True if a transaction was started.
     * @throws PDOException If the transaction cannot be started.
     */
    public function beginTransaction();

    /**
     * [CO-ROUTINE] Commit the active transaction.
     *
     * @link http://php.net/pdo.commit
     *
     * @return boolean      True if the transaction was successfully committed.
     * @throws PDOException If the transaction cannot be committed.
     */
    public function commit();

    /**
     * [CO-ROUTINE] Roll back the active transaction.
     *
     * @link http://php.net/pdo.rollback
     *
     * @return boolean      True if the transaction was successfully rolled back.
     * @throws PDOException If the transaction cannot be rolled back.
     */
    public function rollBack();

    /**
     * [CO-ROUTINE] Get the ID of the last inserted row.
     *
     * @link http://php.net/pdo.lastinsertid
     *
     * @param string|null $name The name of the sequence object to query.
     *
     * @return string The last inserted ID.
     */
    public function lastInsertId($name = null);

    /**
     * [CO-ROUTINE] Get the most recent status code for this connection.
     *
     * @link http://php.net/pdo.errorcode
     *
     * @return string|null The status code, or null if no statement has been run on this connection.
     */
    public function errorCode();

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
    public function errorInfo();

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
    public function quote($string, $parameterType = PDO::PARAM_STR);

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
    public function setAttribute($attribute, $value);

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
    public function getAttribute($attribute);
}
