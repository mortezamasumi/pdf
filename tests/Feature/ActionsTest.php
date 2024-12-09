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
