<?php
namespace Icecave\Engage;

use Icecave\Engage\Detail\Client;
use Icecave\Engage\Detail\Request\InvokeStatementMethod;
use Icecave\Recoil\Recoil;
use PDO;
use PDOException;
use ReflectionClass;

class Statement implements StatementInterface
{
    /**
     * @param Client $channel The channel used for RPC communication.
     * @param string $id A unique identifier for the statement object.
     */
    public function __construct(Client $serviceClient, $statementId)
    {
        $this->serviceClient = $serviceClient;
        $this->statementId = $statementId;
    }

    public function __destruct()
    {
        $this->serviceClient->releaseStatement($this->statementId);
    }

    public function fetch(
        $fetchStyle = null,
        $cursorOrientation = PDO::FETCH_ORI_NEXT,
        $cursorOffset = 0
    ) {
        return $this->invoke(
            'fetch',
            [
                $fetchStyle,
                $cursorOrientation,
                $cursorOffset
            ]
        );
    }

    public function fetchObject($className = 'stdClass', array $constructorArguments = [])
    {
        $reflector = new ReflectionClass($className);
        $object = $reflector->newInstanceWithoutConstructor();

        $values = (yield $this->fetch(PDO::FETCH_ASSOC));

        if (!is_array($values)) {
            yield Recoil::return_($values);
        }

        foreach ($values as $key => $value) {
            $object->{$key} = $value;
        }

        if ($constructor = $reflector->getConstructor()) {
            $constructor->invokeArgs($object, $constructorArguments);
        }

        yield Recoil::return_($object);
    }

    public function fetchColumn($columnIndex)
    {
        return $this->invoke('fetchColumn', [$columnIndex]);
    }

    public function nextRowset()
    {
        return $this->invoke('nextRowset');
    }

    public function closeCursor()
    {
        return $this->invoke('closeCursor');
    }

    public function columnCount()
    {
        return $this->invoke('columnCount');
    }

    public function rowCount()
    {
        return $this->invoke('rowCount');
    }

    public function errorCode()
    {
        return $this->invoke('errorCode');
    }

    public function errorInfo()
    {
        return $this->invoke('errorInfo');
    }

    public function getColumnMeta($columnIndex)
    {
        return $this->invoke('getColumnMeta', [$columnIndex]);
    }

    public function setAttribute($attribute, $value)
    {
        return $this->invoke('setAttribute', [$attribute, $value]);
    }

    public function getAttribute($attribute)
    {
        return $this->invoke('getAttribute', [$attribute]);
    }

    public function debugDumpParams()
    {
        echo (yield $this->invoke('debugDumpParams'));
    }

    private function invoke($name, array $arguments = [])
    {
        $request = new InvokeStatementMethod(
            $this->statementId,
            $name,
            $arguments
        );

        return $this->serviceClient->send($request);
    }

    private $serviceClient;
    private $statementId;
}
