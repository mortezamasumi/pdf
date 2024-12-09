<?php

namespace Tests\Services;

use Mortezamasumi\PdfReport\Reports\ReportColumn;
use Mortezamasumi\PdfReport\Reports\Reporter;
use Mortezamasumi\PdfReport\Reports\RowNumberColumn;

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
