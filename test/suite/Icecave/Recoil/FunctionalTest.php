<?php
namespace Icecave\Recoil\Database;

use PHPUnit_Framework_TestCase;

class FunctionalTest extends PHPUnit_Framework_TestCase
{
    use FunctionalConnectionTestTrait;
    use FunctionalStatementTestTrait;

    public function createFactory()
    {
        return new ConnectionFactory;
    }
}
