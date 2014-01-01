<?php
namespace Icecave\Recoil\Database\Detail;

use Icecave\Recoil\Channel\BidirectionalChannelInterface;
use Icecave\Recoil\Database\Exception\DatabaseException;
use Icecave\Recoil\Database\StatementInterface;
use Icecave\Recoil\Kernel\KernelInterface;
use Icecave\Recoil\Recoil;
use PDO;
use ReflectionClass;

class Statement implements StatementInterface
{
    public function __construct(BidirectionalChannelInterface $channel, KernelInterface $kernel, $objectId)
    {
        $this->channel = $channel;
        $this->kernel = $kernel;
        $this->objectId = $objectId;
    }

    public function __destruct()
    {
        $this->kernel->execute(
            $this->channel->write([$this->objectId, 'release'])
        );
    }

    public function fetch(
        $fetchStyle = null,
        $cursorOrientation = PDO::FETCH_ORI_NEXT,
        $cursorOffset = 0
    ) {
        return $this->serviceRequest(__FUNCTION__, func_get_args());
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
        return $this->serviceRequest(__FUNCTION__, func_get_args());
    }

    public function nextRowset()
    {
        return $this->serviceRequest(__FUNCTION__);
    }

    public function closeCursor()
    {
        return $this->serviceRequest(__FUNCTION__);
    }

    public function columnCount()
    {
        return $this->serviceRequest(__FUNCTION__);
    }

    public function rowCount()
    {
        return $this->serviceRequest(__FUNCTION__);
    }

    public function errorCode()
    {
        return $this->serviceRequest(__FUNCTION__);
    }

    public function errorInfo()
    {
        return $this->serviceRequest(__FUNCTION__);
    }

    public function getColumnMeta($columnIndex)
    {
        return $this->serviceRequest(__FUNCTION__, func_get_args());
    }

    public function setAttribute($attribute, $value)
    {
        return $this->serviceRequest(__FUNCTION__, func_get_args());
    }

    public function getAttribute($attribute)
    {
        return $this->serviceRequest(__FUNCTION__, func_get_args());
    }

    public function debugDumpParams()
    {
        echo (yield $this->serviceRequest('debugDumpParams'));
    }

    public function serviceRequest($method, array $arguments = [])
    {
        $request = [$this->objectId, $method];

        if ($arguments) {
            $request[] = $arguments;
        }

        yield $this->channel->write($request);

        $response = (yield $this->channel->read());

        switch ($response[0]) {
            case ResponseType::VALUE:
                yield Recoil::return_($response[1]);

            case ResponseType::EXCEPTION:
                throw new DatabaseException($response[1], $response[2], $response[3]);
        }

        throw new RuntimeException('Invalid response type.');
    }

    private $channel;
    private $kernel;
    private $objectId;
}
