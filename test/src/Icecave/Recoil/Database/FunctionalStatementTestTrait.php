<?php
namespace Icecave\Recoil\Database;

use Icecave\Recoil\Recoil;
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
        Recoil::run(
            function () use ($mode) {
                $select = 'SELECT id, name, name FROM test';

                // Prepare the expected value using PDO directly ...
                $statement = $this->pdo->query($select);
                $statement->setFetchMode($mode);
                $expected = $statement->fetch();

                // Attempt to produce the same result asynchronously ...
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
    }

    public function testStatementSetFetchModeInto()
    {
    }

    public function testStatementFetch()
    {
        Recoil::run(
            function () {
                $select = 'SELECT id, name, name FROM test';

                // Prepare the expected value using PDO directly ...
                $statement = $this->pdo->query($select);
                $expected = [];

                while ($row = $statement->fetch()) {
                    $expected[] = $row;
                }

                // Attempt to produce the same result asynchronously ...
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
        Recoil::run(
            function () use ($mode) {
                $select = 'SELECT id, name, name FROM test';

                // Prepare the expected value using PDO directly ...
                $statement = $this->pdo->query($select);
                $expected = [];

                while ($row = $statement->fetch($mode)) {
                    $expected[] = $row;
                }

                // Attempt to produce the same result asynchronously ...
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
    }

    public function testStatementFetchObjectWithCustomClass()
    {
    }

    public function testStatementFetchObjectWithUnknownClass()
    {
    }

    public function testStatementFetchAll()
    {
        Recoil::run(
            function () {
                $select = 'SELECT id, name, name FROM test';

                // Prepare the expected value using PDO directly ...
                $statement = $this->pdo->query($select);
                $expected = $statement->fetchAll();

                // Attempt to produce the same result asynchronously ...
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
        Recoil::run(
            function () use ($mode) {
                $select = 'SELECT id, name, name FROM test';

                // Prepare the expected value using PDO directly ...
                $statement = $this->pdo->query($select);
                $expected = $statement->fetchAll($mode);

                // Attempt to produce the same result asynchronously ...
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
        Recoil::run(
            function () {
                $connection = (yield $this->factory->connect($this->dsn));
                $statement = (yield $connection->prepare('SELECT * FROM test'));

                yield $statement->execute();

                $expected = [
                    'native_type' => 'string',
                    'sqlite:decl_type' => 'STRING',
                    'flags' => [],
                    'name' => 'name',
                    'len' => 4294967295,
                    'precision' => 0,
                    'pdo_type' => PDO::PARAM_STR,
                ];

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
