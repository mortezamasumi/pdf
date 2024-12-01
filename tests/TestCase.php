<?php

namespace Mortezamasumi\PdfReport\Tests;

use Mortezamasumi\SmsChannel\PdfReportServiceProvider;

abstract class TestCase extends \Orchestra\Testbench\TestCase
{
    protected function getPackageProviders($app): array
    {
        return [PdfReportServiceProvider::class];
    }
}
