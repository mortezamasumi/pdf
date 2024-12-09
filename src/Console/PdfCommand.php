<?php

namespace Mortezamasumi\Pdf\Console;

use Illuminate\Console\Command;
use Mortezamasumi\Pdf\Facades\Pdf;

class PdfCommand extends Command
{
    protected $signature = 'pdf:clear';

    protected $description = 'clears all temporary pdf files older than 1 day in /public/temp';

    public function handle(): void
    {
        Pdf::clear(days: 1);
    }
}
