<?php

namespace EorPlatform\LaravelPandaDoc\Facades;

use Illuminate\Support\Facades\Facade;

class PandaDoc extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'pandadoc';
    }
}
