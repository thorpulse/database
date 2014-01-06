<?php
namespace Recoil\Database;

use PDO;

/**
 * An asynchronous PDO-like database connection.
 */
interface ConnectionInterface
{
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
    public function prepare($statement, $attributes = []);

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
    public function query($statement, $mode = null, $fetchArgument = null, array $constructorArguments = null);

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
    public function exec($statement);

    /**
     * [COROUTINE] Returns true if there is an active transaction.
     *
     * @link http://php.net/pdo.intransaction
     *
     * @return boolean True if there is an active transaction.
     */
    public function inTransaction();

    /**
     * [COROUTINE] Start a transation.
     *
     * @link http://php.net/pdo.begintransaction
     *
     * @return boolean True if a transaction was started.
     */
    public function beginTransaction();

    /**
     * [COROUTINE] Commit the active transaction.
     *
     * @link http://php.net/pdo.commit
     *
     * @return boolean True if the transaction was successfully committed.
     */
    public function commit();

    /**
     * [COROUTINE] Roll back the active transaction.
     *
     * @link http://php.net/pdo.rollback
     *
     * @return boolean True if the transaction was successfully rolled back.
     */
    public function rollBack();

    /**
     * [COROUTINE] Get the ID of the last inserted row.
     *
     * @link http://php.net/pdo.lastinsertid
     *
     * @param string|null $name The name of the sequence object to query.
     *
     * @return string The last inserted ID.
     */
    public function lastInsertId($name = null);

    /**
     * [COROUTINE] Get the most recent status code for this connection.
     *
     * @link http://php.net/pdo.errorcode
     *
     * @return string|null The status code, or null if no statement has been run on this connection.
     */
    public function errorCode();

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
    public function errorInfo();

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
    public function quote($string, $parameterType = PDO::PARAM_STR);

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
    public function setAttribute($attribute, $value);

    /**
     * [COROUTINE] Get the value of an attribute.
     *
     * @link http://php.net/pdo.getattribute
     *
     * @param integer $attribute The attribute to get.
     *
     * @return mixed The attribute value.
     */
    public function getAttribute($attribute);
}
