<?php

namespace Mediconesystems\LivewireDatatables\Traits;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Livewire\WithPagination;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Mediconesystems\LivewireDatatables\Field;
use Mediconesystems\LivewireDatatables\Fieldset;

trait LivewireDatatable
{
    use WithPagination;

    public $fields;
    public $sort;
    public $direction;
    public $activeSelectFilters = [];
    public $activeBooleanFilters = [];
    public $activeTextFilters = [];

    public $dates;
    public $times;
    public $perPage = 10;

    public function mount()
    {
        $this->fields = $this->fields()->map->toArray()->toArray();
        $this->initialiseSort();
    }

    public function builder()
    {
        return $this->model()::query();
    }

    public function fields()
    {
        return Fieldset::fromModel($this->model())->fields();
    }

    private function initialiseSort()
    {
        $this->sort = $this->getColumns()->first();
    }

    public function sort($field)
    {
        if ($this->sort === $field) {
            $this->direction = !$this->direction;
        } else {
            $this->sort = $field;
        }
    }

    public function toggle($index)
    {
        $this->fields[$index]['hidden'] = !$this->fields[$index]['hidden'];

        if ($this->sort === $this->fields[$index]['name']) {
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

    public function doTextFilter($field, $value)
    {
        $this->activeTextFilters[$field] = $value;
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
        $this->dates = [
            'start' => null,
            'end' => null,
        ];
    }

    public function lastMonth()
    {
        $this->dates['start'] = now()->subMonth()->startOfMonth()->format('Y-m-d');
        $this->dates['end'] = now()->subMonth()->endOfMonth()->format('Y-m-d');
    }

    public function lastQuarter()
    {
        $this->dates['start'] = now()->subQuarter()->startOfQuarter()->format('Y-m-d');
        $this->dates['end'] = now()->subQuarter()->endOfQuarter()->format('Y-m-d');
    }

    public function lastYear()
    {
        $this->dates['start'] = now()->subYear()->startOfYear()->format('Y-m-d');
        $this->dates['end'] = now()->subYear()->endOfYear()->format('Y-m-d');
    }

    public function monthToToday()
    {
        $this->dates['start'] = now()->subMonth()->addDay()->format('Y-m-d');
        $this->dates['end'] = now()->format('Y-m-d');
    }

    public function quarterToToday()
    {
        $this->dates['start'] = now()->subQuarter()->addDay()->format('Y-m-d');
        $this->dates['end'] = now()->format('Y-m-d');
    }

    public function yearToToday()
    {
        $this->dates['start'] = now()->subYear()->addDay()->format('Y-m-d');
        $this->dates['end'] = now()->format('Y-m-d');
    }

    public function clearTimeFilter()
    {
        $this->times = [
            'field' => '',
            'start' => '',
            'end' => '',
        ];
    }

    public function clearAllFilters()
    {
        $this->clearDateFilter();
        $this->clearTimeFilter();
        $this->activeSelectFilters = [];
        $this->activeBooleanFilters = [];
        $this->activeTextFilters = [];
    }

    public function removeBooleanFilter($column)
    {
        unset($this->activeBooleanFilters[$column]);
    }

    public function removeTextFilter($column)
    {
        unset($this->activeTextFilters[$column]);
    }

    public function visibleFields()
    {
        return collect($this->fields)->reject->hidden;
    }

    public function getColumns()
    {
        return $this->visibleFields()->map->name;
    }

    public function getSelectStatements()
    {
        return $this->visibleFields()->map(function ($field) {
            return $field['column'] ? $field['column'] . ' AS ' . $field['name'] : null;
        })->filter();
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
                $query->orWhere($this->getFieldColumn($index), 'like', "%$value%");
            }
        });
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

    public function getResults()
    {
        return $this->builder()
            ->when(true, function ($query) {
                $this->visibleFields()->filter(function ($field, $key) {
                    return isset($field['scope']);
                })->each(function ($field) use ($query) {
                    $query->{$field['scope']}($field['name']);
                });
            })
            ->addSelect($this->getSelectStatements()->filter()->toArray())
            ->when(count($this->activeSelectFilters) > 0, function ($query) {
                return $this->addSelectFilters($query);
            })
            ->when(count($this->activeBooleanFilters) > 0, function ($query) {
                return $this->addBooleanFilters($query);
            })
            ->when(count($this->activeTextFilters) > 0, function ($query) {
                return $this->addTextFilters($query);
            })
            ->when(isset($this->dates['field']) && ((isset($this->dates['start']) && $this->dates['start'] !== '') || (isset($this->dates['end']) && $this->dates['end'] !== '')), function ($query) {
                return $this->addDateRangeFilter($query);
            })
            ->when(isset($this->times['field']) && $this->times['field'] !== '', function ($query) {
                return $this->addTimeRangeFilter($query);
            })
            // ->when(isset($this->queryString), function ($query) {
            //     return $this->parseQueryIntoBuilder($query, $this->queryString, 'and');
            // })
            ->when(isset($this->sort), function ($query) {
                return $query->orderBy($this->sort, $this->direction ? 'asc' : 'desc');
            });
    }

    public function mapCallbacks()
    {
        $results = $this->getResults()->paginate($this->perPage);
        // dd($results->getCollection());
        $results->getCollection()->map(function ($row, $i) {

            foreach ($row->getAttributes() as $name => $value) {
                $row->$name = $this->getFieldCallback($name)['callback'] ? $this->{$this->getFieldCallback($name)['callback']}($value, $this->getFieldCallback($name)['params'] ?? null) : $value;
            }
            return $row;
        });

        return $results;
    }

    public function getFieldCallback($fieldName)
    {
        return collect($this->fields)->firstWhere('name', $fieldName)
            ? Arr::only(collect($this->fields)->firstWhere('name', $fieldName), ['callback', 'params']) : null;
    }

    public function formatTime($time)
    {
        return $time ? Carbon::parse($time)->format('H:i') : null;
    }

    public function formatDate($date, $format)
    {
        return $date ? Carbon::parse($date)->format($format) : null;
    }

    public function round($value, $precision = 0)
    {
        return $value ? round($value, $precision) : null;
    }

    public function boolean($value)
    {
        return $value
            ? 'check-circle'
            : 'x-circle';
    }

    public function makeLink($value, $model)
    {
        return '<a href="/$model/' . $value . '" class="border-2 border-transparent hover:border-blue-500 hover:bg-blue-100 hover:shadow-lg text-blue-600 rounded-lg px-3 py-1">' . str_pad($value, 6, '0', STR_PAD_LEFT) . '</a>';
    }

    public function truncate($value)
    {
        return '<span class="group cursor-pointer">
            <span class="inline-block flex items-center">' . Str::limit($value, 16) . '</span><span class="z-10 w-full -ml-1/2 sm:w-4/5 sm:max-w-6xl sm:-ml-2/5 mt-2 px-1 text-xs whitespace-pre-wrap rounded-lg bg-gray-100 border border-gray-300 shadow-xl text-gray-700 text-left whitespace-normal absolute hidden group-hover:block">' . $value . '</span></span>';
    }

    public function getResultsProperty()
    {
        return $this->mapCallbacks();
    }

    public function getColumnsProperty()
    {
        return $this->getColumns();
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

    public function getDateFiltersProperty()
    {
        return collect($this->fields)->filter->dateFilter;
    }

    public function getTimeFiltersProperty()
    {
        return collect($this->fields)->filter->timeFilter;
    }

    public function render()
    {
        return view('datatables::livewire.datatable');
    }
}
