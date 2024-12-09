<?php

namespace Mortezamasumi\PdfReport\Actions;

use Filament\Tables\Actions\BulkAction;
use Mortezamasumi\PdfReport\Concerns\CanCreateReport;

class ReportBulkAction extends BulkAction
{
    use CanCreateReport;
}
