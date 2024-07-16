<?php

namespace Rud99\SberSbp {

    use Illuminate\Support\Carbon;
    use Illuminate\Support\Facades\Cache;

    class LaravelCacheAdapter implements ICacheAdapter
    {
        public function get(string $sKey, float $nSeconds, callable $fCallback)
        {
            return Cache::remember($sKey, Carbon::now()->addSeconds($nSeconds), $fCallback);
        }
    }
}

