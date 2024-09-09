<?php

namespace WeblaborMx\Collaboration\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \WeblaborMx\Collaboration\Collaboration
 */
class Collaboration extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \WeblaborMx\Collaboration\Collaboration::class;
    }
}
