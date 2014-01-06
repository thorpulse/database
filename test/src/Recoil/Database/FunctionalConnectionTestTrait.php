<?php
namespace Recoil\Database;

use Recoil\Database\Detail\Connection;
use Recoil\Database\Exception\DatabaseException;
use Recoil\Recoil;
use PDO;

trait FunctionalConnectionTestTrait
{
    public function setUp()
    {
        $this->factory = $this->createFactory();
        $this->path = tempnam(sys_get_temp_dir(), 'recoil-database-');
        $this->dsn = 'sqlite:' . $this->path;
        $this->pdo = new PDO($this->dsn);

        $this->pdo->exec(
            'CREATE TABLE test (
                id INTEGER PRIMARY KEY,
                name STRING NOT NULL
            )'
        );

        $this->pdo->exec('INSERT INTO test VALUES (null, "foo")');
        $this->pdo->exec('INSERT INTO test VALUES (null, "bar")');
        $this->pdo->exec('INSERT INTO test VALUES (null, "spam")');
    }

    public function tearDown()
    {
        if (file_exists($this->path)) {
            unlink($this->path);
        }
    }

    abstract public function createFactory();

    public function testConnectionPrepare()
    {
        Recoil::run(
            function () {
                $connection = (yield $this->factory->connect($this->dsn));

                $statement = (yield $connection->prepare('DELETE FROM test'));

                $this->assertInstanceOf(StatementInterface::CLASS, $statement);
            }
        );
    }

    public function testConnectionQuery()
    {
        Recoil::run(
            function () {
                $connection = (yield $this->factory->connect($this->dsn));

                $statement = (yield $connection->query('SELECT * FROM test'));

                $this->assertInstanceOf(StatementInterface::CLASS, $statement);

                $this->assertEquals(
                    [[1, 'foo'], [2, 'bar'], [3, 'spam']],
                    (yield $statement->fetchAll(PDO::FETCH_NUM))
                );
            }
        );
    }

    public function testConnectionQueryWithFetchModeParameters()
    {
        Recoil::run(
            function () {
                $connection = (yield $this->factory->connect($this->dsn));

                $statement = (yield $connection->query(
                    'SELECT * FROM test',
                    PDO::FETCH_CLASS,
                    TestRowClass::CLASS,
                    [1, 2, 3]
                ));

                $expected = new TestRowClass(1, 2, 3);
                $expected->id = '1';
                $expected->name = 'foo';

                $this->assertInstanceOf(StatementInterface::CLASS, $statement);

                $this->assertEquals(
                    $expected,
                    (yield $statement->fetch())
                );
            }
        );
    }

    public function testConnectionExec()
    {
        Recoil::run(
            function () {
                $connection = (yield $this->factory->connect($this->dsn));

                $count = (yield $connection->exec('DELETE FROM test WHERE id > 1'));

                $this->assertSame(2, $count);
                $this->assertEquals(
                    [[1, 'foo']],
                    $this->pdo->query('SELECT * FROM test')->fetchAll(PDO::FETCH_NUM)
                );
            }
        );
    }

    public function testConnectionInTransaction()
    {
        Recoil::run(
            function () {
                $connection = (yield $this->factory->connect($this->dsn));

                $this->assertFalse(yield $connection->inTransaction());

                yield $connection->beginTransaction();

                $this->assertTrue(yield $connection->inTransaction());

                yield $connection->rollback();

                $this->assertFalse(yield $connection->inTransaction());
            }
        );
    }

    public function testConnectionCommit()
    {
        Recoil::run(
            function () {
                $connection = (yield $this->factory->connect($this->dsn));

                yield $connection->beginTransaction();

                yield $connection->exec('DELETE FROM test');

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

                yield $connection->exec('DELETE FROM test');

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
