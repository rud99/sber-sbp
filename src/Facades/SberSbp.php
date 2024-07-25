<?php

namespace Rud99\SberSbp\Facades;

use Illuminate\Support\Facades\Facade;

/** @see Order */
/** @see CreateResult */
/** @see CancelResult */
/** @see OrderItem */
/** @see RevokeResult */
class SberSbp extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return 'sber-sbp';
    }
}
