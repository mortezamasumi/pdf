<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Mortezamasumi\PdfReport\Pages\ReportPage;
use Tests\Services\ActionPage;
use Tests\Services\Product;
use Tests\Services\TestComponent;
use Tests\Services\TestPageReporter;
use Tests\Services\TestTableReporter;

uses(RefreshDatabase::class);

afterEach(function () {
    if (Storage::exists('/public/temp')) {
        Storage::deleteDirectory('/public/temp');
    }
});

it('render livewire component successfully', function () {
    Livewire::withQueryParams(['test' => 'a test message'])
        ->test(TestComponent::class)
        ->assertSee('a test message')
        ->assertStatus(200);
});

it('render report page correctly', function () {
    Livewire::test(ReportPage::class)
        ->assertSee(__('pdf-report::pdf-report.report'));
});

it('change locale is results render report page correctly', function () {
    $this->app['config']->set('app.locale', 'fa');

    Livewire::test(ReportPage::class)
        ->assertSee(__('pdf-report::pdf-report.report'));
});

it('can create pdf instance and store the pdf file in temp folder', function () {
    $pdf = \Mortezamasumi\PdfReport\Facades\PdfReport::create();

    $pdf->Output($pdf->getPath(), 'F');

    $mimeType = File::mimeType($pdf->getPath());

    expect($mimeType)->toBe('application/pdf');
});

it('created pdf contains correct text', function () {
    $pdf = \Mortezamasumi\PdfReport\Facades\PdfReport::create();

    $text = 'this is text to insert inside html';

    $pdf->WriteHTML($text);

    $pdf->Output($pdf->getPath(), 'F');

    $parser = new \Smalot\PdfParser\Parser();

    $parsed = $parser->parseFile($pdf->getPath());

    expect($parsed->getText())->toContain($text);
});

it('report page embeds pdf url correctly', function () {
    $pdf = \Mortezamasumi\PdfReport\Facades\PdfReport::create();

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
            ['name' => 'this is text for column1'],
            ['type' => 'this is text for column2'],
        ]),
        'returnUrl' => null,
        'selectedColumns' => collect(TestTableReporter::getColumns())->mapWithKeys(fn($column): array => [$column->getName() => $column->getLabel()])->all(),
        'options' => null,
    ]);

    $parser = new \Smalot\PdfParser\Parser();

    $parsed = $parser->parseFile($reporter->getPdf()->getPath());

    expect($parsed->getText())->toContain('Row');
    expect($parsed->getText())->toContain('this is text for column1');
    expect($parsed->getText())->toContain('this is text for column2');
});

it('can instantiate Reporter class which contains a custom page', function () {
    $reporter = app(TestPageReporter::class);

    $parser = new \Smalot\PdfParser\Parser();

    $parsed = $parser->parseFile($reporter->getPdf()->getPath());

    expect($parsed->getText())->toContain('test text for cell');
    expect($parsed->getText())->toContain('test text for text');
    expect($parsed->getText())->toContain('test text for textBox');
});

it('can create Products table', function () {
    factory(Product::class, 20)->create();

    expect(Product::all()->count())->toBe(20);
});

it('can call standalone report action', function () {
    Livewire::test(ActionPage::class)
        ->callAction('standalone-action')
        ->assertHasNoActionErrors();
});

it('can call report header action', function () {
    factory(Product::class, 20)->create();

    Livewire::test(ActionPage::class)
        ->callTableAction('header-action')
        ->assertHasNoActionErrors();
});

it('can call report table action', function () {
    // $product = factory(Product::class, 1)->create();

    Livewire::test(ActionPage::class)
        // right now can not inject record into action
        // ->callTableAction('table-action', $product)
        ->callTableAction('table-action')
        ->assertHasNoActionErrors();
});

it('can call report form action', function () {
    Livewire::test(ActionPage::class)
        ->callFormComponentAction('form-action', 'form-action')
        ->assertHasNoActionErrors();
});

it('can call report bulk table action', function () {
    $products = factory(Product::class, 20)->create();

    Livewire::test(ActionPage::class)
        ->callTableBulkAction('bulk-action', $products)
        ->assertHasNoActionErrors();
});
