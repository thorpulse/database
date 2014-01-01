<?php
namespace Icecave\Recoil\Database;

use Icecave\Recoil\Database\Detail\Connection;
use Icecave\Recoil\Database\Exception\DatabaseException;
use Icecave\Recoil\Recoil;
use PDO;

trait FunctionalTestTrait
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
    }

    public function tearDown()
    {
        if (file_exists($this->path)) {
            unlink($this->path);
        }
    }

    abstract public function createFactory();

    public function testPrepare()
    {
    }

    public function testQuery()
    {
    }

    public function testExec()
    {
        Recoil::run(
            function () {
                $connection = (yield $this->factory->connect($this->dsn));
                $count = (yield $connection->exec('INSERT INTO test VALUES (null, "foo")'));

                $this->assertSame(1, $count);
                $this->assertEquals(
                    [[1, 'foo']],
                    $this->pdo->query('SELECT * FROM test')->fetchAll(PDO::FETCH_NUM)
                );
            }
        );
    }

    public function testInTransaction()
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

    public function testCommit()
    {
        Recoil::run(
            function () {
                $connection = (yield $this->factory->connect($this->dsn));

                yield $connection->beginTransaction();

                yield $connection->exec('INSERT INTO test VALUES (null, "foo")');

                $this->assertEquals(0, $this->pdo->query('SELECT COUNT(*) FROM test')->fetchColumn());

                yield $connection->commit();

                $this->assertFalse(yield $connection->inTransaction());
                $this->assertEquals(1, $this->pdo->query('SELECT COUNT(*) FROM test')->fetchColumn());

            }
        );
    }

    public function testRollback()
    {
        Recoil::run(
            function () {
                $connection = (yield $this->factory->connect($this->dsn));

                yield $connection->beginTransaction();

                yield $connection->exec('INSERT INTO test VALUES (null, "foo")');

                $this->assertEquals(0, $this->pdo->query('SELECT COUNT(*) FROM test')->fetchColumn());

                yield $connection->rollback();

                $this->assertFalse(yield $connection->inTransaction());
                $this->assertEquals(0, $this->pdo->query('SELECT COUNT(*) FROM test')->fetchColumn());

            }
        );
    }

    public function testLastInsertId()
    {
        Recoil::run(
            function () {
                $connection = (yield $this->factory->connect($this->dsn));

                yield $connection->exec('INSERT INTO test VALUES (null, "foo")');

                $this->assertEquals(1, (yield $connection->lastInsertId()));

                yield $connection->exec('INSERT INTO test VALUES (null, "bar")');

                $this->assertEquals(2, (yield $connection->lastInsertId()));
            }
        );
    }

    public function testErrorCode()
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

    public function testErrorInfo()
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

    public function testQuote()
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

    public function testSetAndGetAttribute()
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

    public function testException()
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
