<?php

namespace Moinul\LinkScanner\Facades;

use Illuminate\Support\Facades\Facade;

class LinkScanner extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'linkscanner';
    }
}
