<?php

namespace Mortezamasumi\PdfReport\Facades;

use Illuminate\Support\Facades\Facade;

class PdfReport extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'PdfReport';
    }
}
