<?php
namespace Recoil\Database;

class TestRowClass
{
    public function __construct()
    {
        if ($arguments = func_get_args()) {
            $this->arguments = $arguments;
        }
    }
}
