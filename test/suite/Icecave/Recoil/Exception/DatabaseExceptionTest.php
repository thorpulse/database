<?php
namespace Icecave\Recoil\Database\Exception;

use Exception;
use Icecave\Recoil\Recoil;
use PHPUnit_Framework_TestCase;

class DatabaseExceptionTest extends PHPUnit_Framework_TestCase
{
    public function testException()
    {
        $exception = new DatabaseException('foo', '99999', array('bar', 'baz'));

        $this->assertSame('foo', $exception->getMessage());
        $this->assertSame('99999', $exception->getCode());
        $this->assertSame(array('bar', 'baz'), $exception->errorInfo);
    }

    public function testExceptionDefaultErrorInfo()
    {
        $exception = new DatabaseException('foo', '99999');

        $this->assertSame('foo', $exception->getMessage());
        $this->assertSame('99999', $exception->getCode());
        $this->assertSame(array('99999', '99999', 'foo'), $exception->errorInfo);
    }

    public function testExceptionDefaults()
    {
        $exception = new DatabaseException('foo');

        $this->assertSame('foo', $exception->getMessage());
        $this->assertSame(0, $exception->getCode());
        $this->assertNull($exception->errorInfo);
    }
}
