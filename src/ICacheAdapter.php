<?php

namespace Rud99\SberSbp;
interface ICacheAdapter
{
    function get(string $sKey, float $nSeconds, callable $fCallback);
}


