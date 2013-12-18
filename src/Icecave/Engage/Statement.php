<?php
namespace Icecave\Engage;

use Icecave\Recoil\Recoil;
use Icecave\Engage\Detail\Client;
use Icecave\Engage\Detail\StatementMethodRequest;
use PDO;
use PDOException;

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
        $this->serviceClient->destroyStatement($this->statementId);
    }

    public function columnCount()
    {
        if (null === $this->columnCount) {
            $this->columnCount = (yield $this->serviceClient->send(
                new StatementMethodRequest($this->statementId, 'columnCount')
            ));
        }

        yield Recoil::return_($this->columnCount);
    }

    public function rowCount()
    {
        if (null === $this->rowCount) {
            $this->rowCount = (yield $this->serviceClient->send(
                new StatementMethodRequest($this->statementId, 'rowCount')
            ));
        }

        yield Recoil::return_($this->rowCount);
    }

    public function errorCode()
    {
        if (null === $this->errorCode) {
            $this->errorCode = (yield $this->serviceClient->send(
                new StatementMethodRequest($this->statementId, 'errorCode')
            ));
        }

        yield Recoil::return_($this->errorCode);
    }

    public function errorInfo()
    {
        if (null === $this->errorInfo) {
            $this->errorInfo = (yield $this->serviceClient->send(
                new StatementMethodRequest($this->statementId, 'errorInfo')
            ));
        }

        yield Recoil::return_($this->errorInfo);
    }

    private $serviceClient;
    private $statementId;
    private $columnCount;
    private $rowCount;
    private $errorCode;
    private $errorInfo;
    private $columnMetaData;
}
