<?php

namespace Mortezamasumi\PdfReport\Console;

use Illuminate\Console\Command;
use Mortezamasumi\PdfReport\Facades\PdfReport;

class PdfReportCommand extends Command
{
    protected $signature = 'pdf-report:clear';

    protected $description = 'clears all temporary pdf files older than 1 day in /public/temp';

    public function handle(): void
    {
        PdfReport::clear();
    }
}
