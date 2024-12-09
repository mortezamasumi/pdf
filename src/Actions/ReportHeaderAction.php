<?php

namespace Mortezamasumi\PdfReport\Actions;

use Filament\Tables\Actions\Action;
use Mortezamasumi\PdfReport\Concerns\CanCreateReport;

class ReportHeaderAction extends Action
{
    use CanCreateReport;
}
