<?php
namespace Recoil\Database;

/**
 * Interface for database connection factories.
 */
interface ConnectionFactoryInterface
{
    /**
     * [COROUTINE] Establish a database connection.
     *
     * The parameters are the same as {@see PDO::__construct()}.
     *
     * @param string      $dsn           The data-source name for the connection.
     * @param string|null $username      The username to use for the DSN.
     * @param string|null $password      The password to use for the DSN.
     * @param array|null  $driverOptions Driver-specific configuration options.
     *
     * @return ConnectionInterface The database connection.
     */
    public function connect($dsn, $username = null, $password = null, array $driverOptions = []);
}
