<?php

namespace bskl\LaravelMpSms;

use Illuminate\Support\Facades\Facade as IlluminateFacade;

/**
 * Class Facade
 *
 * @package bskl\LaravelMpSms
 */
class Facade extends IlluminateFacade
{
    /**
     * @param   void
     * @return  string
     */
    protected static function getFacadeAccessor()
    {
        return 'mpsms';
    }
}
