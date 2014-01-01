<?php
namespace Icecave\Recoil\Database\Detail;

abstract class ResponseType
{
    const VALUE     = 0x10;
    const STATEMENT = 0x11;
    const EXCEPTION = 0x12;
}
