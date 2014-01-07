<?php
namespace Recoil\Database;

use PDO;
use Recoil\Recoil;
use stdClass;

trait StatementTestTrait
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
                $statement = (yield $connection->prepare($this->deleteQuery));

                yield $statement->execute();

                $this->assertEquals(
                    0,
                    $this->pdo
                        ->query('SELECT COUNT(*) FROM test')
                        ->fetchColumn()
                );
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
                $statement = $this->pdo->query($this->selectQuery);
                $statement->setFetchMode($mode);
                $expected = $statement->fetch();

                $connection = (yield $this->factory->connect($this->dsn));
                $statement = (yield $connection->query($this->selectQuery));
                yield $statement->setFetchMode($mode);

                $this->assertEquals(
                    $expected,
                    (yield $statement->fetch())
                );
            }
        );
    }

    // public function testStatementSetFetchModeBound()
    // {
    // }

    // public function testStatementSetFetchModeLazy()
    // {
    // }

    public function testStatementSetFetchModeClass()
    {
        Recoil::run(
            function () {
                $statement = $this->pdo->query($this->selectQuery);
                $statement->setFetchMode(
                    PDO::FETCH_CLASS,
                    TestRowClass::CLASS,
                    [1, 2, 3]
                );
                $expected = $statement->fetch();

                $connection = (yield $this->factory->connect($this->dsn));
                $statement = (yield $connection->query($this->selectQuery));

                yield $statement->setFetchMode(
                    PDO::FETCH_CLASS,
                    TestRowClass::CLASS,
                    [1, 2, 3]
                );

                $this->assertEquals(
                    $expected,
                    (yield $statement->fetch())
                );
            }
        );
    }

    public function testStatementSetFetchModeWithInvalidClassArgument()
    {
        Recoil::run(
            function () {
                $connection = (yield $this->factory->connect($this->dsn));
                $statement = (yield $connection->prepare($this->selectQuery));

                $this->setExpectedException(
                    'ReflectionException',
                    'Class ClassDoesNotExist does not exist'
                );

                yield $statement->setFetchMode(
                    PDO::FETCH_CLASS,
                    'ClassDoesNotExist'
                );
            }
        );
    }

    public function testStatementSetFetchModeInto()
    {
        Recoil::run(
            function () {
                $statement = $this->pdo->query($this->selectQuery);
                $expected = new stdClass;
                $statement->setFetchMode(PDO::FETCH_INTO, $expected);
                $statement->fetch();

                $connection = (yield $this->factory->connect($this->dsn));
                $statement = (yield $connection->query($this->selectQuery));

                $target = new stdClass;

                yield $statement->setFetchMode(PDO::FETCH_INTO, $target);
                $result = (yield $statement->fetch());

                $this->assertEquals($expected, $target);
                $this->assertSame($target, $result);
            }
        );
    }

    public function testStatementFetch()
    {
        Recoil::run(
            function () {
                $statement = $this->pdo->query($this->selectQuery);

                $expected = [];
                while ($row = $statement->fetch()) {
                    $expected[] = $row;
                }

                $connection = (yield $this->factory->connect($this->dsn));
                $statement = (yield $connection->query($this->selectQuery));

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
                $statement = $this->pdo->query($this->selectQuery);

                $expected = [];
                while ($row = $statement->fetch($mode)) {
                    $expected[] = $row;
                }

                $connection = (yield $this->factory->connect($this->dsn));
                $statement = (yield $connection->query($this->selectQuery));

                $result = [];
                while ($row = (yield $statement->fetch($mode))) {
                    $result[] = $row;
                }

                $this->assertEquals($expected, $result);
            }
        );
    }

    // public function testStatementFetchBound()
    // {
    // }

    // public function testStatementFetchLazy()
    // {
    // }

    public function testStatementFetchClass()
    {
        Recoil::run(
            function () {
                $statement = $this->pdo->query($this->selectQuery);
                $statement->setFetchMode(PDO::FETCH_CLASS, 'stdClass');
                $expected = $statement->fetch(PDO::FETCH_CLASS);

                $connection = (yield $this->factory->connect($this->dsn));
                $statement = (yield $connection->query($this->selectQuery));

                yield $statement->setFetchMode(PDO::FETCH_CLASS, 'stdClass');
                $result = (yield $statement->fetch(PDO::FETCH_CLASS));

                $this->assertEquals($expected, $result);
            }
        );
    }

    public function testStatementFetchClassFailure()
    {
        Recoil::run(
            function () {
                $connection = (yield $this->factory->connect($this->dsn));
                $statement = (yield $connection->query($this->selectQuery));

                $this->setExpectedException(
                    'LogicException',
                    'PDO::FETCH_CLASS must be configured as the DEFAULT fetch mode in order to use PDO::FETCH_CLASS.'
                );

                yield $statement->fetch(PDO::FETCH_CLASS);
            }
        );
    }

    public function testStatementFetchInto()
    {
        Recoil::run(
            function () {
                $statement = $this->pdo->query($this->selectQuery);
                $expected = new stdClass;
                $statement->setFetchMode(PDO::FETCH_INTO, $expected);
                $statement->fetch(PDO::FETCH_INTO);

                $connection = (yield $this->factory->connect($this->dsn));
                $statement = (yield $connection->query($this->selectQuery));

                $target = new stdClass;

                yield $statement->setFetchMode(PDO::FETCH_INTO, $target);
                $result = (yield $statement->fetch(PDO::FETCH_INTO));

                $this->assertEquals($expected, $result);
                $this->assertSame($target, $result);
            }
        );
    }

    public function testStatementFetchIntoFailure()
    {
        Recoil::run(
            function () {
                $connection = (yield $this->factory->connect($this->dsn));
                $statement = (yield $connection->query($this->selectQuery));

                $this->setExpectedException(
                    'LogicException',
                    'PDO::FETCH_INTO must be configured as the DEFAULT fetch mode in order to use PDO::FETCH_INTO.'
                );

                yield $statement->fetch(PDO::FETCH_INTO);
            }
        );
    }

    public function testStatementFetchObject()
    {
        Recoil::run(
            function () {
                $statement = $this->pdo->query($this->selectQuery);
                $expected = $statement->fetchObject();

                $connection = (yield $this->factory->connect($this->dsn));
                $statement = (yield $connection->query($this->selectQuery));

                $this->assertEquals(
                    $expected,
                    (yield $statement->fetchObject())
                );

                yield $statement->fetchObject();
                yield $statement->fetchObject();

                $this->assertFalse(yield $statement->fetchObject());
            }
        );
    }

    public function testStatementFetchObjectWithCustomClass()
    {
        Recoil::run(
            function () {
                $statement = $this->pdo->query($this->selectQuery);
                $expected = $statement->fetchObject(TestRowClass::CLASS);

                $connection = (yield $this->factory->connect($this->dsn));
                $statement = (yield $connection->query($this->selectQuery));

                $this->assertEquals(
                    $expected,
                    (yield $statement->fetchObject(TestRowClass::CLASS))
                );
            }
        );
    }

    public function testStatementFetchObjectWithConstructorArguments()
    {
        Recoil::run(
            function () {
                $statement = $this->pdo->query($this->selectQuery);
                $expected = $statement->fetchObject(
                    TestRowClass::CLASS,
                    [1, 2, 3]
                );

                $connection = (yield $this->factory->connect($this->dsn));
                $statement = (yield $connection->query($this->selectQuery));

                $result = (yield $statement->fetchObject(
                    TestRowClass::CLASS,
                    [1, 2, 3]
                ));

                $this->assertEquals($expected, $result);
            }
        );
    }

    public function testStatementFetchAll()
    {
        Recoil::run(
            function () {
                $statement = $this->pdo->query($this->selectQuery);
                $expected = $statement->fetchAll();

                $connection = (yield $this->factory->connect($this->dsn));
                $statement = (yield $connection->query($this->selectQuery));

                $this->assertEquals(
                    $expected,
                    (yield $statement->fetchAll())
                );
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
                $statement = $this->pdo->query($this->selectQuery);
                $expected = $statement->fetchAll($mode);

                $connection = (yield $this->factory->connect($this->dsn));
                $statement = (yield $connection->query($this->selectQuery));

                $this->assertEquals(
                    $expected,
                    (yield $statement->fetchAll($mode))
                );
            }
        );
    }

    // public function testStatementFetchAllBound()
    // {
    // }

    // public function testStatementFetchAllLazy()
    // {
    // }

    public function testStatementFetchAllClass()
    {
        Recoil::run(
            function () {
                $statement = $this->pdo->query($this->selectQuery);
                $expected = $statement->fetchAll(
                    PDO::FETCH_CLASS,
                    TestRowClass::CLASS,
                    [1, 2, 3]
                );

                $connection = (yield $this->factory->connect($this->dsn));
                $statement = (yield $connection->query($this->selectQuery));

                $result = (yield $statement->fetchAll(
                    PDO::FETCH_CLASS,
                    TestRowClass::CLASS,
                    [1, 2, 3]
                ));

                $this->assertEquals($expected, $result);
            }
        );
    }

    public function testStatementFetchColumn()
    {
        Recoil::run(
            function () {
                $connection = (yield $this->factory->connect($this->dsn));
                $statement = (yield $connection->query($this->selectQuery));

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

                $this->assertEquals(
                    'bob',
                    $this->pdo
                        ->query('SELECT name FROM test WHERE id = 2')
                        ->fetchColumn()
                );
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

                $this->assertEquals(
                    'bob',
                    $this->pdo
                        ->query('SELECT name FROM test WHERE id = 2')
                        ->fetchColumn()
                );
            }
        );
    }

    // public function testStatementBindParam()
    // {
    // }

    // public function testStatementBindColumn()
    // {
    // }

    public function testStatementColumnCount()
    {
        Recoil::run(
            function () {
                $connection = (yield $this->factory->connect($this->dsn));
                $statement = (yield $connection->query($this->selectQuery));

                $this->assertEquals(3, (yield $statement->columnCount()));
            }
        );
    }

    public function testStatementRowCount()
    {
        Recoil::run(
            function () {
                $connection = (yield $this->factory->connect($this->dsn));
                $statement = (yield $connection->query($this->deleteQuery));

                $this->assertEquals(3, (yield $statement->rowCount()));
            }
        );
    }

    public function testStatementGetColumnMeta()
    {
        Recoil::run(
            function () {
                $statement = $this->pdo->query($this->selectQuery);
                $expected = $statement->getcolumnMeta(1);

                $connection = (yield $this->factory->connect($this->dsn));
                $statement = (yield $connection->query($this->selectQuery));
                $metaData = (yield $statement->getColumnMeta(1));

                $this->assertEquals($expected, $metaData);
            }
        );
    }

    // public function testStatementNextRowset()
    // {
    // }

    // public function testStatementCloseCursor()
    // {
    // }

    // public function testStatementSetAttribute()
    // {
    // }

    // public function testStatementGetAttribute()
    // {
    // }

    public function testStatementErrorCode()
    {
        Recoil::run(
            function () {
                $connection = (yield $this->factory->connect($this->dsn));
                $statement = (yield $connection->prepare($this->selectQuery));

                yield $statement->bindValue(1, 'foo');
                yield $statement->execute();

                $this->assertSame(
                    'HY000',
                    (yield $statement->errorCode())
                );
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

                $this->assertSame(
                    ['HY000', 25, 'bind or column index out of range'],
                    (yield $statement->errorInfo())
                );
            }
        );
    }

    public function testStatementQueryString()
    {
        Recoil::run(
            function () {
                $connection = (yield $this->factory->connect($this->dsn));
                $statement = (yield $connection->prepare($this->selectQuery));

                $this->assertEquals(
                    $this->selectQuery,
                    (yield $statement->queryString())
                );
            }
        );
    }

    public function testStatementDebugDumpParams()
    {
        Recoil::run(
            function () {
                $output  = 'SQL: [31] SELECT id, name, name FROM test' . PHP_EOL;
                $output .= 'Params:  0' . PHP_EOL;
                $this->expectOutputString($output);

                $connection = (yield $this->factory->connect($this->dsn));
                $statement = (yield $connection->prepare($this->selectQuery));

                yield $statement->debugDumpParams();
            }
        );
    }
}
