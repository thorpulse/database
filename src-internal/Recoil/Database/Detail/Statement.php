<?php
namespace Recoil\Database\Detail;

use Recoil\Database\Exception\DatabaseException;
use Recoil\Database\StatementInterface;
use Recoil\Recoil;
use PDO;
use ReflectionClass;

class Statement implements StatementInterface
{
    public function __construct(Connection $connection, $objectId)
    {
        $this->connection = $connection;
        $this->objectId = $objectId;
        $this->defaultFetchMode = null;
    }

    public function __destruct()
    {
        $channel = $this->connection->channel();

        if (!$channel->isClosed()) {
            $this->connection->kernel()->execute(
                $channel->write([$this->objectId, 'release'])
            );
        }
    }

    /**
     * [COROUTINE] Execute the prepared statement.
     *
     * @param array $inputParameters Values to be bind to query placeholders.
     *
     * @return boolean True if prepared statement was executed successfully.
     */
    public function execute(array $inputParameters = [])
    {
        return $this->serviceRequest(__FUNCTION__, func_get_args());
    }

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
    public function setFetchMode($mode, $fetchArgument = null, array $constructorArguments = null)
    {
        $this->defaultFetchMode = null;
        $this->defaultFetchArgument = null;
        $this->defaultFetchConstructorArguments = null;

        switch ($mode) {
            case PDO::FETCH_ASSOC:
            case PDO::FETCH_BOTH:
            case PDO::FETCH_NAMED:
            case PDO::FETCH_NUM:
            case PDO::FETCH_OBJ:
                yield $this->serviceRequest(__FUNCTION__, [$mode]);
                break;

            case PDO::FETCH_CLASS:
                $this->defaultFetchConstructorArguments = $constructorArguments;
            case PDO::FETCH_INTO:
                $this->defaultFetchArgument = $fetchArgument;
            case PDO::FETCH_BOUND:
            case PDO::FETCH_LAZY:
                $this->defaultFetchMode = $mode;
                yield $this->serviceRequest(__FUNCTION__, [PDO::FETCH_BOTH]);
                break;
        }
    }

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
    public function fetch($mode = null, $cursorOrientation = PDO::FETCH_ORI_NEXT, $cursorOffset = 0)
    {
        if (null === $mode) {
            $mode = $this->defaultFetchMode;
        }

        switch ($mode) {
            case PDO::FETCH_CLASS:
            case PDO::FETCH_INTO:
            case PDO::FETCH_BOUND:
            case PDO::FETCH_LAZY:
                throw new \LogicException('The current fetch mode is not yet supported.');
        }

        return $this->serviceRequest(__FUNCTION__, [$mode, $cursorOrientation, $cursorOffset]);
    }

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
    public function fetchObject($className = 'stdClass', array $constructorArguments = [])
    {
        $reflector = new ReflectionClass($className);
        $object = $reflector->newInstanceWithoutConstructor();

        $values = (yield $this->fetch(PDO::FETCH_ASSOC));

        if (false === $values) {
            yield Recoil::return_(false);
        }

        foreach ($values as $key => $value) {
            $object->{$key} = $value;
        }

        if ($constructor = $reflector->getConstructor()) {
            $constructor->invokeArgs($object, $constructorArguments);
        }

        yield Recoil::return_($object);
    }

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
    public function fetchAll($mode = null, $fetchArgument = null, array $constructorArguments = null)
    {
        if (null === $mode) {
            $mode = $this->defaultFetchMode;
        }

        switch ($mode) {
            case PDO::FETCH_CLASS:
            case PDO::FETCH_INTO:
            case PDO::FETCH_BOUND:
            case PDO::FETCH_LAZY:
                throw new \LogicException('The current fetch mode is not yet supported.');
        }

        return $this->serviceRequest(__FUNCTION__, func_get_args());
    }

    /**
     * [COROUTINE] Return a single column from the next row in the result set.
     *
     * @link http://php.net/pdostatement.fetchcolumn
     *
     * @param integer $columnIndex The 0-based column index.
     *
     * @return mixed The column value, or false on failure.
     */
    public function fetchColumn($columnIndex = 0)
    {
        return $this->serviceRequest(__FUNCTION__, func_get_args());
    }

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
    public function bindValue($parameter, $value, $dataType = PDO::PARAM_STR)
    {
        return $this->serviceRequest(__FUNCTION__, func_get_args());
    }

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
    public function bindParam($parameter, &$value, $dataType = PDO::PARAM_STR, $length = null, $driverOptions = null)
    {
        throw new \LogicException('Not implemented');
    }

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
    public function bindColumn($column, &$value, $dataType = null, $length = null, $driverOptions = null)
    {
        throw new \LogicException('Not implemented');
    }

    /**
     * [COROUTINE] Fetch the number of columns in the rowset.
     *
     * @link http://php.net/pdostatement.columncount
     *
     * @return integer The number of columns in the rowset.
     */
    public function columnCount()
    {
        return $this->serviceRequest(__FUNCTION__);
    }

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
    public function rowCount()
    {
        return $this->serviceRequest(__FUNCTION__);
    }

    /**
     * [COROUTINE] Retrieve meta-data about a column in the result.
     *
     * @link http://php.net/pdostatement.getcolumnmeta
     *
     * @param integer $columnIndex The 0-based column index.
     *
     * @return array Meta-data about the column at the specified index.
     */
    public function getColumnMeta($columnIndex)
    {
        return $this->serviceRequest(__FUNCTION__, func_get_args());
    }

    /**
     * [COROUTINE] Advance to the next rowset in a multi-rowset statement.
     *
     * @link http://php.net/pdostatement.nextrowset
     *
     * @return boolean True if successful; otherwise, false.
     */
    public function nextRowset()
    {
        return $this->serviceRequest(__FUNCTION__);
    }

    /**
     * [COROUTINE] Close the cursor, allowing the statement to be executed again.
     *
     * @link http://php.net/pdostatement.closecursor
     *
     * @return boolean True if successful; otherwise, false.
     */
    public function closeCursor()
    {
        return $this->serviceRequest(__FUNCTION__);
    }

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
    public function setAttribute($attribute, $value)
    {
        return $this->serviceRequest(__FUNCTION__, func_get_args());
    }

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
    public function getAttribute($attribute)
    {
        return $this->serviceRequest(__FUNCTION__, func_get_args());
    }

    /**
     * [COROUTINE] Get the most recent status code for this statement.
     *
     * @link http://php.net/pdostatement.errorcode
     *
     * @return string|null The status code, or null if this statement has not been executed.
     */
    public function errorCode()
    {
        return $this->serviceRequest(__FUNCTION__);
    }

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
    public function errorInfo()
    {
        return $this->serviceRequest(__FUNCTION__);
    }

    /**
     * [COROUTINE] Fetch the query string used by the statement.
     *
     * @return string The query string used by the statement.
     */
    public function queryString()
    {
        return $this->serviceRequest(__FUNCTION__);
    }

    /**
     * [COROUTINE] Dump debug information about the prepared statement to STDOUT.
     *
     * @link http://php.net/pdostatement.debugdumpparams
     */
    public function debugDumpParams()
    {
        echo (yield $this->serviceRequest(__FUNCTION__));
    }

    private function serviceRequest($method, array $arguments = [])
    {
        $request = [$this->objectId, $method];

        if ($arguments) {
            $request[] = $arguments;
        }

        yield $this->connection->channel()->write($request);
        $response = (yield $this->connection->channel()->read());

        switch ($response[0]) {
            case ResponseType::VALUE:
                yield Recoil::return_($response[1]);

            case ResponseType::EXCEPTION:
                throw new DatabaseException($response[1], $response[2], $response[3]);
        }

        throw new RuntimeException('Invalid response type.');
    }

    private $connection;
    private $objectId;
    private $defaultFetchMode;
    private $defaultFetchArgument;
    private $defaultFetchConstructorArguments;
}
