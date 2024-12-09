<?php

use Illuminate\Support\Facades\File;
use Livewire\Livewire;
use Mortezamasumi\Pdf\Facades\Pdf;
use Mortezamasumi\Pdf\Pages\ReportPage;
use Smalot\PdfParser\Parser;
use Tests\Services\TestPageReporter;
use Tests\Services\TestTableReporter;

afterEach(function () {
    Pdf::clear(days: 0);
});

it('can create pdf instance and store the pdf file in temp folder', function () {
    $pdf = Pdf::create();

    $pdf->Output($pdf->getPath(), 'F');

    $mimeType = File::mimeType($pdf->getPath());

    expect($mimeType)->toBe('application/pdf');
});

it('created pdf contains correct text', function () {
    $pdf = Pdf::create();

    $text = 'this is text to insert inside html';

    $pdf->WriteHTML($text);

    $pdf->Output($pdf->getPath(), 'F');

    $parser = new Parser();

    $parsed = $parser->parseFile($pdf->getPath());

    expect($parsed->getText())->toContain($text);
});

it('report page embeds pdf url correctly', function () {
    $pdf = Pdf::create();

    Livewire::withQueryParams([
        'path'  => $pdf->getEmbedPath(),
        'title' => 'test',
        'back'  => '/',
    ])->test(ReportPage::class)
      ->assertSeeHtml('<embed src="' . $pdf->getEmbedPath() . '"');
});

it('can instantiate Reporter class which contains a table', function () {
    $reporter = app(TestTableReporter::class, [
        'records' => collect([
            ['name' => 'this is taext for column1'],
            ['type' => 'this is text for column2'],
        ]),
        'returnUrl' => null,
        'selectedColumns' => collect(TestTableReporter::getColumns())->mapWithKeys(fn($column): array => [$column->getName() => $column->getLabel()])->all(),
        'options' => null,
    ]);

    $parser = new Parser();

    $parsed = $parser->parseFile($reporter->getPdf()->getPath());

    expect($parsed->getText())->toContain('Row');
    expect($parsed->getText())->toContain('this is text for column1');
    expect($parsed->getText())->toContain('this is text for column2');
});

it('can instantiate Reporter class which contains a custom page', function () {
    $reporter = app(TestPageReporter::class);

    $parser = new Parser();

    $parsed = $parser->parseFile($reporter->getPdf()->getPath());

    expect($parsed->getText())->toContain('test text for cell');
    expect($parsed->getText())->toContain('test text for text');
    expect($parsed->getText())->toContain('test text for textBox');
});
