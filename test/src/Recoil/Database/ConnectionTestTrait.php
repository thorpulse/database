<?php
namespace Recoil\Database;

use Recoil\Database\Detail\Connection;
use Recoil\Database\Exception\DatabaseException;
use Recoil\Recoil;
use PDO;

trait ConnectionTestTrait
{
    public function testConnectionPrepare()
    {
        Recoil::run(
            function () {
                $connection = (yield $this->factory->connect($this->dsn));
                $statement = (yield $connection->prepare($this->selectQuery));

                $this->assertInstanceOf(StatementInterface::CLASS, $statement);

                yield $statement->execute();

                $this->assertEquals(3, (yield $statement->columnCount()));
            }
        );
    }

    public function testConnectionQuery()
    {
        Recoil::run(
            function () {
                $connection = (yield $this->factory->connect($this->dsn));
                $statement = (yield $connection->query($this->selectQuery));

                $this->assertInstanceOf(StatementInterface::CLASS, $statement);
                $this->assertEquals(3, (yield $statement->columnCount()));
            }
        );
    }

    public function testConnectionQueryWithFetchModeParameters()
    {
        Recoil::run(
            function () {
                $connection = (yield $this->factory->connect($this->dsn));
                $statement = (yield $connection->query(
                    $this->selectQuery,
                    PDO::FETCH_CLASS,
                    TestRowClass::CLASS,
                    [1, 2, 3]
                ));

                $this->assertInstanceOf(StatementInterface::CLASS, $statement);

                $expected = new TestRowClass(1, 2, 3);
                $expected->id = '1';
                $expected->name = 'foo';

                $this->assertEquals($expected, (yield $statement->fetch()));
            }
        );
    }

    public function testConnectionExec()
    {
        Recoil::run(
            function () {
                $connection = (yield $this->factory->connect($this->dsn));

                $this->assertSame(3, (yield $connection->exec($this->deleteQuery)));
            }
        );
    }

    public function testConnectionCommit()
    {
        Recoil::run(
            function () {
                $connection = (yield $this->factory->connect($this->dsn));

                yield $connection->beginTransaction();
                yield $connection->exec($this->deleteQuery);

                $this->assertEquals(3, $this->pdo->query('SELECT COUNT(*) FROM test')->fetchColumn());

                yield $connection->commit();

                $this->assertFalse(yield $connection->inTransaction());
                $this->assertEquals(0, $this->pdo->query('SELECT COUNT(*) FROM test')->fetchColumn());
            }
        );
    }

    public function testConnectionRollback()
    {
        Recoil::run(
            function () {
                $connection = (yield $this->factory->connect($this->dsn));

                yield $connection->beginTransaction();
                yield $connection->exec($this->deleteQuery);

                $this->assertEquals(3, $this->pdo->query('SELECT COUNT(*) FROM test')->fetchColumn());

                yield $connection->rollback();

                $this->assertFalse(yield $connection->inTransaction());
                $this->assertEquals(3, $this->pdo->query('SELECT COUNT(*) FROM test')->fetchColumn());
            }
        );
    }

    public function testConnectionLastInsertId()
    {
        Recoil::run(
            function () {
                $connection = (yield $this->factory->connect($this->dsn));

                yield $connection->exec('INSERT INTO test VALUES (null, "a")');

                $this->assertEquals(4, (yield $connection->lastInsertId()));

                yield $connection->exec('INSERT INTO test VALUES (null, "b")');

                $this->assertEquals(5, (yield $connection->lastInsertId()));
            }
        );
    }

    public function testConnectionErrorCode()
    {
        Recoil::run(
            function () {
                $connection = (yield $this->factory->connect($this->dsn));

                yield $connection->exec('FOO');

                $code = (yield $connection->errorCode());

                $this->assertSame('HY000', $code);
            }
        );
    }

    public function testConnectionErrorInfo()
    {
        Recoil::run(
            function () {
                $connection = (yield $this->factory->connect($this->dsn));

                yield $connection->exec('FOO');

                $info = (yield $connection->errorInfo());

                $this->assertSame(['HY000', 1, 'near "FOO": syntax error'], $info);
            }
        );
    }

    public function testConnectionQuote()
    {
        Recoil::run(
            function () {
                $connection = (yield $this->factory->connect($this->dsn));

                $input = "`'\"{[;";
                $expected = $this->pdo->quote($input);
                $result = (yield $connection->quote($input));

                $this->assertSame($expected, $result);
            }
        );
    }

    public function testConnectionSetAndGetAttribute()
    {
        Recoil::run(
            function () {
                $connection = (yield $this->factory->connect($this->dsn));
                $mode = (yield $connection->getAttribute(PDO::ATTR_ERRMODE));

                $this->assertSame(PDO::ERRMODE_SILENT, $mode);

                yield $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $mode = (yield $connection->getAttribute(PDO::ATTR_ERRMODE));

                $this->assertSame(PDO::ERRMODE_EXCEPTION, $mode);
            }
        );
    }

    public function testExceptionPropagation()
    {
        Recoil::run(
            function () {
                $connection = (yield $this->factory->connect($this->dsn));
                yield $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                $this->setExpectedException(
                    DatabaseException::CLASS,
                    'SQLSTATE[HY000]: General error: 1 near "FOO": syntax error'
                );

                yield $connection->exec('FOO');
            }
        );
    }
}
