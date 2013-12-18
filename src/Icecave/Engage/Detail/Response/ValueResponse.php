<?php
namespace Icecave\Engage\Detail\Response;

class ValueResponse implements ResponseInterface
{
    public function __construct($value)
    {
        $this->value = $value;
    }

    public function get()
    {
        return $this->value;
    }

    private $value;
}
