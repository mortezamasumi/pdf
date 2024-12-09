<?php

namespace Mortezamasumi\Pdf\Actions;

use Filament\Tables\Actions\Action;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Livewire\Component;
use Mortezamasumi\Pdf\Concerns\CanCreateReport;

class ReportTableAction extends Action
{
    /** right now can not inject Model into action method */
    use CanCreateReport;

    public function getActionLabel(Component $livewire): string
    {
        return $this->getPluralModelLabel();
    }

    public function getActionHeading(Component $livewire): string
    {
        return $this->getPluralModelLabel();
    }

    public function getActionRecords(Component $livewire): Collection
    {
        $reporter = $this->getReporter();

        if ($livewire instanceof HasTable) {
            if (!$this->hasForceUseReporterModel()) {
                $query = $livewire->getTableQueryForExport();
            } else {
                $query = class_exists($reporter::getModel()) ? $reporter::getModel()::query() : null;
            }
        } else {
            $query = class_exists($reporter::getModel()) ? $reporter::getModel()::query() : null;
        }

        if ($query) {
            $query = $reporter::modifyQuery($query);
            if ($this->modifyQueryUsing) {
                $query = $this->evaluate($this->modifyQueryUsing, [
                    'query' => $query,
                ]) ?? $query;
            }

            $records = collect(Arr::wrap($this->getRecord()));
        } else {
            $records = collect([]);
        }

        return $records;
    }
}
