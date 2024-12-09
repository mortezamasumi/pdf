<?php

namespace Mortezamasumi\PdfReport\Reports;

use Filament\Support\Components\Component;
use Filament\Support\Concerns\CanAggregateRelatedModels;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Number;
use Mortezamasumi\PdfReport\Concerns\CanFormatState;
use Mortezamasumi\PdfReport\Concerns\HasCellState;
use Closure;

class ReportColumn extends Component
{
    use CanAggregateRelatedModels;
    use CanFormatState;
    use HasCellState;

    protected string $name;

    protected string|Closure|null $label = null;

    protected ?Reporter $reporter = null;

    protected bool|Closure $isEnabledByDefault = true;

    protected string $evaluationIdentifier = 'column';

    protected int $span = 1;

    final public function __construct(string $name)
    {
        $this->name($name);
    }

    public static function make(string $name): static
    {
        $static = app(static::class, ['name' => $name]);
        $static->configure();

        return $static;
    }

    public function name(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function label(string|Closure|null $label): static
    {
        $this->label = $label;

        return $this;
    }

    public function span(int|Closure|null $span): static
    {
        $this->span = $span;

        return $this;
    }

    public function getSpan(): int
    {
        return $this->evaluate($this->span) ?? 1;
    }

    public function getSpanPercentage(): string
    {
        if ($this->getReporter()->getColumnsSpan() <= 0) {
            return '';
        }

        return Number::format(number: 100 * ($this->getSpan() / $this->getReporter()->getColumnsSpan()), precision: 2);
    }

    public function reporter(?Reporter $reporter): static
    {
        $this->reporter = $reporter;

        return $this;
    }

    public function enabledByDefault(bool|Closure $condition = true): static
    {
        $this->isEnabledByDefault = $condition;

        return $this;
    }

    public function isEnabledByDefault(): bool
    {
        return (bool) $this->evaluate($this->isEnabledByDefault);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getReporter(): ?Reporter
    {
        return $this->reporter;
    }

    public function getRecord(): mixed
    {
        return $this->getReporter()?->getRecord();
    }

    public function getLabel(): ?string
    {
        return $this->evaluate($this->label) ?? (string) str($this->getName())
                                                             ->beforeLast('.')
                                                             ->afterLast('.')
                                                             ->kebab()
                                                             ->replace(['-', '_'], ' ')
                                                             ->ucfirst();
    }

    public function applyRelationshipAggregates(EloquentBuilder $query): EloquentBuilder
    {
        return $query->when(
            filled([$this->getRelationshipToAvg(), $this->getColumnToAvg()]),
            fn($query) => $query->withAvg($this->getRelationshipToAvg(), $this->getColumnToAvg())
        )->when(
            filled($this->getRelationshipsToCount()),
            fn($query) => $query->withCount(Arr::wrap($this->getRelationshipsToCount()))
        )->when(
            filled($this->getRelationshipsToExistenceCheck()),
            fn($query) => $query->withExists(Arr::wrap($this->getRelationshipsToExistenceCheck()))
        )->when(
            filled([$this->getRelationshipToMax(), $this->getColumnToMax()]),
            fn($query) => $query->withMax($this->getRelationshipToMax(), $this->getColumnToMax())
        )->when(
            filled([$this->getRelationshipToMin(), $this->getColumnToMin()]),
            fn($query) => $query->withMin($this->getRelationshipToMin(), $this->getColumnToMin())
        )->when(
            filled([$this->getRelationshipToSum(), $this->getColumnToSum()]),
            fn($query) => $query->withSum($this->getRelationshipToSum(), $this->getColumnToSum())
        );
    }

    public function applyEagerLoading(EloquentBuilder $query): EloquentBuilder
    {
        if (!$this->hasRelationship($query->getModel())) {
            return $query;
        }

        $relationshipName = $this->getRelationshipName();

        if (array_key_exists($relationshipName, $query->getEagerLoads())) {
            return $query;
        }

        return $query->with([$relationshipName]);
    }

    protected function resolveDefaultClosureDependencyForEvaluationByName(string $parameterName): array
    {
        return match ($parameterName) {
            'reporter' => [$this->getReporter()],
            'options'  => [$this->getReporter()->getOptions()],
            'record'   => [$this->getRecord()],
            default    => parent::resolveDefaultClosureDependencyForEvaluationByName($parameterName),
        };
    }

    protected function resolveDefaultClosureDependencyForEvaluationByType(string $parameterType): array
    {
        $record = $this->getRecord();

        return match ($parameterType) {
            Reporter::class                               => [$this->getReporter()],
            Model::class, $record ? $record::class : null => [$record],
            default                                       => parent::resolveDefaultClosureDependencyForEvaluationByType($parameterType),
        };
    }
}
