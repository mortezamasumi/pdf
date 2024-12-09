<?php

namespace Tests\Services;

use Mortezamasumi\Pdf\Reports\Reporter;

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
