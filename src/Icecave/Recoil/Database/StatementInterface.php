<?php
namespace Icecave\Recoil\Database;

use PDO;

interface StatementInterface
{
    /**
     * [COROUTINE] Execute the prepared statement.
     *
     * @link http://php.net/pdostatement.execute
     *
     * @param array $inputParameters Values to be bind to query placeholders.
     *
     * @return boolean True if prepared statement was executed successfully.
     */
    public function execute(array $inputParameters = []);

    /**
     * [COROUTINE] Set the default fetch mode for this statement.
     *
     * @link http://php.net/pdostatement.setfetchmode
     *
     * @param integer            $mode                 The fetch mode (one of the PDO::FETCH_* constants).
     * @param string|object|null $fetchArgument        The class name for PDO::FETCH_CLASS, or object for PDO::FETCH_OBJECT.
     * @param array|null         $constructorArguments The constructor arguments for PDO::FETCH_OBJECT.
     *
     * @return boolean True if the fetch mode is set successfully.
     */
    public function setFetchMode($mode, $fetchArgument = null, array $constructorArguments = null);

    /**
     * [COROUTINE] Fetch the next result in the rowset.
     *
     * @link http://php.net/pdostatement.fetch
     *
     * @param integer|null $mode              The fetch mode (one of the PDO::FETCH_* constants), or null to use the default.
     * @param integer      $cursorOrientation The cursor orientation (one of the PDO::FETCH_ORI_* constants).
     * @param integer      $cursorOffset      The result index for PDO::FETCH_ORI_ABS or offset for PDO::FETCH_ORI_REL.
     *
     * @return mixed The return type depends on the fetch mode, in all cases false is returned on failure.
     */
    public function fetch($mode = null, $cursorOrientation = PDO::FETCH_ORI_NEXT, $cursorOffset = 0);

    /**
     * [COROUTINE] Fetch the next result in the rowset as an object.
     *
     * @link http://php.net/pdostatement.fetchobject
     *
     * @param string $className            The name of the class to use to represent the result.
     * @param array  $constructorArguments Arguments to pass to the class constructor.
     *
     * @return object|boolean Return an instance of the given class representing the next row, or false on failure.
     */
    public function fetchObject($className = 'stdClass', array $constructorArguments = []);

    /**
     * [COROUTINE] Fetch all remaining results.
     *
     * @link http://php.net/pdostatement.fetchall
     *
     * @param integer|null       $mode                 The fetch mode (one of the PDO::FETCH_* constants), or null to use the default.
     * @param string|object|null $fetchArgument        The class name for PDO::FETCH_CLASS, or object for PDO::FETCH_OBJECT.
     * @param array|null         $constructorArguments The constructor arguments for PDO::FETCH_OBJECT.
     *
     * @return array An array containing all of the remaining rows in the result set.
     */
    public function fetchAll($mode = null, $fetchArguments = null, array $constructorArguments = null);

    /**
     * [COROUTINE] Return a single column from the next row in the result set.
     *
     * @link http://php.net/pdostatement.fetchcolumn
     *
     * @param integer $columnIndex The 0-based column index.
     *
     * @return mixed The column value, or false on failure.
     */
    public function fetchColumn($columnIndex = 0);

    /**
     * [COROUTINE] Bind a value to a named or positional placeholder.
     *
     * @link http://php.net/pdostatement.bindvalue
     *
     * @param integer|string $parameter The parameter name, or 1-based index for positional placeholders.
     * @param mixed          $value     The value to bind to the parameter.
     * @param integer        $dataType  The data-type of the parameter.
     *
     * @return boolean True if the value is bound successfully.
     */
    public function bindValue($parameter, $value, $dataType = PDO::PARAM_STR);

    /**
     * [COROUTINE] Bind a variable reference to a named or positional placeholder.
     *
     * @link http://php.net/pdostatement.bindparam
     *
     * @param integer|string $parameter     The parameter name, or 1-based index for positional placeholders.
     * @param mixed          &$value        The value to bind to the parameter.
     * @param integer        $dataType      The data-type of the parameter.
     * @param integer|null   $length        The length of the data-type.
     * @param mixed          $driverOptions Driver specific binding options.
     *
     * @return boolean True if the variable is bound successfully.
     */
    public function bindParam($parameter, &$value, $dataType = PDO::PARAM_STR, $length = null, $driverOptions = null);

    /**
     * [COROUTINE] Bind a column to a variable reference.
     *
     * @link http://php.net/pdostatement.bindcolumn
     *
     * @param string|integer $column        The column name or 1-based column index.
     * @param mixed          &$value        The value to bind to the parameter.
     * @param integer        $dataType      The data-type of the parameter.
     * @param integer|null   $length        The length of the data-type.
     * @param mixed          $driverOptions Driver specific binding options.
     *
     * @return boolean True if the column is bound successfully.
     */
    public function bindColumn($column, &$value, $dataType = null, $length = null, $driverOptions = null);

    /**
     * [COROUTINE] Fetch the number of columns in the rowset.
     *
     * @link http://php.net/pdostatement.columncount
     *
     * @return integer The number of columns in the rowset.
     */
    public function columnCount();

    /**
     * [COROUTINE] Fetch the number of rows affected by the last execution.
     *
     * Returns the number of rows affected by the last INSERT, UPDATE or DELETE
     * statement. Some drivers may return the number of rows returned by the
     * last SELECT statement, but this should not be relied upon.
     *
     * @link http://php.net/pdostatement.rowCount
     *
     * @return integer The number of columns in the rowset.
     */
    public function rowCount();

    /**
     * [COROUTINE] Retrieve meta-data about a column in the result.
     *
     * @link http://php.net/pdostatement.getcolumnmeta
     *
     * @param integer $columnIndex The 0-based column index.
     *
     * @return array Meta-data about the column at the specified index.
     */
    public function getColumnMeta($columnIndex);

    /**
     * [COROUTINE] Advance to the next rowset in a multi-rowset statement.
     *
     * @link http://php.net/pdostatement.nextrowset
     *
     * @return boolean True if successful; otherwise, false.
     */
    public function nextRowset();

    /**
     * [COROUTINE] Close the cursor, allowing the statement to be executed again.
     *
     * @link http://php.net/pdostatement.closecursor
     *
     * @return boolean True if successful; otherwise, false.
     */
    public function closeCursor();

    /**
     * [COROUTINE] Fetch the query string used by the statement.
     *
     * @return string The query string used by the statement.
     */
    public function queryString();

    /**
     * [COROUTINE] Set the value of an attribute.
     *
     * @link http://php.net/pdostatement.setattribute
     *
     * @param integer $attribute The attribute to set.
     * @param mixed   $value     The value to set the attribute to.
     *
     * @return boolean      True if the attribute was successfully set.
     * @throws PDOException If the attribute could not be set.
     */
    public function setAttribute($attribute, $value);

    /**
     * [COROUTINE] Get the value of an attribute.
     *
     * @link http://php.net/pdostatement.getattribute
     *
     * @param integer $attribute The attribute to get.
     *
     * @return mixed        The attribute value.
     * @throws PDOException If the attribute could not be read.
     */
    public function getAttribute($attribute);

    /**
     * [COROUTINE] Get the most recent status code for this statement.
     *
     * @link http://php.net/pdostatement.errorcode
     *
     * @return string|null The status code, or null if this statement has not been executed.
     */
    public function errorCode();

    /**
     * [COROUTINE] Get status information about the last operation performed on
     * this statement.
     *
     * For details of the status information returned, see the PHP manual entry
     * for PDOStatement::errorInfo().
     *
     * @link http://php.net/pdostatement.errorinfo
     *
     * @return array The status information.
     */
    public function errorInfo();

    /**
     * [COROUTINE] Dump debug information about the prepared statement to STDOUT.
     *
     * @link http://php.net/pdostatement.debugdumpparams
     */
    public function debugDumpParams();
}
