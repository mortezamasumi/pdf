<?php

namespace Mortezamasumi\PdfReport\Actions;

use Filament\Forms\Components\Actions\Action;
use Mortezamasumi\PdfReport\Concerns\CanCreateReport;

class ReportFormAction extends Action
{
    use CanCreateReport;
}
