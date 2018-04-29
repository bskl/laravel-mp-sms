<?php

namespace Bskl\MpSms;

use Illuminate\Support\Facades\Facade as IlluminateFacade;

/**
 * Class Facade.
 */
class Facade extends IlluminateFacade
{
    /**
     * @param   void
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'mpsms';
    }
}
