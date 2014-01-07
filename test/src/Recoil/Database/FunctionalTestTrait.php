<?php
namespace Recoil\Database;

use PDO;
use Recoil\Recoil;

trait FunctionalTestTrait
{
    use ConnectionTestTrait;
    use StatementTestTrait;

    public function setUp()
    {
        $this->factory = $this->createFactory();
        $this->path    = tempnam(sys_get_temp_dir(), 'recoil-database-');
        $this->dsn     = 'sqlite:' . $this->path;
        $this->pdo     = new PDO($this->dsn);

        $this->pdo->exec(
            'CREATE TABLE test (
                id INTEGER PRIMARY KEY,
                name STRING NOT NULL
            )'
        );

        $this->pdo->exec('INSERT INTO test VALUES (null, "foo")');
        $this->pdo->exec('INSERT INTO test VALUES (null, "bar")');
        $this->pdo->exec('INSERT INTO test VALUES (null, "spam")');

        $this->selectQuery = 'SELECT id, name, name FROM test';
        $this->deleteQuery = 'DELETE FROM test';
    }

    public function tearDown()
    {
        if (file_exists($this->path)) {
            unlink($this->path);
        }
    }

    abstract public function createFactory();
}
