<?php

use Illuminate\Support\Facades\File;
use Mortezamasumi\PdfReport\Console\PdfReportCommand;

it('clears all temporary pdf files older than 1 day in /public/temp', function () {
    $pdf = \Mortezamasumi\PdfReport\Facades\PdfReport::create();

    $pdf->Output($pdf->getPath(), 'F');

    expect(File::exists($pdf->getPath()))->toBeTrue();

    touch($pdf->getPath(), strtotime(now()->subDays(2)->format('Y-m-d H:i:s')));

    $this->artisan(PdfReportCommand::class);

    expect(File::exists($pdf->getPath()))->toBeFalse();
});
