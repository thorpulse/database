<?php
namespace Recoil\Database\Detail;

use LogicException;
use PDO;
use Recoil\Database\Exception\DatabaseException;
use Recoil\Database\StatementInterface;
use Recoil\Recoil;
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
        $this->defaultFetchMode = $mode;
        $this->defaultFetchArgument = null;
        $this->defaultFetchConstructorArguments = null;

        switch ($mode) {
            case PDO::FETCH_CLASS:
                $this->defaultFetchArgument = new ReflectionClass($fetchArgument);
                $this->defaultFetchConstructorArguments = $constructorArguments;
                break;

            case PDO::FETCH_INTO:
                if (!is_object($fetchArgument)) {
                    throw new InvalidArgumentException('Fetch argument must be an object.');
                }

                $this->defaultFetchArgument = $fetchArgument;
                break;

            case PDO::FETCH_BOUND:
            case PDO::FETCH_LAZY:
                throw new LogicException('The current fetch mode is not yet supported.');
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
            case PDO::FETCH_BOUND:
            case PDO::FETCH_LAZY:
                throw new \LogicException('The current fetch mode is not yet supported.');

            case PDO::FETCH_CLASS:
                return $this->fetchClassLogic($cursorOrientation, $cursorOffset);

            case PDO::FETCH_INTO:
                return $this->fetchIntoLogic($cursorOrientation, $cursorOffset);
        }

        return $this->serviceRequest(
            __FUNCTION__,
            [$mode, $cursorOrientation, $cursorOffset]
        );
    }

    private function fetchClassLogic($cursorOrientation, $cursorOffset)
    {
        if (PDO::FETCH_CLASS !== $this->defaultFetchMode) {
            throw new LogicException(
                'PDO::FETCH_CLASS must be configured as the DEFAULT fetch mode in order to use PDO::FETCH_CLASS.'
            );
        }

        $result = (yield $this->fetch(PDO::FETCH_ASSOC, $cursorOrientation, $cursorOffset));

        if ($result) {
            $result = $this->createObject(
                $result,
                $this->defaultFetchArgument,
                $this->defaultFetchConstructorArguments
            );
        }

        yield Recoil::return_($result);
    // @codeCoverageIgnoreStart
    }
    // @codeCoverageIgnoreEnd

    private function fetchIntoLogic($cursorOrientation, $cursorOffset)
    {
        if (PDO::FETCH_INTO !== $this->defaultFetchMode) {
            throw new LogicException(
                'PDO::FETCH_INTO must be configured as the DEFAULT fetch mode in order to use PDO::FETCH_INTO.'
            );
        }

        $result = (yield $this->fetch(PDO::FETCH_ASSOC, $cursorOrientation, $cursorOffset));

        if ($result) {
            $this->populateObject(
                $result,
                $this->defaultFetchArgument
            );

            $result = $this->defaultFetchArgument;
        }

        yield Recoil::return_($result);
    // @codeCoverageIgnoreStart
    }
    // @codeCoverageIgnoreEnd

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
        $result = (yield $this->fetch(PDO::FETCH_ASSOC));

        if ($result) {
            $result = $this->createObject(
                $result,
                new ReflectionClass($className),
                $constructorArguments
            );
        }

        yield Recoil::return_($result);
    // @codeCoverageIgnoreStart
    }
    // @codeCoverageIgnoreEnd

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
            case PDO::FETCH_INTO:
            case PDO::FETCH_BOUND:
            case PDO::FETCH_LAZY:
                throw new LogicException('The current fetch mode is not yet supported.');

            case PDO::FETCH_CLASS:
                return $this->fetchAllClassLogic($fetchArgument, $constructorArguments);
        }

        return $this->serviceRequest(__FUNCTION__, [$mode]);
    }

    public function fetchAllClassLogic($className, array $constructorArguments = null)
    {
        $reflector = new ReflectionClass($className);

        $result = (yield $this->fetchAll(PDO::FETCH_ASSOC));

        if ($result) {
            $objects = [];

            foreach ($result as $row) {
                $objects[] = $this->createObject(
                    $row,
                    $reflector,
                    $constructorArguments
                );
            }

            $result = $objects;
        }

        yield Recoil::return_($result);
    // @codeCoverageIgnoreStart
    }
    // @codeCoverageIgnoreEnd

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
        throw new LogicException('Not implemented');
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
        throw new LogicException('Not implemented');
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

    private function createObject($rowData, ReflectionClass $reflector, array $constructorArguments = null)
    {
        $object = $reflector->newInstanceWithoutConstructor();

        // Note that object properties are set BEFORE the constructor is called
        // to mirror PDO's behaviour ...
        $this->populateObject($rowData, $object);

        if ($constructor = $reflector->getConstructor()) {
            if ($constructorArguments) {
                $constructor->invokeArgs($object, $constructorArguments);
            } else {
                $constructor->invoke($object);
            }
        }

        return $object;
    }

    private function populateObject($rowData, $object)
    {
        foreach ($rowData as $key => $value) {
            $object->{$key} = $value;
        }
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
        }

        throw new DatabaseException($response[1], $response[2], $response[3]);
    }

    private $connection;
    private $objectId;
    private $defaultFetchMode;
    private $defaultFetchArgument;
    private $defaultFetchConstructorArguments;
}
