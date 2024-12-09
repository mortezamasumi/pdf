<?php

namespace Mortezamasumi\Pdf\Concerns;

use Filament\Actions\MountableAction;
use Filament\Facades\Filament;
use Filament\Support\Enums\Alignment;
use Filament\Support\Enums\MaxWidth;
use Filament\Support\Facades\FilamentIcon;
use Filament\Forms;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Livewire\Component;
use Mortezamasumi\Pdf\Actions\ReportAction;
use Mortezamasumi\Pdf\Actions\ReportBulkAction;
use Mortezamasumi\Pdf\Actions\ReportFormAction;
use Mortezamasumi\Pdf\Actions\ReportHeaderAction;
use Mortezamasumi\Pdf\Actions\ReportTableAction;
use Mortezamasumi\Pdf\Reports\ReportColumn;
use Mortezamasumi\Pdf\Reports\Reporter;
use Closure;

trait CanCreateReport
{
    /**
     * @var class-string<Reporter>
     */
    protected string $reporter;

    protected bool|Closure $hasSelectableColumns = true;

    protected bool|Closure $hasForceUseReporterModel = false;

    protected bool|Closure $hasRequiredConfirmation = false;

    /**
     * @var array<string, mixed> | Closure
     */
    protected array|Closure $options = [];

    protected ?Closure $modifyQueryUsing = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->label(function (ReportAction|ReportFormAction|ReportBulkAction|ReportTableAction|ReportHeaderAction $action, Component $livewire): string {
            return __('pdf::pdf.label', ['label' => $action->getActionLabel($livewire)]);
        });

        $this->modalHeading(function (ReportAction|ReportFormAction|ReportBulkAction|ReportTableAction|ReportHeaderAction $action, Component $livewire): string {
            return __('pdf::pdf.heading', ['heading' => $action->getActionHeading($livewire)]);
        });

        $this->modalSubmitActionLabel(__('pdf::pdf.action'));

        $this->groupedIcon('heroicon-m-printer');

        $this->form(fn(ReportAction|ReportFormAction|ReportBulkAction|ReportTableAction|ReportHeaderAction $action): array => [
            Forms\Components\Fieldset::make(__('pdf::pdf.columns'))
                ->columns(1)
                ->inlineLabel()
                ->schema(function () use ($action): array {
                    return array_map(
                        fn(ReportColumn $column): Forms\Components\Split => Forms\Components\Split::make([
                            Forms\Components\Checkbox::make('isEnabled')
                                ->hiddenLabel()
                                ->default(fn() => $column->isEnabledByDefault())
                                ->grow(false),
                            Forms\Components\TextInput::make('label')
                                ->hiddenLabel()
                                ->default($column->getLabel())
                                ->readOnly(),
                        ])->verticallyAlignCenter()
                          ->statePath($column->getName()),
                        $action->getReporter()::getColumns(),
                    );
                })
                ->statePath('selectedColumns')
                ->visible($this->hasSelectableColumns()),
            ...$action->getReporter()::getOptionsFormComponents(),
        ]);

        $this->action(function (ReportAction|ReportFormAction|ReportBulkAction|ReportTableAction|ReportHeaderAction $action, array $data, Component $livewire) {
            $options = array_merge(
                $action->getOptions(),
                Arr::except($data, ['selectedColumns']),
            );

            $reporter = $action->getReporter();

            $selectedColumns = collect($reporter::getColumns())->mapWithKeys(fn(ReportColumn $column): array => [$column->getName() => $column->getLabel()])->all();

            if ($action->hasSelectableColumns()) {
                $temp = collect($data['selectedColumns'])
                            ->dot()
                            ->reduce(fn(Collection $carry, mixed $value, string $key): Collection => $carry->mergeRecursive([
                                Str::beforeLast($key, '.') => [Str::afterLast($key, '.') => $value],
                            ]), collect())
                            ->filter(fn(array $column): bool => $column['isEnabled'] ?? false)
                            ->mapWithKeys(fn(array $column, string $columnName): array => [$columnName => $column['label']])
                            ->all();

                $selectedColumns = array_merge(array_flip(array_intersect(array_keys($selectedColumns), array_keys($temp))), $temp);
            }

            $reporter = app($reporter, [
                'records'         => $action->getActionRecords($livewire),
                'returnUrl'       => $reporter::getReturnUrl() ?: (method_exists($livewire, 'getUrl') ? $livewire->getUrl() : Filament::getCurrentPanel()->getUrl()),
                'selectedColumns' => $selectedColumns,
                'options'         => $options,
            ]);
        });

        $this->color('gray');

        $this->modalWidth('xl');

        $this->modalHidden(
            fn(ReportAction|ReportFormAction|ReportBulkAction|ReportTableAction|ReportHeaderAction $action) => !count($action->getReporter()::getOptionsFormComponents()) &&
                !$action->hasSelectableColumns() &&
                !$this->hasRequiredConfirmation
        );
    }

    public function requiresConfirmation(bool|Closure $condition = true): static
    {
        $this->modalAlignment(fn(MountableAction $action): ?Alignment              => $action->evaluate($condition) ? Alignment::Center : null);
        $this->modalFooterActionsAlignment(fn(MountableAction $action): ?Alignment => $action->evaluate($condition) ? Alignment::Center : null);
        $this->modalIcon(fn(MountableAction $action): ?string                      => $action->evaluate($condition) ? (FilamentIcon::resolve('actions::modal.confirmation') ?? 'heroicon-o-exclamation-triangle') : null);
        $this->modalHeading ??= fn(MountableAction $action): string|Htmlable|null => $action->evaluate($condition) ? $action->getLabel() : null;
        $this->modalDescription(fn(MountableAction $action): ?string       => $action->evaluate($condition) ? __('filament-actions::modal.confirmation') : null);
        $this->modalSubmitActionLabel(fn(MountableAction $action): ?string => $action->evaluate($condition) ? __('filament-actions::modal.actions.confirm.label') : null);
        $this->modalWidth(fn(MountableAction $action): ?MaxWidth           => $action->evaluate($condition) ? MaxWidth::Medium : null);

        $this->hasRequiredConfirmation = $this->evaluate($condition);

        return $this;
    }

    public static function getDefaultName(): ?string
    {
        return 'report';
    }

    /**
     * @param  class-string<Reporter>  $reporter
     */
    public function reporter(string $reporter): static
    {
        $this->reporter = $reporter;

        return $this;
    }

    /**
     * @return class-string<Reporter>
     */
    public function getReporter(): string
    {
        return $this->reporter;
    }

    public function options(array|Closure $options): static
    {
        $this->options = $options;

        return $this;
    }

    /**
     * @return array<string, mixed>
     */
    public function getOptions(): array
    {
        return $this->evaluate($this->options);
    }

    public function modifyQueryUsing(?Closure $callback): static
    {
        $this->modifyQueryUsing = $callback;

        return $this;
    }

    public function selectableColumns(bool|Closure $condition = true): static
    {
        $this->hasSelectableColumns = $condition;

        return $this;
    }

    public function hasSelectableColumns(): bool
    {
        return (bool) $this->evaluate($this->hasSelectableColumns);
    }

    public function forceUseReporterModel(bool|Closure $condition = true): static
    {
        $this->hasforceUseReporterModel = $condition;

        return $this;
    }

    public function hasForceUseReporterModel(): bool
    {
        return (bool) $this->evaluate($this->hasForceUseReporterModel);
    }
}
