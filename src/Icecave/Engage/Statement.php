<?php
namespace Icecave\Engage;

use Icecave\Recoil\Channel\BidirectionalChannelInterface;
use Icecave\Recoil\Recoil;
use PDO;
use PDOException;

class Statement extends StatementInterface
{
    /**
     * @param BidirectionalChannelInterface $channel The channel used for RPC communication.
     * @param string $id A unique identifier for the statement object.
     */
    public function __construct(BidirectionalChannelInterface $channel, $id)
    {
        $this->channel = $channel;
        $this->id = $id;
    }

    // public bool bindColumn($column, &$param [, int $type [, int $maxlen [, mixed $driverdata ]]] )
    // public bool bindParam ( mixed $parameter , mixed &$variable [, int $data_type = PDO::PARAM_STR [, int $length [, mixed $driver_options ]]] )
    // public bool bindValue ( mixed $parameter , mixed $value [, int $data_type = PDO::PARAM_STR ] )
    // public bool closeCursor ( void )
    // public int columnCount ( void )
    // public void debugDumpParams ( void )
    // public string errorCode ( void )
    // public array errorInfo ( void )
    // public bool execute ([ array $input_parameters ] )
    // public mixed fetch ([ int $fetch_style [, int $cursor_orientation = PDO::FETCH_ORI_NEXT [, int $cursor_offset = 0 ]]] )
    // public array fetchAll ([ int $fetch_style [, mixed $fetch_argument [, array $ctor_args = array() ]]] )
    // public string fetchColumn ([ int $column_number = 0 ] )
    // public mixed fetchObject ([ string $class_name = "stdClass" [, array $ctor_args ]] )
    // public mixed getAttribute ( int $attribute )
    // public array getColumnMeta ( int $column )
    // public bool nextRowset ( void )
    // public int rowCount ( void )
    // public bool setAttribute ( int $attribute , mixed $value )
    // public function setFetchMode ($mode);

    public function columnCount()
    {
        if (null === $this->columnCount) {
            $this->columnCount = (yield $this->rpc('columnCount'));
        }

        return $this->columnCount;
    }

    public function rowCount()
    {
        if (null === $this->rowCount) {
            $this->rowCount = (yield $this->rpc('rowCount'));
        }

        return $this->rowCount;
    }

    public function errorCode()
    {
        if (null === $this->errorCode) {
            $this->errorCode = (yield $this->rpc('errorCode'));
        }

        return $this->errorCode;
    }

    public function errorInfo()
    {
        if (null === $this->errorInfo) {
            $this->errorInfo = (yield $this->rpc('errorInfo'));
        }

        return $this->errorInfo;
    }

    protected function rpc($name, array $arguments = [])
    {
        yield $this->channel->write(
            [
                'statement-' . $this->id,
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
    private $id;
    private $columnCount;
    private $rowCount;
    private $errorCode;
    private $errorInfo;
    private $columnMetaData;
}
