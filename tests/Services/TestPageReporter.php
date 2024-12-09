<?php

namespace Tests\Services;

use Mortezamasumi\Pdf\Reports\ReportColumn;
use Mortezamasumi\Pdf\Reports\Reporter;
use Mortezamasumi\Pdf\Reports\RowNumberColumn;

class TestPageReporter extends Reporter
{
    public function getPage(): void
    {
        $this->cell('test text for cell');
        $this->text('test text for text');
        $this->textBox('test text for textBox');

        $this->addPage();
    }
}
