<?php

use Livewire\Livewire;
use Mortezamasumi\PdfReport\Pages\ReportPage;
use Tests\Services\TestComponent;

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
