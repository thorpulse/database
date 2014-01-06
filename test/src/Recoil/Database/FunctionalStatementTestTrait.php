<?php
namespace Recoil\Database;

use Recoil\Recoil;
use PDO;

trait FunctionalStatementTestTrait
{
    public function simpleFetchModes()
    {
        return [
            [PDO::FETCH_ASSOC],
            [PDO::FETCH_BOTH],
            [PDO::FETCH_NAMED],
            [PDO::FETCH_NUM],
            [PDO::FETCH_OBJ],
        ];
    }

    public function testStatementExecute()
    {
        Recoil::run(
            function () {
                $connection = (yield $this->factory->connect($this->dsn));
                $statement = (yield $connection->prepare('DELETE FROM test'));

                yield $statement->execute();

                $this->assertEquals(0, $this->pdo->query('SELECT COUNT(*) FROM test')->fetchColumn());
            }
        );
    }

    /**
     * @dataProvider simpleFetchModes
     */
    public function testStatementSetFetchModeWithSimpleMode($mode)
    {
        $select = 'SELECT id, name, name FROM test';
        $statement = $this->pdo->query($select);
        $statement->setFetchMode($mode);
        $expected = $statement->fetch();

        Recoil::run(
            function () use ($mode, $select, $expected) {
                $connection = (yield $this->factory->connect($this->dsn));
                $statement = (yield $connection->prepare($select));

                yield $statement->execute();
                yield $statement->setFetchMode($mode);
                $result = (yield $statement->fetch());

                $this->assertEquals($expected, $result);
            }
        );
    }

    public function testStatementSetFetchModeBound()
    {
    }

    public function testStatementSetFetchModeLazy()
    {
    }

    public function testStatementSetFetchModeClass()
    {
        $select = 'SELECT id, name, name FROM test';
        $statement = $this->pdo->query($select);
        $statement->setFetchMode(PDO::FETCH_CLASS, TestRowClass::CLASS, [1, 2, 3]);
        $expected = $statement->fetch();

        Recoil::run(
            function () use ($select, $expected) {
                $connection = (yield $this->factory->connect($this->dsn));
                $statement = (yield $connection->prepare($select));

                yield $statement->execute();
                yield $statement->setFetchMode(PDO::FETCH_CLASS, TestRowClass::CLASS, [1, 2, 3]);
                $result = (yield $statement->fetch());

                $this->assertEquals($expected, $result);
            }
        );
    }

    public function testStatementSetFetchModeInto()
    {
    }

    public function testStatementFetch()
    {
        $select = 'SELECT id, name, name FROM test';
        $statement = $this->pdo->query($select);
        $expected = [];

        while ($row = $statement->fetch()) {
            $expected[] = $row;
        }

        Recoil::run(
            function () use ($select, $expected) {
                $connection = (yield $this->factory->connect($this->dsn));
                $statement = (yield $connection->prepare($select));

                yield $statement->execute();

                $result = [];
                while ($row = (yield $statement->fetch())) {
                    $result[] = $row;
                }

                $this->assertEquals($expected, $result);
            }
        );
    }
    /**
     * @dataProvider simpleFetchModes
     */
    public function testStatementFetchWithSimpleMode($mode)
    {
        $select = 'SELECT id, name, name FROM test';
        $statement = $this->pdo->query($select);
        $expected = [];

        while ($row = $statement->fetch($mode)) {
            $expected[] = $row;
        }

        Recoil::run(
            function () use ($mode, $select, $expected) {
                $connection = (yield $this->factory->connect($this->dsn));
                $statement = (yield $connection->prepare($select));

                yield $statement->execute();

                $result = [];
                while ($row = (yield $statement->fetch($mode))) {
                    $result[] = $row;
                }

                $this->assertEquals($expected, $result);
            }
        );
    }

    public function testStatementFetchBound()
    {
    }

    public function testStatementFetchLazy()
    {
    }

    public function testStatementFetchClass()
    {
    }

    public function testStatementFetchInto()
    {
    }

    public function testStatementFetchObject()
    {
        $select = 'SELECT id, name, name FROM test LIMIT 1';
        $statement = $this->pdo->query($select);
        $expected = $statement->fetchObject();

        Recoil::run(
            function () use ($select, $expected) {
                $connection = (yield $this->factory->connect($this->dsn));
                $statement = (yield $connection->prepare($select));

                yield $statement->execute();

                $this->assertEquals(
                    $expected,
                    (yield $statement->fetchObject())
                );

                $this->assertFalse(
                    yield $statement->fetchObject()
                );
            }
        );
    }

    public function testStatementFetchObjectWithCustomClass()
    {
        $select = 'SELECT id, name, name FROM test';
        $statement = $this->pdo->query($select);
        $expected = $statement->fetchObject(TestRowClass::CLASS);

        Recoil::run(
            function () use ($select, $expected) {
                $connection = (yield $this->factory->connect($this->dsn));
                $statement = (yield $connection->prepare($select));

                yield $statement->execute();
                $result = (yield $statement->fetchObject(TestRowClass::CLASS));

                $this->assertEquals($expected, $result);
            }
        );
    }

    public function testStatementFetchObjectWithConstructArguments()
    {
        $select = 'SELECT id, name FROM test';
        $statement = $this->pdo->query($select);
        $expected = $statement->fetchObject(TestRowClass::CLASS, [1, 2, 3]);

        Recoil::run(
            function () use ($select, $expected) {
                $connection = (yield $this->factory->connect($this->dsn));
                $statement = (yield $connection->prepare($select));

                yield $statement->execute();
                $result = (yield $statement->fetchObject(TestRowClass::CLASS, [1, 2, 3]));

                $this->assertEquals($expected, $result);
            }
        );
    }

    public function testStatementFetchAll()
    {
        $select = 'SELECT id, name, name FROM test';
        $statement = $this->pdo->query($select);
        $expected = $statement->fetchAll();

        Recoil::run(
            function () use ($select, $expected) {
                $connection = (yield $this->factory->connect($this->dsn));
                $statement = (yield $connection->prepare($select));

                yield $statement->execute();
                $result = (yield $statement->fetchAll());

                $this->assertEquals($expected, $result);
            }
        );
    }

    /**
     * @dataProvider simpleFetchModes
     */
    public function testStatementFetchAllWithSimpleMode($mode)
    {
        $select = 'SELECT id, name, name FROM test';
        $statement = $this->pdo->query($select);
        $expected = $statement->fetchAll($mode);

        Recoil::run(
            function () use ($mode, $select, $expected) {
                $connection = (yield $this->factory->connect($this->dsn));
                $statement = (yield $connection->prepare($select));

                yield $statement->execute();
                $result = (yield $statement->fetchAll($mode));

                $this->assertEquals($expected, $result);
            }
        );
    }

    public function testStatementFetchAllBound()
    {
    }

    public function testStatementFetchAllLazy()
    {
    }

    public function testStatementFetchAllClass()
    {
    }

    public function testStatementFetchAllInto()
    {
    }

    public function testStatementFetchColumn()
    {
        Recoil::run(
            function () {
                $connection = (yield $this->factory->connect($this->dsn));
                $statement = (yield $connection->prepare('SELECT id, name, name FROM test'));

                yield $statement->execute();

                $this->assertEquals(1, (yield $statement->fetchColumn()));
                $this->assertEquals('bar', (yield $statement->fetchColumn(1)));
                $this->assertEquals('spam', (yield $statement->fetchColumn(2)));
            }
        );
    }

    public function testStatementBindValueWithPlaceholderIndex()
    {
        Recoil::run(
            function () {
                $connection = (yield $this->factory->connect($this->dsn));
                $statement = (yield $connection->prepare('UPDATE test SET name = ? WHERE id = ?'));

                yield $statement->bindValue(1, 'bob');
                yield $statement->bindValue(2, 2, PDO::PARAM_INT);
                yield $statement->execute();

                $this->assertEquals('bob', $this->pdo->query('SELECT name FROM test WHERE id = 2')->fetchColumn());
            }
        );
    }

    public function testStatementBindValueWithPlaceholderName()
    {
        Recoil::run(
            function () {
                $connection = (yield $this->factory->connect($this->dsn));
                $statement = (yield $connection->prepare('UPDATE test SET name = :name WHERE id = :id'));

                yield $statement->bindValue('name', 'bob');
                yield $statement->bindValue('id', 2, PDO::PARAM_INT);
                yield $statement->execute();

                $this->assertEquals('bob', $this->pdo->query('SELECT name FROM test WHERE id = 2')->fetchColumn());
            }
        );
    }

    public function testStatementBindParam()
    {
    }

    public function testStatementBindColumn()
    {
    }

    public function testStatementColumnCount()
    {
        Recoil::run(
            function () {
                $connection = (yield $this->factory->connect($this->dsn));
                $statement = (yield $connection->prepare('SELECT * FROM test'));

                yield $statement->execute();

                $this->assertEquals(2, (yield $statement->columnCount()));
            }
        );
    }

    public function testStatementRowCount()
    {
        Recoil::run(
            function () {
                $connection = (yield $this->factory->connect($this->dsn));
                $statement = (yield $connection->prepare('DELETE FROM test'));

                yield $statement->execute();

                $this->assertEquals(3, (yield $statement->rowCount()));
            }
        );
    }

    public function testStatementGetColumnMeta()
    {
        $select = 'SELECT id, name FROM test';
        $statement = $this->pdo->query($select);
        $expected = $statement->getcolumnMeta(1);

        Recoil::run(
            function () use ($select, $expected) {
                $connection = (yield $this->factory->connect($this->dsn));
                $statement = (yield $connection->query($select));

                $metaData = (yield $statement->getColumnMeta(1));

                $this->assertEquals($expected, $metaData);
            }
        );
    }

    public function testStatementNextRowset()
    {
    }

    public function testStatementCloseCursor()
    {
    }

    public function testStatementSetAttribute()
    {
    }

    public function testStatementGetAttribute()
    {
    }

    public function testStatementErrorCode()
    {
        Recoil::run(
            function () {
                $connection = (yield $this->factory->connect($this->dsn));

                $statement = (yield $connection->prepare('SELECT * FROM test'));

                yield $statement->bindValue(1, 'foo');

                yield $statement->execute();

                $code = (yield $statement->errorCode());

                $this->assertSame('HY000', $code);
            }
        );
    }

    public function testStatementErrorInfo()
    {
        Recoil::run(
            function () {
                $connection = (yield $this->factory->connect($this->dsn));

                $statement = (yield $connection->prepare('SELECT * FROM test'));

                yield $statement->bindValue(1, 'foo');

                yield $statement->execute();

                $info = (yield $statement->errorInfo());

                $this->assertSame(['HY000', 25, 'bind or column index out of range'], $info);
            }
        );
    }

    public function testStatementQueryString()
    {
        Recoil::run(
            function () {
                $query = 'SELECT * FROM test';

                $connection = (yield $this->factory->connect($this->dsn));
                $statement = (yield $connection->prepare($query));

                $this->assertEquals($query, (yield $statement->queryString()));
            }
        );
    }

    public function testStatementDebugDumpParams()
    {
        $output  = 'SQL: [18] SELECT * FROM test' . PHP_EOL;
        $output .= 'Params:  0' . PHP_EOL;

        $this->expectOutputString($output);

        Recoil::run(
            function () {
                $query = 'SELECT * FROM test';

                $connection = (yield $this->factory->connect($this->dsn));
                $statement = (yield $connection->prepare($query));

                yield $statement->debugDumpParams();
            }
        );
    }
}
