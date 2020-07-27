<?php

namespace Mediconesystems\LivewireDatatables\Http\Livewire;

use Exception;
use Livewire\Component;
use Illuminate\View\View;
use Illuminate\Support\Str;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Mediconesystems\LivewireDatatables\Column;
use Mediconesystems\LivewireDatatables\ColumnSet;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Mediconesystems\LivewireDatatables\Traits\WithCallbacks;
use Mediconesystems\LivewireDatatables\Exports\DatatableExport;
use Mediconesystems\LivewireDatatables\Traits\WithPresetDateFilters;
use Mediconesystems\LivewireDatatables\Traits\WithPresetTimeFilters;

class LivewireDatatable extends Component
{
    use WithPagination, WithCallbacks, WithPresetDateFilters, WithPresetTimeFilters;

    const SEPARATOR = '|**lwdt**|';

    public $model;
    public $with;
    public $columns;
    public $search;
    public $sort;
    public $direction;
    public $activeDateFilters = [];
    public $activeTimeFilters = [];
    public $activeSelectFilters = [];
    public $activeBooleanFilters = [];
    public $activeTextFilters = [];
    public $activeNumberFilters = [];
    public $hideHeader;
    public $hidePagination;
    public $perPage;
    public $include;
    public $exclude;
    public $hide;
    public $dates;
    public $times;
    public $renames;
    public $searchable;
    public $exportable;
    public $hideable;
    public $params;

    public function mount(
        $model = null,
        $with = null,
        $include = [],
        $exclude = [],
        $hide = [],
        $dates = [],
        $times = [],
        $renames = [],
        $searchable = [],
        $sort = null,
        $hideHeader = null,
        $hidePagination = null,
        $perPage = 10,
        $exportable = false,
        $hideable = false,
        $params = []
    ) {
        foreach(['model','with','include','exclude','hide','dates','times','renames','searchable','sort','hideHeader','hidePagination','perPage','exportable','hideable'] as $property) {
            $this->$property = $this->$property ?? $$property;
        }

        $this->params = $params;

        $this->columns = $this->freshColumns();

        $this->initialiseSort();
    }

    public function columns()
    {
        return $this->modelInstance;
    }

    public function getModelInstanceProperty()
    {
        return $this->model::firstOrFail();
    }

    public function getWithRelationModelInstancesProperty()
    {
        if (! $this->with) {
            return;
        }

        return collect(is_array($this->with) ? $this->with : array_map('trim', explode(',', $this->with)))->map(function ($with) {
            $model = $this->model::query()->getRelation($with)->getQuery()->first();

            return collect($model->getAttributes())->keys()->reject(function ($name) use ($model) {
                return in_array($name, $model->getHidden());
            })
            ->map(function ($attribute) use ($with) {
                return Column::name($with.'.'.$attribute);
            });
        })->flatten()->toArray();
    }

    public function freshColumns()
    {
        $columns = ColumnSet::build($this->columns(), $this->withRelationModelInstances)
            ->include($this->include)
            ->exclude($this->exclude)
            ->hide($this->hide)
            ->formatDates($this->dates)
            ->formatTimes($this->times)
            ->rename($this->renames)
            ->search($this->searchable)
            ->sort($this->sort)
            ->columnsArray();

        if(($name = collect($columns)->pluck('name')->duplicates() )&& collect($columns)->pluck('name')->duplicates()->count()) {
            throw new Exception('Duplicate Column Name: ' . $name->first());
        }

        return $columns;
    }

    public function builder()
    {
        return $this->model::query()
            ->when($this->with, function ($query) {
                foreach(is_array($this->with) ? $this->with : array_map('trim', explode(',', $this->with)) as $with) {
                    $with = app($this->model)->query()->getRelation($with);

                    switch (true) {
                        case $with instanceof BelongsTo:
                            $query->leftJoin(
                                $with->getRelated()->getTable(),
                                $with->getQualifiedOwnerKeyName(),
                                $with->getQualifiedForeignKeyName()
                            );
                        break;

                        case $with instanceof BelongsToMany:
                            throw new Exception('If you join a BelongsToMany you will get more records than you expect. Try a scope instead');
                        break;
                    }
                }
            })
            ;
    }

    public function initialiseSort()
    {
        $this->sort = $this->defaultSort() ? $this->defaultSort()['key'] : $this->visibleColumns->keys()->first();
        $this->direction = $this->defaultSort() && $this->defaultSort()['direction'] === 'asc';
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
        $column = $this->freshColumns()[$this->sort];

        switch (true) {
            case $column['sort']:
                return $column['sort'];
                break;

            case $column['base']:
                return $column['base'];
                break;

            case isset($column['callback']) && count($column['additionalSelects']):
                return $this->getSelectStatementFromCallback($column);

            default:
                return $this->getSelectStatementFromName($column['name']);
                break;
        }
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
        if ($this->sort == $index) {
            $this->initialiseSort();
        }

        $this->columns[$index]['hidden'] = ! $this->columns[$index]['hidden'];
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
        foreach (explode(' ', $value) as $val) {
            $this->activeTextFilters[$index][] = $val;
        }
        $this->page = 1;
    }

    public function doDateFilterStart($index, $start)
    {
        $this->activeDateFilters[$index]['start'] = $start;
        $this->page = 1;
    }

    public function doDateFilterEnd($index, $end)
    {
        $this->activeDateFilters[$index]['end'] = $end;
        $this->page = 1;
    }

    public function doTimeFilterStart($index, $start)
    {
        $this->activeTimeFilters[$index]['start'] = $start;
        $this->page = 1;
    }

    public function doTimeFilterEnd($index, $end)
    {
        $this->activeTimeFilters[$index]['end'] = $end;
        $this->page = 1;
    }

    public function doNumberFilterStart($index, $start)
    {
        $this->activeNumberFilters[$index]['start'] = (int) $start;
        $this->page = 1;
    }

    public function doNumberFilterEnd($index, $end)
    {
        $this->activeNumberFilters[$index]['end'] = $end ? (int) $end : null;
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

    public function removeTextFilter($column, $key = null)
    {
        if ($key) {
            unset($this->activeTextFilters[$column][$key]);
            if (count($this->activeTextFilters[$column]) < 1) {
                unset($this->activeTextFilters[$column]);
            }
        } else {
            unset($this->activeTextFilters[$column]);
        }
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
            if (isset($column['scope'])) {
                return;
            }
            if (isset($column['raw'])) {
                return DB::raw($column['raw']);
            }
            if (isset($column['base'])) {
                return $column['base'] . ' AS ' . $column['name'];
            }
            if (isset($column['callback']) && count($column['additionalSelects'])) {
                return $this->getSelectStatementFromCallback($column, true);
            }
            if ($column['type'] === 'editable') {
                return $this->getSelectStatementForEditable($column);
            }
            return $this->getSelectStatementFromName($column['name'], true);
        })->filter()->flatten();
    }

    public function getSelectStatementFromName($name, $alias = false) {
        if (Str::contains($name, '.')) {
            $nameParts = explode('.', $name);
            return $this->builder()->getModel()->{$nameParts[0]}()->getRelated()->getTable().'.'.$nameParts[1] . ($alias ? ' AS ' . $name : '');
        }

        return $this->builder()->getModel()->getTable().'.' . $name;
    }

    public function getSelectStatementFromCallback($column, $alias = false)
    {
        $columns = array_map([$this, 'getSelectStatementFromName'], $column['additionalSelects']);
        return count($columns) > 1
            ? DB::raw('CONCAT_WS("' . static::SEPARATOR . '" ,' . implode(', ', $columns) . ')' . ($alias ? ' AS  `' . $column['name'] . '`' : ''))
            : $columns[0] . ($alias ? ' AS ' . $column['name'] : '');
    }

    public function getSelectStatementForEditable($column)
    {
        return [
            $this->getSelectStatementFromName($column['name']),
            $this->builder()->getModel()->getTable().'.id AS ' . $this->builder()->getModel()->getTable().'.id'
        ];
    }

    public function getColumnField($index)
    {
        if ($this->columns[$index]['raw']) {
            return $this->columns[$index]['sort'];
        }

        if (isset($this->columns[$index]['callback']) && count($this->columns[$index]['additionalSelects'])) {
            return $this->getSelectStatementFromCallback($this->columns[$index]);
        }

        return $this->getSelectStatementFromName($this->columns[$index]['name']);
    }

    public function getColumnLabel($index)
    {
        return $this->columns[$index]['label'];
    }

    public function getDisplayValue($index, $value)
    {
        return is_array($this->columns[$index]['filterable']) && is_numeric($value)
            ? collect($this->columns[$index]['filterable'])->firstWhere('id', '=', $value)['name'] ?? $value
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
        if (!isset($this->columns[$index]['scopeFilter'])) {
            return;
        }

        return $query->{$this->columns[$index]['scopeFilter']}($value);
    }

    public function addBooleanFilters($builder)
    {
        return $builder->where(function ($query) use ($builder) {
            foreach ($this->activeBooleanFilters as $index => $value) {
                if ($this->addScopeSelectFilter($query, $index, $value)) {
                    return;
                } else if ($value == 1) {

                    $query->where(DB::raw($this->getColumnField($index)), '>', 0);
                } else if (strlen($value)) {
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
            foreach ($this->activeTextFilters as $index => $activeTextFilter) {
                $query->where(function ($query) use ($index, $activeTextFilter) {
                    foreach ($activeTextFilter as $value) {
                        $query->orWhereRaw("LOWER(" . $this->getColumnField($index) . ") like ?", [strtolower("%$value%")]);
                    }
                });
            }
        });
    }

    public function addNumberFilters($builder)
    {
        return $builder->where(function ($query) {
            foreach ($this->activeNumberFilters as $index => $filter) {
                $query->whereRaw($this->getColumnField($index) . " BETWEEN ? AND ?", [
                    isset($filter['start']) ? $filter['start'] : 0,
                    isset($filter['end']) ? $filter['end'] : 9999999
                ]);
            }
        });
    }

    public function addDateRangeFilter($builder)
    {
        return $builder->where(function ($query) {
            foreach ($this->activeDateFilters as $index => $filter) {
                $query->whereBetween($this->getColumnField($index), [
                    isset($filter['start']) ? $filter['start'] : '0000-00-00',
                    isset($filter['end']) ? $filter['end'] : now()->format('Y-m-d')
                ]);
            }
        });
    }

    public function addTimeRangeFilter($builder)
    {
        return $builder->where(function ($query) {
            foreach ($this->activeTimeFilters as $index => $filter) {
                $start = isset($filter['start']) && $filter['start'] != '' ? $filter['start'] : '00:00:00';
                $end = isset($filter['end']) && $filter['end'] != '' ? $filter['end'] : '23:59:59';

                if ($end < $start) {
                    $query->where(function ($subQuery) use ($index, $start, $end) {
                        $subQuery->whereBetween($this->getColumnField($index), [$start, '23:59'])
                            ->orWhereBetween($this->getColumnField($index), ['00:00', $end]);
                    });
                } else {
                    $query->whereBetween($this->getColumnField($index), [$start, $end]);
                }
            }
        });
    }

    public function searchableColumns()
    {
        return collect($this->columns)->filter(function ($column, $key) {
            return $column['searchable'];
        });
    }

    public function scopeColumns()
    {
        return $this->visibleColumns->filter(function ($column, $key) {
            return isset($column['scope']);
        });
    }

    public function getColumnFromLabel($label)
    {
        return collect($this->columns)->firstWhere('label', $label);
    }

    public function getHeaderProperty()
    {
        return method_exists(static::class, 'header');
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
        return $this->mapCallbacks($this->buildDatabaseQuery()->toBase()->paginate($this->perPage));
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

    public function getActiveFiltersProperty()
    {
        return count($this->activeDateFilters)
            || count($this->activeTimeFilters)
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
                    foreach (explode(' ', $this->search) as $search) {
                        $query->where(function ($query) use ($search) {
                            $this->searchableColumns()->each(function ($column, $i) use ($query, $search) {
                                $query->orWhereRaw("LOWER(" . $this->getSelectStatementFromName($column['name']) . ") like ?", "%$search%");
                            });
                        });
                    }
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
            ->when(count($this->activeDateFilters) > 0, function ($query) {
                return $this->addDateRangeFilter($query);
            })
            ->when(count($this->activeTimeFilters) > 0, function ($query) {
                return $this->addTimeRangeFilter($query);
            })
            ->when(isset($this->sort), function ($query) {
                $query->orderBy(DB::raw($this->getSortString()), $this->direction ? 'asc' : 'desc');
            });
    }

    public function getCallbacks()
    {
        return collect($this->freshColumns())->filter->callback->mapWithKeys(function ($column) {
            return [$column['name'] => $column['callback']];
        });
    }

    public function getEditables()
    {
        return collect($this->freshColumns())->filter(function ($column) {
            return $column['type'] === 'editable';
        })->mapWithKeys(function ($column) {
            return [$column['name'] => true];
        });
    }

    public function mapCallbacks($paginatedCollection)
    {
        $paginatedCollection->getCollection()->map(function ($row, $i) {
            foreach ($row as $name => $value) {
                if (isset($this->getEditables()[$name])) {
                    $row->$name = view('datatables::editable', [
                        'value' => $value,
                        'table' => $this->builder()->getModel()->getTable(),
                        'column' => Str::after($name, '.'),
                        'rowId' => $row->{$this->builder()->getModel()->getTable() . '.id'},
                    ]);
                } else if (isset($this->getCallbacks()[$name]) && is_string($this->getCallbacks()[$name])) {
                    $row->$name = $this->{$this->getCallbacks()[$name]}($value, $row);
                } else if(Str::startsWith($name, 'callback_')) {
                    $row->$name = $this->getCallbacks()[$name](...explode(static::SEPARATOR, $value));
                } else if(isset($this->getCallbacks()[$name]) && is_callable($this->getCallbacks()[$name])) {
                    $row->$name = $this->getCallbacks()[$name]($value, $row);
                }

                if($this->search && $this->searchableColumns()->firstWhere('name', $name)) {
                    $row->$name = $this->highlight($row->$name, $this->search);
                }
            }

            return $row;
        });

        return $paginatedCollection;
    }

    public function highlight($value, $string)
    {
        $output = substr($value, stripos($value, $string), strlen($string));

        if ($value instanceof View) {
            return $value
                ->with(['value' => str_ireplace($string, view('datatables::highlight', ['slot' => $output]), $value->gatherData()['value'])]);
        }

        return str_ireplace($string, view('datatables::highlight', ['slot' => $output]), $value);
    }

    public function render()
    {
        return view('datatables::datatable');
    }

    public function export()
    {
        $path = 'datatables/export-' . now()->timestamp . '.xlsx';
        (new DatatableExport($this->buildDatabaseQuery()->get()))->store($path, config('livewire-datatables.file_export.disk') ?: config('filesystems.default'));
        Storage::setVisibility($path, 'public');
        $this->exportFile = $path;
        $this->emit('startDownload', $path);
    }
}
