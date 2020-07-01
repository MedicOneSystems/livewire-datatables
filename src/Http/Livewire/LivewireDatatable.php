<?php

namespace Mediconesystems\LivewireDatatables\Http\Livewire;

use Livewire\Component;
use Illuminate\Support\Arr;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use Mediconesystems\LivewireDatatables\Fieldset;
use Mediconesystems\LivewireDatatables\Traits\EditsFields;
use Mediconesystems\LivewireDatatables\Traits\WithCallbacks;
use Mediconesystems\LivewireDatatables\Traits\HandlesProperties;
use Mediconesystems\LivewireDatatables\Traits\WithPresetDateFilters;
use Mediconesystems\LivewireDatatables\Traits\WithPresetTimeFilters;

class LivewireDatatable extends Component
{
    use WithPagination, WithCallbacks, WithPresetDateFilters, WithPresetTimeFilters, HandlesProperties, EditsFields;

    public $model;
    public $fields;
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

        $this->fields = $this->fieldset()
            ->include($include)
            ->exclude($exclude)
            ->hidden($hidden)
            ->formatDates($dates)
            ->formatTimes($times)
            ->rename($renames)
            ->search($search)
            ->sort($sort)
            ->fieldsArray();

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

    public function fieldset()
    {
        return Fieldset::fromModel($this->model());
    }

    public function initialiseSort()
    {
        $this->sort = $this->defaultSort() ? $this->defaultSort()['key'] : $this->visibleFields->keys()->first();
        $this->direction = $this->defaultSort()['direction'] === 'asc';
    }

    public function defaultSort()
    {
        $fieldIndex = collect($this->fields)->search(function ($field) {
            return is_string($field['defaultSort']);
        });

        return $fieldIndex ? [
            'key' => $fieldIndex,
            'direction' => $this->fields[$fieldIndex]['defaultSort']
        ] : null;
    }

    public function getSortString()
    {
        return $this->fieldset()->fields()[$this->sort]->sort
            ?? $this->fieldset()->fields()[$this->sort]->column
            ?? $this->fieldset()->fields()[$this->sort]->raw;
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
        $this->fields[$index]['hidden'] = !$this->fields[$index]['hidden'];

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

    public function doNumberFilter($index, $low = 0, $high = 1000000)
    {
        $this->activeNumberFilters[$index] = [$low, $high];
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
        unset($this->activeTextFilters[$column]);
    }

    public function getVisibleFieldsProperty()
    {
        return collect($this->fields)->reject->hidden;
    }

    public function getSelectStatements()
    {
        return $this->visibleFields->map(function ($field) {
            return $field['column'] ? $field['column'] . ' AS ' . $field['name'] : null;
        })->filter()->merge($this->getAdditionalSelectStatements());
    }

    public function getAdditionalSelectStatements()
    {
        return collect($this->fields)->flatMap(function ($field) {
            return $field['additionalSelects'];
        })->filter();
    }

    public function getRawStatements()
    {
        return $this->visibleFields->map->raw->filter();
    }

    public function getFieldColumn($index)
    {
        return $this->fields[$index]['column'];
    }

    public function getFieldName($index)
    {
        return $this->fields[$index]['name'];
    }

    public function getDisplayValue($index, $value)
    {
        return is_array($this->selectFilters[$index]) && is_numeric($value)
            ? collect($this->selectFilters[$index]['selectFilter'])->firstWhere('id', '=', $value)['name'] ?? $value
            : $value;
    }

    public function addSelectFilters($builder)
    {
        return $builder->where(function ($query) {
            foreach ($this->activeSelectFilters as $index => $activeSelectFilter) {
                $query->where(function ($query) use ($index, $activeSelectFilter) {
                    foreach ($activeSelectFilter as $value) {
                        $this->addScopeSelectFilter($query, $index, $value)
                            ?? $query->orWhere($this->getFieldColumn($index), $value);
                    }
                });
            }
        });
    }

    public function addScopeSelectFilter($query, $index, $value)
    {
        if (!isset($this->fields[$index]['filterScope'])) {
            return;
        }

        return $query->{$this->fields[$index]['filterScope']}($value);
    }

    public function addBooleanFilters($builder)
    {
        return $builder->where(function ($query) use ($builder) {
            foreach ($this->activeBooleanFilters as $index => $value) {
                if ($this->addScopeBooleanFilter($query, $index, $value)) {
                    return;
                } else if ($value) {
                    $query->where(DB::raw($this->getFieldColumn($index)), '>', 0);
                } else {
                    $query->whereNull(DB::raw($this->getFieldColumn($index)))
                        ->orWhere(DB::raw($this->getFieldColumn($index)), 0);
                }
            }
        });
    }

    public function addScopeBooleanFilter($query, $index, $value)
    {
        if (!isset($this->fields[$index]['filterScope'])) {
            return;
        }

        return $query->{$this->fields[$index]['filterScope']}($value);
    }

    public function addTextFilters($builder)
    {
        return $builder->where(function ($query) {
            foreach ($this->activeTextFilters as $index => $value) {
                $query->orWhereRaw("LOWER(" . $this->getFieldColumn($index) . ") like ?", [strtolower("%$value%")]);
            }
        });
    }

    public function addNumberFilters($builder)
    {
        return $builder->whereBetween($this->getFieldColumn(key($this->activeNumberFilters)), reset($this->activeNumberFilters));
    }

    public function addDateRangeFilter($builder)
    {
        return $builder->when(isset($this->dates['start']), function ($query) {
            return $query->whereDate($this->getFieldColumn($this->dates['field']), '>', $this->dates['start']);
        })->when(isset($this->dates['end']), function ($query) {
            return $query->whereDate($this->getFieldColumn($this->dates['field']), '<', $this->dates['end']);
        });
    }

    public function addTimeRangeFilter($builder)
    {
        $times['start'] = $this->times['start'] ?? '00:00';
        $times['end'] = $this->times['end'] ?? '23:59';

        return $builder->where(function ($query) use ($times) {
            if ($times['start'] < $times['end']) {
                return $query->whereBetween($this->getFieldColumn($this->times['field']), [$times['start'], $times['end']]);
            }

            return $query->where(function ($subQuery) use ($times) {
                $subQuery->whereBetween($this->getFieldColumn($this->times['field']), [$times['start'], '23:59'])
                    ->orWhereBetween($this->getFieldColumn($this->times['field']), ['00:00', $times['end']]);
            });
        });
    }

    public function globallySearched()
    {
        return $this->visibleFields->filter(function ($field, $key) {
            return isset($field['globalSearch']);
        });
    }

    public function scopeFields()
    {
        return $this->visibleFields->filter(function ($field, $key) {
            return isset($field['scope']);
        });
    }

    public function getFieldCallback($fieldName)
    {
        return collect($this->fields)->firstWhere('name', $fieldName)
            ? Arr::only(collect($this->fields)->firstWhere('name', $fieldName), ['callback', 'params']) : null;
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
        return collect($this->fields)->filter->selectFilter;
    }

    public function getBooleanFiltersProperty()
    {
        return collect($this->fields)->filter->booleanFilter;
    }

    public function getTextFiltersProperty()
    {
        return collect($this->fields)->filter->textFilter;
    }

    public function getNumberFiltersProperty()
    {
        return collect($this->fields)->filter->numberFilter;
    }

    public function getDateFiltersProperty()
    {
        return tap(collect($this->fields)->filter->dateFilter, function ($fields) {
            $this->dates['field'] = $fields->keys()->first();
        });
    }

    public function getTimeFiltersProperty()
    {
        return tap(collect($this->fields)->filter->timeFilter, function ($fields) {
            $this->times['field'] = $fields->keys()->first();
        });
    }

    public function getActiveFiltersProperty()
    {
        return isset($this->dates['field'])
            || isset($this->times['field'])
            || count($this->activeSelectFilters)
            || count($this->activeBooleanFilters)
            || count($this->activeTextFilters);
    }

    public function buildDatabaseQuery()
    {
        // dd($this->additionalSelects);
        return $this->builder()
            ->addSelect($this->getSelectStatements()->toArray())
            ->when($this->search, function ($query) {
                $query->where(function ($query) {
                    $this->globallySearched()->each(function ($field, $i) use ($query) {
                        $this->fields[$i]['callback'] = 'highlight';
                        $this->fields[$i]['params'] = [$this->search];
                        $query->orWhere($field['column'], 'like', "%$this->search%");
                    });
                });
            })
            ->when(count($this->getRawStatements()), function ($query) {
                $this->getRawStatements()->each(function ($statement) use ($query) {
                    $query->selectRaw($statement);
                });
            })
            ->when(count($this->scopeFields()), function ($query) {
                $this->scopeFields()->each(function ($field) use ($query) {
                    $query->{$field['scope']}($field['name']);
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
                return $query->orderBy($this->getSortString(), $this->direction ? 'asc' : 'desc');
            });
    }

    public function mapCallbacks($paginatedCollection)
    {
        $paginatedCollection->getCollection()->map(function ($row, $i) {
            foreach ($row->getAttributes() as $name => $value) {
                $row->$name = $this->getFieldCallback($name)['callback']
                    ? $this->{$this->getFieldCallback($name)['callback']}($value, $row, ...$this->getFieldCallback($name)['params'] ?? null)
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
