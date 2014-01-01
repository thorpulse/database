<?php
namespace Icecave\Recoil\Database;

use PDO;

interface StatementInterface
{
    /**
     * [COROUTINE] Execute the prepared statement.
     *
     * @param array $inputParameters Values to be bind to query placeholders.
     *
     * @return boolean True if prepared statement was executed successfully.
     */
    public function execute(array $inputParameters = []);

    /**
     * [COROUTINE] Fetch all remaining results.
     *
     * There are a number of valid ways to call this method. See the PHP manual
     * entry for PDOStatement::fetchAll() for more information.
     *
     * @link http://php.net/pdostatement.fetchall
     *
     * @param mixed $argument,... Arguments.
     *
     * @return array An array containing all of the remaining rows in the result set.
     */
    public function fetchAll();

    // public $queryString;

    // public bool bindColumn($column, &$param [, int $type [, int $maxlen [, mixed $driverdata ]]] )
    // public bool bindParam ( mixed $parameter , mixed &$variable [, int $data_type = PDO::PARAM_STR [, int $length [, mixed $driver_options ]]] )
    // public bool bindValue ( mixed $parameter , mixed $value [, int $data_type = PDO::PARAM_STR ] )
    // public void debugDumpParams ( void )
    // public bool execute ([ array $input_parameters ] )
    // public array fetchAll ([ int $fetch_style [, mixed $fetch_argument [, array $ctor_args = array() ]]] )
    // public function setFetchMode ($mode);

    // public function fetch(
    //     $fetchStyle = null,
    //     $cursorOrientation = PDO::FETCH_ORI_NEXT,
    //     $cursorOffset = 0
    // );

    // public function fetchObject($className = 'stdClass', array $constructorArguments = []);

    // public function fetchColumn($columnIndex);

    // public function nextRowset();
    // public function closeCursor();
    // public function columnCount();
    // public function rowCount();
    // public function errorCode();
    // public function errorInfo();
    // public function getColumnMeta($columnIndex);
    // public function setAttribute($attribute, $value);
    // public function getAttribute($attribute);
    // public function debugDumpParams();

    // public bool bindColumn($column, &$param [, int $type [, int $maxlen [, mixed $driverdata ]]] )
    // public bool bindParam ( mixed $parameter , mixed &$variable [, int $data_type = PDO::PARAM_STR [, int $length [, mixed $driver_options ]]] )
}
