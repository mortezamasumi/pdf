<?php

namespace Mortezamasumi\PdfReport\Facades;

use Illuminate\Support\Facades\Facade;
use Mortezamasumi\PdfReport\PdfReportService;

class PdfReport extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return PdfReportService::class;
    }
}
