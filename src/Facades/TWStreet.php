<?php

namespace Hsquare\TWStreet\Facades;

use Illuminate\Support\Facades\Facade;

class TWStreet extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'twstreet';
    }
}
