<?php

namespace Tests\Services;

use Mortezamasumi\Pdf\Reports\ReportColumn;
use Mortezamasumi\Pdf\Reports\Reporter;
use Mortezamasumi\Pdf\Reports\RowNumberColumn;

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
