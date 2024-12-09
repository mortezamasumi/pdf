<?php

namespace Mortezamasumi\PdfReport\Actions;

use Filament\Tables\Actions\Action;
use Mortezamasumi\PdfReport\Concerns\CanCreateReport;

class ReportTableAction extends Action
{
    /** right now can not inject Model into action method */
    use CanCreateReport;
}
