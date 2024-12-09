<?php

namespace Tests\Services;

use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Filament\Forms;
use Filament\Tables;
use Mortezamasumi\Pdf\Actions\ReportAction;
use Mortezamasumi\Pdf\Actions\ReportBulkAction;
use Mortezamasumi\Pdf\Actions\ReportFormAction;
use Mortezamasumi\Pdf\Actions\ReportHeaderAction;
use Mortezamasumi\Pdf\Actions\ReportTableAction;
use Tests\Services\Product;
use Tests\Services\TestTableReporter;

class ActionPage extends Page implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    protected static string $view = 'action-page';

    public ?array $data = [];

    protected function getHeaderActions(): array
    {
        return [
            ReportAction::make('standalone-action')->reporter(TestTableReporter::class),
        ];
    }

    public function form(Form $form): Form
    {
        return $form
                   ->schema([
                       Forms\Components\TextInput::make('form-action')
                           ->suffixAction(ReportFormAction::make('form-action')->reporter(TestTableReporter::class))
                   ])
                   ->statePath('data');
    }

    public function table(Table $table): Table
    {
        return $table
                   ->query(Product::query())
                   ->columns([
                       Tables\Columns\TextColumn::make('name'),
                   ])
                   ->filters([
                       // ...
                   ])
                   ->headerActions([
                       ReportHeaderAction::make('header-action')->reporter(TestTableReporter::class),
                   ])
                   ->actions([
                       ReportTableAction::make('table-action')->reporter(TestTableReporter::class),
                   ])
                   ->bulkActions([
                       ReportBulkAction::make('bulk-action')->reporter(TestTableReporter::class),
                   ]);
    }
}
