<?php

namespace Tests\Services;

use Mortezamasumi\PdfReport\Reports\ReportColumn;
use Mortezamasumi\PdfReport\Reports\Reporter;
use Mortezamasumi\PdfReport\Reports\RowNumberColumn;

class TestTableReporter extends Reporter
{
    public static function getColumns(): array
    {
        return [
            RowNumberColumn::make('row'),
            ReportColumn::make('name'),
            ReportColumn::make('type'),
        ];
    }
}
