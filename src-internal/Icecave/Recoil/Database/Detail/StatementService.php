<?php
namespace Icecave\Recoil\Database\Detail;

use PDOStatement;

class StatementService
{
    public function __construct(ServiceManager $serviceManager, PDOStatement $statement)
    {
        $this->serviceManager = $serviceManager;
        $this->statement = $statement;
    }

    public function release()
    {
        $this->statement = null;

        $this->serviceManager->removeObject($this);

        return null; // Do not send a response.
    }

    public function __call($name, array $arguments)
    {
        $value = call_user_func_array(
            [$this->statement, $name],
            $arguments
        );

        return [ResponseType::VALUE, $value];
    }

    public function queryString()
    {
        return [ResponseType::VALUE, $this->statement->queryString];
    }

    public function debugDumpParams()
    {
        ob_start();

        $this->statement->debugDumpParams();

        $output = ob_get_clean();

        return [ResponseType::VALUE, $output];
    }

    private $serviceManager;
    private $statement;
}
