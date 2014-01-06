<?php
namespace Recoil\Database\Detail;

use PDO;
use PDOStatement;

class ConnectionService
{
    public function __construct(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;
    }

    public function connect($dsn, $username = null, $password = null, array $driverOptions = null)
    {
        $this->connection = new PDO($dsn, $username, $password, $driverOptions);

        return [ResponseType::VALUE, null];
    }

    public function disconnect()
    {
        $this->connection = null;

        $this->serviceManager->removeObject($this);

        return null; // Do not send a response.
    }

    public function __call($name, array $arguments)
    {
        $value = call_user_func_array(
            [$this->connection, $name],
            $arguments
        );

        if (!$value instanceof PDOStatement) {
            return [ResponseType::VALUE, $value];
        }

        $statementService = new StatementService($this->serviceManager, $value);
        $objectId = $this->serviceManager->addObject($statementService);

        return [ResponseType::STATEMENT, $objectId];
    }

    private $serviceManager;
    private $connection;
}
