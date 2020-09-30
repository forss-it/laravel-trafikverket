<?php

namespace KFVIT\LaravelTrafikverket;

class Facade extends \Illuminate\Support\Facades\Facade
{
    /**
     * {@inheritDoc}
     */
    protected static function getFacadeAccessor()
    {
        return 'trafikverket';
    }
}
