<?php

namespace Mediconesystems\LivewireDatatables\Http\Livewire;

use Livewire\Component;
use Illuminate\Support\Arr;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use Mediconesystems\LivewireDatatables\ColumnSet;
use Mediconesystems\LivewireDatatables\Traits\WithCallbacks;
use Mediconesystems\LivewireDatatables\Traits\HandlesProperties;
use Mediconesystems\LivewireDatatables\Traits\WithPresetDateFilters;
use Mediconesystems\LivewireDatatables\Traits\WithPresetTimeFilters;

class LivewireDatatable extends Component
{
    use WithPagination, WithCallbacks, WithPresetDateFilters, WithPresetTimeFilters, HandlesProperties;

    public $model;
    public $columns;
    public $search;
    public $sort;
    public $direction;
    public $activeSelectFilters = [];
    public $activeBooleanFilters = [];
    public $activeTextFilters = [];
    public $activeNumberFilters = [];
    public $hideToggles;
    public $hideHeader;
    public $hidePagination;

    public $dates;
    public $times;
    public $perPage;

    public function mount(
        $model = null,
        $include = [],
        $exclude = [],
        $hidden = [],
        $dates = [],
        $times = [],
        $renames = [],
        $search = [],
        $sort = null,
        $hideToggles = null,
        $hideHeader = null,
        $hidePagination = null,
        $perPage = 10
    ) {
        $this->model = $this->model ?? $model;
        $this->hideToggles = $hideToggles;
        $this->hideHeader = $hideHeader;
        $this->hidePagination = $hidePagination;
        $this->perPage = $perPage;

        $this->columns = $this->columnset()
            ->include($include)
            ->exclude($exclude)
            ->hidden($hidden)
            ->formatDates($dates)
            ->formatTimes($times)
            ->rename($renames)
            ->search($search)
            ->sort($sort)
            ->columnsArray();

        $this->initialiseSort();
    }

    public function model()
    {
        return $this->model;
    }

    public function builder()
    {
        return $this->model()::query();
    }

    public function columnset()
    {
        return ColumnSet::fromModel($this->model());
    }

    public function initialiseSort()
    {
        $this->sort = $this->defaultSort() ? $this->defaultSort()['key'] : $this->visibleColumns->keys()->first();
        $this->direction = $this->defaultSort()['direction'] === 'asc';
    }

    public function defaultSort()
    {
        $columnIndex = collect($this->columns)->search(function ($column) {
            return is_string($column['defaultSort']);
        });

        return $columnIndex ? [
            'key' => $columnIndex,
            'direction' => $this->columns[$columnIndex]['defaultSort']
        ] : null;
    }

    public function getSortString()
    {
        return $this->columnset()->columns()[$this->sort]->sort
            ?? $this->columnset()->columns()[$this->sort]->field
            ?? $this->columnset()->columns()[$this->sort]->raw;
    }

    public function sort($index)
    {
        if ($this->sort === (int) $index) {
            $this->direction = !$this->direction;
        } else {
            $this->sort = (int) $index;
        }
        $this->page = 1;
    }

    public function toggle($index)
    {
        $this->columns[$index]['hidden'] = !$this->columns[$index]['hidden'];

        if ($this->sort === $index) {
            $this->initialiseSort();
        }
    }

    public function doBooleanFilter($index, $value)
    {
        $this->activeBooleanFilters[$index] = $value;
        $this->page = 1;
    }

    public function doSelectFilter($index, $value)
    {
        $this->activeSelectFilters[$index][] = $value;
        $this->page = 1;
    }

    public function doTextFilter($index, $value)
    {
        $this->activeTextFilters[$index] = $value;
        $this->page = 1;
    }

    public function doNumberFilterStart($index, $start)
    {
        $this->activeNumberFilters[$index]['start'] = (int) $start;
        $this->page = 1;
    }

    public function doNumberFilterEnd($index, $end)
    {
        $this->activeNumberFilters[$index]['end'] = (int) $end;
        $this->page = 1;
    }

    public function removeSelectFilter($column, $key = null)
    {
        unset($this->activeSelectFilters[$column][$key]);
        if (count($this->activeSelectFilters[$column]) < 1) {
            unset($this->activeSelectFilters[$column]);
        }
    }

    public function clearDateFilter()
    {
        $this->dates = null;
    }

    public function clearTimeFilter()
    {
        $this->times = null;
    }

    public function clearFilters()
    {
        $this->activeSelectFilters = [];
        $this->activeBooleanFilters = [];
        $this->activeTextFilters = [];
        $this->activeNumberFilters = [];
    }

    public function clearAllFilters()
    {
        $this->clearDateFilter();
        $this->clearTimeFilter();
        $this->activeSelectFilters = [];
        $this->activeBooleanFilters = [];
        $this->activeTextFilters = [];
        $this->activeNumberFilters = [];
    }

    public function removeBooleanFilter($column)
    {
        unset($this->activeBooleanFilters[$column]);
    }

    public function removeTextFilter($column)
    {
        unset($this->activeTextFilters[$column]);
    }

    public function removeNumberFilter($column)
    {
        unset($this->activeNumberFilters[$column]);
    }

    public function getVisibleColumnsProperty()
    {
        return collect($this->columns)->reject->hidden;
    }

    public function getSelectStatements()
    {
        return $this->visibleColumns->map(function ($column) {
            return $column['field'] ? $column['field'] . ' AS ' . $column['label'] : null;
        })->filter()->merge($this->getAdditionalSelectStatements());
    }

    public function getAdditionalSelectStatements()
    {
        return collect($this->columns)->flatMap(function ($column) {
            return $column['additionalSelects'];
        })->filter();
    }

    public function getRawStatements()
    {
        return $this->visibleColumns->map->raw->filter();
    }

    public function getColumnField($index)
    {
        return $this->columns[$index]['field'];
    }

    public function getColumnLabel($index)
    {
        return $this->columns[$index]['label'];
    }

    public function getDisplayValue($index, $value)
    {
        return is_array($this->selectFilters[$index]) && is_numeric($value)
            ? collect($this->selectFilters[$index]['selectFilter'])->firstWhere('id', '=', $value)['label'] ?? $value
            : $value;
    }

    public function addSelectFilters($builder)
    {
        return $builder->where(function ($query) {
            foreach ($this->activeSelectFilters as $index => $activeSelectFilter) {
                $query->where(function ($query) use ($index, $activeSelectFilter) {
                    foreach ($activeSelectFilter as $value) {
                        $this->addScopeSelectFilter($query, $index, $value)
                            ?? $query->orWhere($this->getColumnField($index), $value);
                    }
                });
            }
        });
    }

    public function addScopeSelectFilter($query, $index, $value)
    {
        if (!isset($this->columns[$index]['filterScope'])) {
            return;
        }

        return $query->{$this->columns[$index]['filterScope']}($value);
    }

    public function addBooleanFilters($builder)
    {
        return $builder->where(function ($query) use ($builder) {
            foreach ($this->activeBooleanFilters as $index => $value) {
                if ($this->addScopeBooleanFilter($query, $index, $value)) {
                    return;
                } else if ($value) {
                    $query->where(DB::raw($this->getColumnField($index)), '>', 0);
                } else {
                    $query->whereNull(DB::raw($this->getColumnField($index)))
                        ->orWhere(DB::raw($this->getColumnField($index)), 0);
                }
            }
        });
    }

    public function addScopeBooleanFilter($query, $index, $value)
    {
        if (!isset($this->columns[$index]['filterScope'])) {
            return;
        }

        return $query->{$this->columns[$index]['filterScope']}($value);
    }

    public function addTextFilters($builder)
    {
        return $builder->where(function ($query) {
            foreach ($this->activeTextFilters as $index => $value) {
                $query->orWhereRaw("LOWER(" . $this->getColumnField($index) . ") like ?", [strtolower("%$value%")]);
            }
        });
    }

    public function addNumberFilters($builder)
    {
        return $builder->where(function ($query) {
            foreach ($this->activeNumberFilters as $index => $filter) {
                return $query->whereBetween($this->getColumnField($index), [
                    isset($filter['start']) ? $filter['start'] : 0,
                    isset($filter['end']) ? $filter['end'] : 9999999
                ]);
            }
        });
    }

    public function addDateRangeFilter($builder)
    {
        return $builder->when(isset($this->dates['start']), function ($query) {
            return $query->whereDate($this->getColumnField($this->dates['field']), '>', $this->dates['start']);
        })->when(isset($this->dates['end']), function ($query) {
            return $query->whereDate($this->getColumnField($this->dates['field']), '<', $this->dates['end']);
        });
    }

    public function addTimeRangeFilter($builder)
    {
        $times['start'] = $this->times['start'] ?? '00:00';
        $times['end'] = $this->times['end'] ?? '23:59';

        return $builder->where(function ($query) use ($times) {
            if ($times['start'] < $times['end']) {
                return $query->whereBetween($this->getColumnField($this->times['field']), [$times['start'], $times['end']]);
            }

            return $query->where(function ($subQuery) use ($times) {
                $subQuery->whereBetween($this->getColumnField($this->times['field']), [$times['start'], '23:59'])
                    ->orWhereBetween($this->getColumnField($this->times['field']), ['00:00', $times['end']]);
            });
        });
    }

    public function globallySearched()
    {
        return $this->visibleColumns->filter(function ($column, $key) {
            return isset($column['globalSearch']);
        });
    }

    public function scopeColumns()
    {
        return $this->visibleColumns->filter(function ($column, $key) {
            return isset($column['scope']);
        });
    }

    public function getColumnCallback($columnName)
    {
        return collect($this->columns)->firstWhere('label', $columnName)
            ? Arr::only(collect($this->columns)->firstWhere('label', $columnName), ['callback', 'params']) : null;
    }

    public function getHeaderProperty()
    {
        return method_exists(static::class, 'header'); // ? $this->header() : $this->header;
    }

    public function getShowHideProperty()
    {
        return $this->showHide() ?? $this->showHide;
    }

    public function getPaginationControlsProperty()
    {
        return $this->paginationControls() ?? $this->paginationControls;
    }

    public function getResultsProperty()
    {
        return $this->mapCallbacks($this->buildDatabaseQuery()->paginate($this->perPage));
    }

    public function getSelectFiltersProperty()
    {
        return collect($this->columns)->filter->selectFilter;
    }

    public function getBooleanFiltersProperty()
    {
        return collect($this->columns)->filter->booleanFilter;
    }

    public function getTextFiltersProperty()
    {
        return collect($this->columns)->filter->textFilter;
    }

    public function getNumberFiltersProperty()
    {
        return collect($this->columns)->filter->numberFilter;
    }

    public function getDateFiltersProperty()
    {
        return tap(collect($this->columns)->filter->dateFilter, function ($columns) {
            $this->dates['field'] = $columns->keys()->first();
        });
    }

    public function getTimeFiltersProperty()
    {
        return tap(collect($this->columns)->filter->timeFilter, function ($columns) {
            $this->times['field'] = $columns->keys()->first();
        });
    }

    public function getActiveFiltersProperty()
    {
        return isset($this->dates['field'])
            || isset($this->times['field'])
            || count($this->activeSelectFilters)
            || count($this->activeBooleanFilters)
            || count($this->activeTextFilters)
            || count($this->activeNumberFilters);
    }

    public function buildDatabaseQuery()
    {
        return $this->builder()
            ->addSelect($this->getSelectStatements()->toArray())
            ->when($this->search, function ($query) {
                $query->where(function ($query) {
                    $this->globallySearched()->each(function ($column, $i) use ($query) {
                        $this->columns[$i]['callback'] = 'highlight';
                        $this->columns[$i]['params'] = [$this->search];
                        $query->orWhere($column['field'], 'like', "%$this->search%");
                    });
                });
            })
            ->when(count($this->getRawStatements()), function ($query) {
                $this->getRawStatements()->each(function ($statement) use ($query) {
                    $query->selectRaw($statement);
                });
            })
            ->when(count($this->scopeColumns()), function ($query) {
                $this->scopeColumns()->each(function ($column) use ($query) {
                    $query->{$column['scope']}($column['label']);
                });
            })
            ->when(count($this->activeSelectFilters) > 0, function ($query) {
                return $this->addSelectFilters($query);
            })
            ->when(count($this->activeBooleanFilters) > 0, function ($query) {
                return $this->addBooleanFilters($query);
            })
            ->when(count($this->activeTextFilters) > 0, function ($query) {
                return $this->addTextFilters($query);
            })
            ->when(count($this->activeNumberFilters) > 0, function ($query) {
                return $this->addNumberFilters($query);
            })
            ->when(isset($this->dates['field']) && (isset($this->dates['start']) || (isset($this->dates['end']))), function ($query) {
                return $this->addDateRangeFilter($query);
            })
            ->when(isset($this->times['field']) && (isset($this->times['start']) || (isset($this->times['end']))), function ($query) {
                return $this->addTimeRangeFilter($query);
            })
            ->when(isset($this->sort), function ($query) {
                // dd($this->getSortString());
                return $query->orderBy($this->getSortString(), $this->direction ? 'asc' : 'desc');
            });
    }

    public function mapCallbacks($paginatedCollection)
    {
        $paginatedCollection->getCollection()->map(function ($row, $i) {
            foreach ($row->getAttributes() as $label => $value) {
                $row->$label = $this->getColumnCallback($label)['callback']
                    ? $this->{$this->getColumnCallback($label)['callback']}($value, $row, ...$this->getColumnCallback($label)['params'] ?? null)
                    : $value;
            }
            return $row;
        });

        return $paginatedCollection;
    }

    public function render()
    {
        return view('livewire-datatables::livewire.datatable');
    }
}
