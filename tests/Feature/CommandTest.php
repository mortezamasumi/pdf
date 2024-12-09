<?php

use Illuminate\Support\Facades\File;
use Mortezamasumi\Pdf\Console\PdfCommand;
use Mortezamasumi\Pdf\Facades\Pdf;

it('clears all temporary pdf files older than 1 day in /public/temp', function () {
    $pdf = Pdf::create();

    $pdf->Output($pdf->getPath(), 'F');

    expect(File::exists($pdf->getPath()))->toBeTrue();

    touch($pdf->getPath(), strtotime(now()->subDays(2)->format('Y-m-d H:i:s')));

    $this->artisan(PdfCommand::class);

    expect(File::exists($pdf->getPath()))->toBeFalse();
});
