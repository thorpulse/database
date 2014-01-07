<?php
namespace Recoil\Database;

use PHPUnit_Framework_TestCase;

/**
 * This test uses a testing-only synchronous connection factory that does not
 * use a sub-process. This allows PHPUnit to produce accurate coverage information
 * for code that would normally be executed in the sub-process.
 */
class CoverageTest extends PHPUnit_Framework_TestCase
{
    use FunctionalTestTrait;

    public function createFactory()
    {
        return new SynchronousConnectionFactory;
    }
}
