<?php

namespace Mortezamasumi\PdfReport\Actions;

use Filament\Actions\Action;
use Mortezamasumi\PdfReport\Concerns\CanCreateReport;

class ReportAction extends Action
{
    use CanCreateReport;
}
