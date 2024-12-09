<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Mortezamasumi\Pdf\Facades\Pdf;
use Tests\Services\ActionPage;
use Tests\Services\Product;

uses(RefreshDatabase::class);

afterEach(function () {
    Pdf::clear(days: 0);
});

it('can create Products table', function () {
    factory(Product::class, 20)->create();

    expect(Product::all()->count())->toBe(20);
});

it('can call standalone report action', function () {
    Livewire::test(ActionPage::class)
        ->callAction('standalone-action')
        ->assertHasNoActionErrors()
        ->assertSee('Report');
});

it('can call report header action', function () {
    factory(Product::class, 20)->create();

    Livewire::test(ActionPage::class)
        ->callTableAction('header-action')
        ->assertHasNoActionErrors()
        ->assertSee('Report products');
});

it('can call report table action', function () {
    // $product = factory(Product::class, 1)->create();

    Livewire::test(ActionPage::class)
        // right now can not inject record into action
        // ->callTableAction('table-action', $product)
        ->callTableAction('table-action')
        ->assertHasNoActionErrors()
        ->assertSee('Report');
});

it('can call report form action', function () {
    Livewire::test(ActionPage::class)
        ->callFormComponentAction('form-action', 'form-action')
        ->assertHasNoActionErrors()
        ->assertSee('Report');
});

it('can call report bulk table action', function () {
    $products = factory(Product::class, 20)->create();

    Livewire::test(ActionPage::class)
        ->callTableBulkAction('bulk-action', $products)
        ->assertHasNoActionErrors()
        ->assertSee('Report products');
});
