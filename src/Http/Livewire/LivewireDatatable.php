<?php

namespace Mediconesystems\LivewireDatatables\Http\Livewire;

use Livewire\Component;
use Illuminate\View\View;
use Illuminate\Support\Str;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Livewire\FileUploadConfiguration;
use Illuminate\Support\Facades\Storage;
use Livewire\Controllers\FileUploadHandler;
use Mediconesystems\LivewireDatatables\ColumnSet;
use Mediconesystems\LivewireDatatables\Traits\WithCallbacks;
use Mediconesystems\LivewireDatatables\Exports\DatatableExport;
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
    public $exportFile;

    public function mount(
        $model = null,
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
        $perPage = 10
    ) {
        $this->model = $this->model ?? $model;
        $this->include = $include;
        $this->exclude = $exclude;
        $this->hide = $hide;
        $this->dates = $dates;
        $this->times = $times;
        $this->renames = $renames;
        $this->searchable = $searchable;
        $this->sort = $sort;
        $this->hideHeader = $hideHeader;
        $this->hidePagination = $hidePagination;
        $this->perPage = $perPage;

        $this->columns = $this->freshColumns();

        $this->initialiseSort();
    }

    public function columns()
    {
        return ColumnSet::fromModelInstance($this->modelInstance);
    }

    public function getModelInstanceProperty()
    {
        return $this->model::firstOrFail();
    }

    public function freshColumns()
    {
        return $this->columns()
            ->include($this->include)
            ->exclude($this->exclude)
            ->hide($this->hide)
            ->formatDates($this->dates)
            ->formatTimes($this->times)
            ->rename($this->renames)
            ->search($this->searchable)
            ->sort($this->sort)
            ->columnsArray();
    }

    public function builder()
    {
        return $this->model::query();
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
        return $this->freshColumns()[$this->sort]['sort']
            ?? $this->freshColumns()[$this->sort]['field']
            ?? $this->freshColumns()[$this->sort]['raw'];
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
        $this->columns[$index]['hidden'] = ! $this->columns[$index]['hidden'];

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

    public function removeTextFilter($column, $key)
    {
        unset($this->activeTextFilters[$column][$key]);
        if (count($this->activeTextFilters[$column]) < 1) {
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
        return $this->columns[$index]['raw']
            ? Str::of($this->columns[$index]['raw'])->beforeLast(' AS ')
            : $this->columns[$index]['field'];
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
                if (($filter['start'] ?? 0) < ($filter['end'] ?? 0)) {
                    $query->whereBetween($this->getColumnField($index), [
                        isset($filter['start']) ? $filter['start'] : '00:00:00',
                        isset($filter['end']) ? $filter['end'] : '23:59:59'
                    ]);
                } else {
                    $query->where(function ($subQuery) use ($filter, $index) {
                        $subQuery->whereBetween($this->getColumnField($index), [
                            isset($filter['start']) ? $filter['start'] : '00:00:00',
                            '23:59'
                        ])->orWhereBetween($this->getColumnField($index), [
                            '00:00',
                            isset($filter['end']) ? $filter['end'] : '23:59:59'
                        ]);
                    });
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
                                $query->orWhereRaw("LOWER(" . $column['field'] . ") like ?", "%$search%");
                            });
                        });
                    }
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
            ->when(count($this->activeDateFilters) > 0, function ($query) {
                return $this->addDateRangeFilter($query);
            })
            ->when(count($this->activeTimeFilters) > 0, function ($query) {
                return $this->addTimeRangeFilter($query);
            })
            ->when(isset($this->sort), function ($query) {
                // dd($this->sort, $this->direction);
                $query->orderBy($this->getSortString(), $this->direction ? 'asc' : 'desc');
            });
    }

    public function mapCallbacks($paginatedCollection)
    {
        $callbacks = collect($this->freshColumns())->filter->callback->mapWithKeys(function ($column) {
            return [$column['label'] => $column['callback']];
        });

        $paginatedCollection->getCollection()->map(function ($row, $i) use ($callbacks) {
            foreach ($row as $label => $value) {
                if(isset($callbacks[$label]) && is_callable($callbacks[$label])) {
                    $row->$label = $callbacks[$label]($value, $row);
                } else if (isset($callbacks[$label]) && is_string($callbacks[$label])) {
                    $row->$label = $this->{$callbacks[$label]}($value, $row);
                }
                if($this->search && $this->searchableColumns()->firstWhere('label', $label)) {
                    $row->$label = $this->highlight($row->$label, $this->search);
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
        $this->exportFile = $path;
        $this->emit('startDownload', $path);
    }
}
