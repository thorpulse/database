<?php
namespace Icecave\Recoil\Database\Detail;

use Exception;
use Icecave\Recoil\Channel\BidirectionalChannelInterface;
use LogicException;
use PDOException;

class ServiceManager
{
    public function __construct(BidirectionalChannelInterface $channel)
    {
        $this->channel = $channel;
        $this->objects = [];

        $this->addObject(new ConnectionService($this));
    }

    public function __invoke()
    {
        do {
            yield $this->dispatch(
                yield $this->channel->read()
            );
        } while (isset($this->objects[0]));

        yield $this->channel->close();
    }

    public function getObject($objectId)
    {
        if (isset($this->objects[$objectId])) {
            return $this->objects[$objectId];
        }

        throw new LogicException('Unknown object #' . $objectId . '.');
    }

    public function addObject($object)
    {
        $objectId = count($this->objects);

        $this->objects[$objectId] = $object;

        return $objectId;
    }

    public function removeObject($object)
    {
        foreach ($this->objects as $id => $obj) {
            if ($obj === $object) {
                unset($this->objects[$id]);
                break;
            }
        }
    }

    private function dispatch($request)
    {
        list($objectId, $method) = $request;

        if (isset($request[2])) {
            $arguments = $request[2];
        } else {
            $arguments = [];
        }

        fwrite(STDERR, sprintf(
            'call %s::%s(%s)' . PHP_EOL,
            $objectId,
            $method,
            json_encode($arguments)
        ));

        $object = $this->getObject($objectId);

        try {
            $response = call_user_func_array(
                [$object, $method],
                $arguments
            );
        } catch (PDOException $e) {
            $response = [ResponseType::EXCEPTION, $e->getMessage(), $e->getCode(), $e->errorInfo];
        } catch (Exception $e) {
            $response = [ResponseType::EXCEPTION, $e->getMessage(), $e->getCode(), null];
        }

        if (null !== $response) {
            yield $this->channel->write($response);
        }
    }

    private $channel;
    private $objects;
}
