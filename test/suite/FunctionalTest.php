<?php
namespace Recoil\Database;

use PHPUnit_Framework_TestCase;

class FunctionalTest extends PHPUnit_Framework_TestCase
{
    use FunctionalTestTrait;

    public function createFactory()
    {
        return new ConnectionFactory;
    }
}
