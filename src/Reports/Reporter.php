<?php

namespace Mortezamasumi\PdfReport\Reports;

use Filament\Support\Concerns\EvaluatesClosures;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Mortezamasumi\PdfReport\Traits\HasPdfReport;
use Closure;

abstract class Reporter
{
    use HasPdfReport;
    use EvaluatesClosures;

    /**
     * @var array<ReportColumn>
     */
    protected array $cachedColumns;

    protected mixed $record;

    protected static ?string $model = null;

    /**
     * @param  array<string, mixed>  $options
     */
    public function __construct(
        protected ?Collection $records,
        protected ?string $returnUrl,
        protected ?array $selectedColumns,
        protected ?array $options,
    ) {
        $this->createReport();
    }

    public function createReport(): void
    {
        $this->createPdf();

        $this->beforePages();

        if (method_exists($this, 'getPage')) {
            $this->pageReport($this->records);
        } else {
            $this->tableReport($this->records);
        }

        $this->afterPages();

        $this->pdf->renderPdf($this->getPdfViewerTitle(), $this->returnUrl);
    }

    public function pageReport(Collection|array|null $items): void
    {
        if (isset($items) && count($items)) {
            $this
                ->processItems($items)
                ->each(function ($item) {
                    $this->setRecord($item);

                    /** @disregard */
                    $this->getPage();
                });

            $this->adjustPages();
        } else {
            /** @disregard */
            $this->getPage();
        }
    }

    public function tableReport(Collection|array|null $items, string|Closure $beforeHtml = null, string|Closure $afterHtml = null, string $classes = ''): void
    {
        $headerHtml =
            Str::of('<tr class="header-row">')
                ->append(
                    collect($this->getColumnsTitle())
                        ->map(fn($item) => "<td class=\"header-cell\" width=\"{$item['width']}%\">{$item['text']}</td>")
                        ->join(''),
                )
                ->append('</tr>')
                ->toString();

        if (!str_contains($headerHtml, '<td')) {
            $headerHtml = '';
        }

        $this->setRecord([]);

        $bodyHtml = $this
                        ->processItems($items ?? [])
                        ->map(
                            function ($item, $index) {
                                $this->setRecord($item);

                                return Str::of('<tr class="body-row">')
                                           ->append(
                                               collect($this->getColumnsData($index + 1))
                                                   ->map(fn($data) => "<td class=\"body-cell\" width=\"{$data['width']}%\">{$data['text']}</td>")
                                                   ->join(''),
                                           )
                                           ->append('</tr>');
                            }
                        )
                        ->join('');

        if (!str_contains($bodyHtml, '<td')) {
            $bodyHtml = '';
        }

        $this->html($this->getTableHtml(header: $headerHtml, body: $bodyHtml, beforeHtml: $beforeHtml, afterHtml: $afterHtml, classes: $classes));
    }

    public function getTableHtml(
        ?string $header                = null,
        ?string $body                  = null,
        ?string $headerFontType        = null,
        ?string $headerFontSize        = null,
        ?string $headerLineHeight      = null,
        ?string $bodyFontType          = null,
        ?string $bodyFontSize          = null,
        ?string $bodyLineHeight        = null,
        ?string $headerBackgroundColor = null,
        ?string $tableBorder           = null,
        ?string $headerAlignment       = null,
        ?string $bodyAlignment         = null,
        string|Closure $beforeHtml     = null,
        string|Closure $afterHtml      = null,
        ?string $classes               = '',
    ): string {
        $headerFontType ??= $this->getHeaderFontType();

        $headerFontSize ??= $this->getHeaderFontSize();

        $headerLineHeight ??= $this->getHeaderLineHeight();

        $bodyFontType ??= $this->getBodyFontType();

        $bodyFontSize ??= $this->getBodyFontSize();

        $bodyLineHeight ??= $this->getBodyLineHeight();

        $headerBackgroundColor ??= $this->getHeaderBackgroundColor();

        $tableBorder ??= $this->getTableBorder();

        $headerAlignment ??= $this->getHeaderAlignment();

        $bodyAlignment ??= $this->getBodyAlignment();

        $headerHtml = <<<HTML
                <thead>
                    $header
                </thead>
            HTML;

        $bodyHtml = <<<HTML
                <tbody>
                    $body
                </tbody>
            HTML;

        $tableHtml = '';

        if (!!$header) {
            $tableHtml .= $headerHtml;
        }

        if (!!$body) {
            $tableHtml .= $bodyHtml;
        }

        $beforeHtml = $this->evaluate($beforeHtml, [
            'record'   => $this->getRecord(),
            'reporter' => $this,
        ]);

        $afterHtml = $this->evaluate($afterHtml, [
            'record'   => $this->getRecord(),
            'reporter' => $this,
        ]);

        return <<<HTML
                <style>
                    .table-class {
                        border:$tableBorder;
                        width: 100%;
                        border-collapse: collapse1;
                        border-spacing: 0px;
                    }
                    .header-row {
                        line-height:$headerLineHeight;
                        font-family:$headerFontType;
                        font-size:$headerFontSize;
                        text-align:$headerAlignment;
                    }
                    .header-cell {
                        background-color:$headerBackgroundColor;
                        border:$tableBorder;
                    }
                    .body-row {
                        line-height:$bodyLineHeight;
                        font-family:$bodyFontType;
                        font-size:$bodyFontSize;
                        text-align:$bodyAlignment;
                    }
                    .body-cell {
                        border:$tableBorder;
                    }
                    {$classes}
                </style>
                $beforeHtml
                <table class="table-class">
                    $tableHtml
                </table>
                $afterHtml
            HTML;
    }

    public function processItems(Collection|array $items): Collection
    {
        if (is_array($items)) {
            return collect($items);
        }

        return $items;
    }

    public function getColumnsTitle(): array
    {
        $columns = $this->getCachedColumns();

        $data = [];

        foreach (array_keys($this->selectedColumns ?? []) as $column) {
            $data[] = [
                'width' => $columns[$column]->getSpanPercentage(),
                'text'  => $columns[$column]->getLabel(),
            ];
        }

        return $data;
    }

    public function getColumnsData(int|string $row = ''): array
    {
        $columns = $this->getCachedColumns();

        $data = [];

        foreach (array_keys($this->selectedColumns ?? []) as $column) {
            $data[] = [
                'width' => $columns[$column]->getSpanPercentage(),
                'text'  => $columns[$column] instanceof RowNumberColumn ? $this->getFormattedRowNumber($row) : $columns[$column]->getFormattedState()
            ];
        }

        return $data;
    }

    public function getCachedColumns(): array
    {
        return $this->cachedColumns ?? array_reduce(static::getColumns(), function (array $carry, ReportColumn $column): array {
            $carry[$column->getName()] = $column->reporter($this);

            return $carry;
        }, []);
    }

    public static function getOptionsFormComponents(): array
    {
        return [];
    }

    public static function getModel(): string
    {
        return static::$model ?? (string) str(class_basename(static::class))
                                              ->beforeLast('Reporter')
                                              ->prepend('App\\Models\\');
    }

    public function setRecord(mixed $record): void
    {
        $this->record = $record;
    }

    public function getRecord(): mixed
    {
        return $this->record;
    }

    public function getOptions(): array
    {
        return $this->options ?? [];
    }

    public static function modifyQuery(Builder $query): Builder
    {
        return $query;
    }

    public function getHeaderFontType(): string
    {
        return 'xyekan';
    }

    public function getHeaderFontSize(): string
    {
        return '9px';
    }

    public function getHeaderLineHeight(): string
    {
        return '250%';
    }

    public function getBodyFontType(): string
    {
        return 'HiwebMitra';
    }

    public function getBodyFontSize(): string
    {
        return '10px';
    }

    public function getBodyLineHeight(): string
    {
        return '150%';
    }

    public function getHeaderBackgroundColor(): string
    {
        return '#CCCCCC';
    }

    public function getTableBorder(): string
    {
        return '1px solid SlateGrey';
    }

    public function getHeaderAlignment(): string
    {
        return 'center';
    }

    public function getBodyAlignment(): string
    {
        return 'center';
    }

    public function getFormattedRowNumber(int|string $row): string
    {
        return $row;
    }

    public static function getColumns(): array
    {
        return [];
    }

    public function getSelectedColumns(): array
    {
        $columns = $this->getCachedColumns();

        $data = [];

        foreach (array_keys($this->selectedColumns ?? []) as $column) {
            $data[] = $columns[$column];
        }

        return $data;
    }

    public function getColumnsSpan(): int
    {
        $total = 0;

        foreach ($this->getSelectedColumns() as $column) {
            if ($column instanceof ReportColumn) {
                $total += $column->getSpan();
            }
        }

        return $total;
    }

    public function adjustPages(): void
    {
        if ($this->pdf->getNumPages() > 1) {
            $this->pdf->deletePage($this->pdf->getNumPages());
        }
    }

    public static function getReturnUrl(): ?string
    {
        return null;
    }

    public function beforePages(): void {}

    public function afterPages(): void {}
}
