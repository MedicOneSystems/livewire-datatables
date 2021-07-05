<?php

namespace Mediconesystems\LivewireDatatables\Http\Livewire;

use Exception;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Database\Query\Expression;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;
use Mediconesystems\LivewireDatatables\ColumnSet;
use Mediconesystems\LivewireDatatables\Exports\DatatableExport;
use Mediconesystems\LivewireDatatables\Traits\WithCallbacks;
use Mediconesystems\LivewireDatatables\Traits\WithPresetDateFilters;
use Mediconesystems\LivewireDatatables\Traits\WithPresetTimeFilters;

class LivewireDatatable extends Component
{
    use WithPagination, WithCallbacks, WithPresetDateFilters, WithPresetTimeFilters;

    const SEPARATOR = '|**lwdt**|';
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
    public $searchable;
    public $exportable;
    public $hideable;
    public $params;
    public $selected = [];
    public $beforeTableSlot;
    public $afterTableSlot;
    public $name;

    protected $query;
    protected $listeners = ['refreshLivewireDatatable'];

    public function updatedSearch()
    {
        $this->page = 1;
    }

    public function mount(
        $model = null,
        $include = [],
        $exclude = [],
        $hide = [],
        $dates = [],
        $times = [],
        $searchable = [],
        $sort = null,
        $hideHeader = null,
        $hidePagination = null,
        $perPage = null,
        $exportable = false,
        $hideable = false,
        $beforeTableSlot = false,
        $afterTableSlot = false,
        $params = []
    ) {
        foreach (['model', 'include', 'exclude', 'hide', 'dates', 'times', 'searchable', 'sort', 'hideHeader', 'hidePagination', 'exportable', 'hideable', 'beforeTableSlot', 'afterTableSlot'] as $property) {
            $this->$property = $this->$property ?? $$property;
        }

        $this->params = $params;

        $this->columns = $this->getViewColumns();

        $this->initialiseSort();

        // check if there are sorting vars in the session
        $key = Str::snake(Str::afterLast(get_called_class(), '\\'));
        $this->sort = session()->get($key.$this->name.'_sort', $this->sort);
        $this->direction = session()->get($key.$this->name.'_direction', $this->direction);
        $this->perPage = $perPage ?? $this->perPage ?? config('livewire-datatables.default_per_page', 10);
    }

    public function columns()
    {
        return $this->modelInstance;
    }

    public function getViewColumns()
    {
        return collect($this->freshColumns)->map(function ($column) {
            return collect($column)->only([
                'hidden',
                'label',
                'align',
                'type',
                'filterable',
                'filterView',
                'name',
                'params',
                'width',
            ])->toArray();
        })->toArray();
    }

    public function getModelInstanceProperty()
    {
        return $this->model::firstOrFail();
    }

    public function builder()
    {
        return $this->model::query();
    }

    public function delete($id)
    {
        $this->model::destroy($id);
    }

    public function getProcessedColumnsProperty()
    {
        // dd($this->columns());
        return ColumnSet::build($this->columns())
            ->include($this->include)
            ->exclude($this->exclude)
            ->hide($this->hide)
            ->formatDates($this->dates)
            ->formatTimes($this->times)
            ->search($this->searchable)
            ->sort($this->sort);
    }

    public function resolveColumnName($column)
    {
        return $column->isBaseColumn()
            ? $this->query->getModel()->getTable().'.'.($column->base ?? Str::before($column->name, ':'))
            : $column->select ?? $this->resolveRelationColumn($column->base ?? $column->name, $column->aggregate);
    }

    public function resolveCheckboxColumnName($column)
    {
        $column = is_object($column)
            ? $column->toArray()
            : $column;

        return Str::contains($column['base'], '.')
            ? $this->resolveRelationColumn($column['base'], $column['aggregate'])
            : $this->query->getModel()->getTable().'.'.$column['base'];
    }

    public function resolveAdditionalSelects($column)
    {
        $selects = collect($column->additionalSelects)->map(function ($select) use ($column) {
            return Str::contains($select, '.')
                ? $this->resolveRelationColumn($select, Str::contains($select, ':') ? Str::after($select, ':') : null, $column->name)
                : $this->query->getModel()->getTable().'.'.$select;
        });

        return $selects->count() > 1
            ? new Expression("CONCAT_WS('".static::SEPARATOR."' ,".
                collect($selects)->map(function ($select) {
                    return "COALESCE($select, '')";
                })->join(', ').')')
            : $selects->first();
    }

    public function resolveEditableColumnName($column)
    {
        return [
            $column->select,
            $this->query->getModel()->getTable().'.'.$this->query->getModel()->getKeyName(),
        ];
    }

    public function getSelectStatements($withAlias = false, $export = false)
    {
        return $this->processedColumns->columns
            ->reject(function ($column) use ($export) {
                return $column->scope || ($export && $column->preventExport);
            })->map(function ($column) {
                if ($column->select) {
                    return $column;
                }

                if ($column->isType('checkbox')) {
                    $column->select = $this->resolveCheckboxColumnName($column);

                    return $column;
                }

                if (Str::startsWith($column->name, 'callback_')) {
                    $column->select = $this->resolveAdditionalSelects($column);

                    return $column;
                }

                $column->select = $this->resolveColumnName($column);

                if ($column->isEditable()) {
                    $column->select = $this->resolveEditableColumnName($column);
                }

                return $column;
            })->when($withAlias, function ($columns) {
                return $columns->map(function ($column) {
                    if (! $column->select) {
                        return null;
                    }
                    if ($column->select instanceof Expression) {
                        $sep_string = config('database.default') === 'pgsql' ? '"' : '`';

                        return new Expression($column->select->getValue().' AS '.$sep_string.$column->name.$sep_string);
                    }

                    if (is_array($column->select)) {
                        $selects = $column->select;
                        $first = array_shift($selects).' AS '.$column->name;
                        $others = array_map(function ($select) {
                            return $select.' AS '.$select;
                        }, $selects);

                        return array_merge([$first], $others);
                    }

                    return $column->select.' AS '.$column->name;
                });
            }, function ($columns) {
                return $columns->map->select;
            });
    }

    protected function resolveRelationColumn($name, $aggregate = null, $alias = null)
    {
        $parts = explode('.', Str::before($name, ':'));
        $columnName = array_pop($parts);
        $relation = implode('.', $parts);

        return  method_exists($this->query->getModel(), $parts[0])
            ? $this->joinRelation($relation, $columnName, $aggregate, $alias ?? $name)
            : $name;
    }

    protected function joinRelation($relation, $relationColumn, $aggregate = null, $alias = null)
    {
        $table = '';
        $model = '';
        $lastQuery = $this->query;
        foreach (explode('.', $relation) as $eachRelation) {
            $model = $lastQuery->getRelation($eachRelation);

            switch (true) {
                case $model instanceof HasOne:
                    $table = $model->getRelated()->getTable();
                    $foreign = $model->getQualifiedForeignKeyName();
                    $other = $model->getQualifiedParentKeyName();
                    break;

                case $model instanceof HasMany:
                    $this->query->customWithAggregate($relation, $aggregate ?? 'count', $relationColumn, $alias);
                    $table = null;
                    break;

                case $model instanceof BelongsTo:
                    $table = $model->getRelated()->getTable();
                    $foreign = $model->getQualifiedForeignKeyName();
                    $other = $model->getQualifiedOwnerKeyName();
                    break;

                case $model instanceof BelongsToMany:
                    $this->query->customWithAggregate($relation, $aggregate ?? 'count', $relationColumn, $alias);
                    $table = null;
                    break;

                case $model instanceof HasOneThrough:
                    $pivot = explode('.', $model->getQualifiedParentKeyName())[0];
                    $pivotPK = $model->getQualifiedFirstKeyName();
                    $pivotFK = $model->getQualifiedLocalKeyName();
                    $this->performJoin($pivot, $pivotPK, $pivotFK);

                    $related = $model->getRelated();
                    $table = $related->getTable();
                    $tablePK = $related->getForeignKey();
                    $foreign = $pivot.'.'.$tablePK;
                    $other = $related->getQualifiedKeyName();

                    break;

                default:
                    $this->query->customWithAggregate($relation, $aggregate ?? 'count', $relationColumn, $alias);
            }
            if ($table) {
                $this->performJoin($table, $foreign, $other);
            }
            $lastQuery = $model->getQuery();
        }

        if ($model instanceof HasOne || $model instanceof BelongsTo || $model instanceof HasOneThrough) {
            return $table.'.'.$relationColumn;
        }

        if ($model instanceof HasMany) {
            return;
        }

        if ($model instanceof BelongsToMany) {
            return;
        }
    }

    protected function performJoin($table, $foreign, $other, $type = 'left')
    {
        $joins = [];
        foreach ((array) $this->query->getQuery()->joins as $key => $join) {
            $joins[] = $join->table;
        }

        if (! in_array($table, $joins)) {
            $this->query->join($table, $foreign, '=', $other, $type);
        }
    }

    public function getFreshColumnsProperty()
    {
        $columns = $this->processedColumns->columnsArray();

        if (($name = collect($columns)->pluck('name')->duplicates()) && collect($columns)->pluck('name')->duplicates()->count()) {
            throw new Exception('Duplicate Column Name: '.$name->first());
        }

        return $columns;
    }

    public function initialiseSort()
    {
        $this->sort = $this->defaultSort()
            ? $this->defaultSort()['key']
            : collect($this->freshColumns)->reject(function ($column) {
                return $column['type'] === 'checkbox' || $column['hidden'];
            })->keys()->first();
        $this->direction = $this->defaultSort() && $this->defaultSort()['direction'] === 'asc';
    }

    public function defaultSort()
    {
        $columnIndex = collect($this->freshColumns)->search(function ($column) {
            return is_string($column['defaultSort']);
        });

        return is_numeric($columnIndex) ? [
            'key' => $columnIndex,
            'direction' => $this->freshColumns[$columnIndex]['defaultSort'],
        ] : null;
    }

    public function getSortString()
    {
        $column = $this->freshColumns[$this->sort];
        $dbTable = DB::connection()->getPDO()->getAttribute(\PDO::ATTR_DRIVER_NAME);

        switch (true) {
            case $column['sort']:
                return $column['sort'];
                break;

            case $column['base']:
                return $column['base'];
                break;

            case is_array($column['select']):
                return Str::before($column['select'][0], ' AS ');
                break;

            case $column['select']:
                return Str::before($column['select'], ' AS ');
                break;

             default:
                return $dbTable == 'pgsql' || $dbTable == 'sqlsrv'
                    ? new Expression('"'.$column['name'].'"')
                    : new Expression('`'.$column['name'].'`');
                break;
        }
    }

    public function updatingPerPage()
    {
        $this->refreshLivewireDatatable();
    }

    public function refreshLivewireDatatable()
    {
        $this->page = 1;
    }

    public function sort($index)
    {
        if ($this->sort === (int) $index) {
            $this->direction = ! $this->direction;
        } else {
            $this->sort = (int) $index;
        }
        $this->page = 1;

        $key = Str::snake(Str::afterLast(get_called_class(), '\\'));
        session()->put([$key.$this->name.'_sort' => $this->sort, $key.$this->name.'_direction' => $this->direction]);
    }

    public function toggle($index)
    {
        if ($this->sort == $index) {
            $this->initialiseSort();
        }

        if (! $this->columns[$index]['hidden']) {
            unset($this->activeSelectFilters[$index]);
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
        $this->activeNumberFilters[$index]['start'] = $start ? (int) $start : null;
        $this->clearEmptyNumberFilter($index);
        $this->page = 1;
    }

    public function doNumberFilterEnd($index, $end)
    {
        $this->activeNumberFilters[$index]['end'] = ($end !== '') ? (int) $end : null;
        $this->clearEmptyNumberFilter($index);
        $this->page = 1;
    }

    public function clearEmptyNumberFilter($index)
    {
        if ((! isset($this->activeNumberFilters[$index]['start']) || $this->activeNumberFilters[$index]['start'] == '') && (! isset($this->activeNumberFilters[$index]['end']) || $this->activeNumberFilters[$index]['end'] == '')) {
            $this->removeNumberFilter($index);
        }
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
        if (isset($key)) {
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

    public function getColumnField($index)
    {
        if ($this->freshColumns[$index]['scope']) {
            return 'scope';
        }

        if ($this->freshColumns[$index]['raw']) {
            return [(string) $this->freshColumns[$index]['sort']];
        }

        return [$this->getSelectStatements()[$index]];
    }

    public function addScopeSelectFilter($query, $index, $value)
    {
        if (! isset($this->freshColumns[$index]['scopeFilter'])) {
            return;
        }

        return $query->{$this->freshColumns[$index]['scopeFilter']}($value);
    }

    public function addScopeNumberFilter($query, $index, $value)
    {
        if (! isset($this->freshColumns[$index]['scopeFilter'])) {
            return;
        }

        return $query->{$this->freshColumns[$index]['scopeFilter']}($value);
    }

    public function addAggregateFilter($query, $index, $filter)
    {
        $column = $this->freshColumns[$index];
        $relation = Str::before($column['name'], '.');
        $aggregate = $this->columnAggregateType($column);
        $field = Str::before(explode('.', $column['name'])[1], ':');

        $query->when($column['type'] === 'boolean', function ($query) use ($filter, $relation, $field, $aggregate) {
            $query->where(function ($query) use ($filter, $relation, $field, $aggregate) {
                if ($filter) {
                    $query->hasAggregate($relation, $field, $aggregate);
                } else {
                    $query->hasAggregate($relation, $field, $aggregate, '<');
                }
            });
        })->when($aggregate === 'group_concat' && count($filter), function ($query) use ($filter, $relation, $field, $aggregate) {
            $query->where(function ($query) use ($filter, $relation, $field, $aggregate) {
                foreach ($filter as $value) {
                    $query->hasAggregate($relation, $field, $aggregate, 'like', '%'.$value.'%');
                }
            });
        })->when(isset($filter['start']), function ($query) use ($filter, $relation, $field, $aggregate) {
            $query->hasAggregate($relation, $field, $aggregate, '>=', $filter['start']);
        })->when(isset($filter['end']), function ($query) use ($filter, $relation, $field, $aggregate) {
            $query->hasAggregate($relation, $field, $aggregate, '<=', $filter['end']);
        });
    }

    public function searchableColumns()
    {
        return collect($this->freshColumns)->filter(function ($column, $key) {
            return $column['searchable'];
        });
    }

    public function scopeColumns()
    {
        return collect($this->freshColumns)->filter(function ($column, $key) {
            return isset($column['scope']);
        });
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
        return $this->mapCallbacks(
            $this->getQuery()->paginate($this->perPage)
        );
    }

    public function getSelectFiltersProperty()
    {
        return collect($this->freshColumns)->filter->selectFilter;
    }

    public function getBooleanFiltersProperty()
    {
        return collect($this->freshColumns)->filter->booleanFilter;
    }

    public function getTextFiltersProperty()
    {
        return collect($this->freshColumns)->filter->textFilter;
    }

    public function getNumberFiltersProperty()
    {
        return collect($this->freshColumns)->filter->numberFilter;
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

    public function columnIsRelation($column)
    {
        return Str::contains($column['name'], '.') && method_exists($this->builder()->getModel(), Str::before($column['name'], '.'));
    }

    public function columnIsAggregateRelation($column)
    {
        if (! $this->columnIsRelation($column)) {
            return;
        }
        $relation = $this->builder()->getRelation(Str::before($column['name'], '.'));

        return /* $relation instanceof HasOne || */ $relation instanceof HasManyThrough || $relation instanceof HasMany || $relation instanceof belongsToMany;
    }

    public function columnAggregateType($column)
    {
        return Str::contains($column['name'], ':')
            ? Str::after(explode('.', $column['name'])[1], ':')
            : (
                $column['type'] === 'string'
                    ? 'group_concat'
                    : 'count'
            );
    }

    public function buildDatabaseQuery($export = false)
    {
        $this->query = $this->builder();

        $this->query->addSelect(
            $this->getSelectStatements(true, $export)
                ->filter()
                ->flatten()
                ->toArray()
        );

        $this->addGlobalSearch()
            ->addScopeColumns()
            ->addSelectFilters()
            ->addBooleanFilters()
            ->addTextFilters()
            ->addNumberFilters()
            ->addDateRangeFilter()
            ->addTimeRangeFilter()
            ->addSort();
    }

    public function addGlobalSearch()
    {
        if (! $this->search) {
            return $this;
        }

        $this->query->where(function ($query) {
            foreach (explode(' ', $this->search) as $search) {
                $query->where(function ($query) use ($search) {
                    $this->searchableColumns()->each(function ($column, $i) use ($query, $search) {
                        $query->orWhere(function ($query) use ($i, $search) {
                            foreach ($this->getColumnField($i) as $column) {
                                $query->when(is_array($column), function ($query) use ($search, $column) {
                                    foreach ($column as $col) {
                                        $query->orWhereRaw('LOWER('.$col.') like ?', '%'.strtolower($search).'%');
                                    }
                                }, function ($query) use ($search, $column) {
                                    $query->orWhereRaw('LOWER('.$column.') like ?', '%'.strtolower($search).'%');
                                });
                            }
                        });
                    });
                });
            }
        });

        return $this;
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function addScopeColumns()
    {
        $this->scopeColumns()->each(function ($column) {
            $this->query->{$column['scope']}($column['label']);
        });

        return $this;
    }

    public function addSelectFilters()
    {
        if (count($this->activeSelectFilters) < 1) {
            return $this;
        }

        $this->query->where(function ($query) {
            foreach ($this->activeSelectFilters as $index => $activeSelectFilter) {
                $query->where(function ($query) use ($index, $activeSelectFilter) {
                    foreach ($activeSelectFilter as $value) {
                        if ($this->columnIsAggregateRelation($this->freshColumns[$index])) {
                            $this->addAggregateFilter($query, $index, $activeSelectFilter);
                        } else {
                            if (! $this->addScopeSelectFilter($query, $index, $value)) {
                                $query->orWhere(function ($query) use ($value, $index) {
                                    foreach ($this->getColumnField($index) as $column) {
                                        $query->orWhere($column, $value);
                                    }
                                });
                            }
                        }
                    }
                });
            }
        });

        return $this;
    }

    public function addBooleanFilters()
    {
        if (count($this->activeBooleanFilters) < 1) {
            return $this;
        }
        $this->query->where(function ($query) {
            foreach ($this->activeBooleanFilters as $index => $value) {
                if ($this->getColumnField($index) === 'scope') {
                    $this->addScopeSelectFilter($query, $index, $value);
                } elseif ($this->columnIsAggregateRelation($this->freshColumns[$index])) {
                    $this->addAggregateFilter($query, $index, $value);
                } elseif ($this->freshColumns[$index]['type'] === 'string') {
                    if ($value == 1) {
                        $query->whereNotNull($this->getColumnField($index)[0])
                            ->where($this->getColumnField($index)[0], '<>', '');
                    } elseif (strlen($value)) {
                        $query->where(function ($query) use ($index) {
                            $query->whereNull(DB::raw($this->getColumnField($index)[0]))
                                ->orWhere(DB::raw($this->getColumnField($index)[0]), '');
                        });
                    }
                } elseif ($value == 1) {
                    $query->where(DB::raw($this->getColumnField($index)[0]), '>', 0);
                } elseif (strlen($value)) {
                    $query->where(function ($query) use ($index) {
                        $query->whereNull(DB::raw($this->getColumnField($index)[0]))
                            ->orWhere(DB::raw($this->getColumnField($index)[0]), 0);
                    });
                }
            }
        });

        return $this;
    }

    public function addTextFilters()
    {
        if (! count($this->activeTextFilters)) {
            return $this;
        }

        $this->query->where(function ($query) {
            foreach ($this->activeTextFilters as $index => $activeTextFilter) {
                $query->where(function ($query) use ($index, $activeTextFilter) {
                    foreach ($activeTextFilter as $value) {
                        if ($this->columnIsRelation($this->freshColumns[$index])) {
                            $this->addAggregateFilter($query, $index, $activeTextFilter);
                        } else {
                            $query->orWhere(function ($query) use ($index, $value) {
                                foreach ($this->getColumnField($index) as $column) {
                                    $column = is_array($column) ? $column[0] : $column;
                                    $query->orWhereRaw('LOWER('.$column.') like ?', [strtolower("%$value%")]);
                                }
                            });
                        }
                    }
                });
            }
        });

        return $this;
    }

    public function addNumberFilters()
    {
        if (! count($this->activeNumberFilters)) {
            return $this;
        }
        $this->query->where(function ($query) {
            foreach ($this->activeNumberFilters as $index => $filter) {
                if ($this->columnIsAggregateRelation($this->freshColumns[$index])) {
                    $this->addAggregateFilter($query, $index, $filter);
                } else {
                    $this->addScopeNumberFilter($query, $index, [
                        isset($filter['start']) ? $filter['start'] : 0,
                        isset($filter['end']) ? $filter['end'] : 9999999999,
                    ])
                        ?? $query->when(isset($filter['start']), function ($query) use ($filter, $index) {
                            $query->whereRaw($this->getColumnField($index)[0].' >= ?', $filter['start']);
                        })->when(isset($filter['end']), function ($query) use ($filter, $index) {
                            $query->whereRaw($this->getColumnField($index)[0].' <= ?', $filter['end']);
                        });
                }
            }
        });

        return $this;
    }

    public function addDateRangeFilter()
    {
        if (! count($this->activeDateFilters)) {
            return $this;
        }

        $this->query->where(function ($query) {
            foreach ($this->activeDateFilters as $index => $filter) {
                if (! ((isset($filter['start']) && $filter['start'] != '') || (isset($filter['end']) && $filter['end'] != ''))) {
                    break;
                }
                $query->whereBetween($this->getColumnField($index)[0], [
                    isset($filter['start']) && $filter['start'] != '' ? $filter['start'] : '0000-00-00',
                    isset($filter['end']) && $filter['end'] != '' ? $filter['end'] : now()->format('Y-m-d'),
                ]);
            }
        });

        return $this;
    }

    public function addTimeRangeFilter()
    {
        if (! count($this->activeTimeFilters)) {
            return $this;
        }

        $this->query->where(function ($query) {
            foreach ($this->activeTimeFilters as $index => $filter) {
                $start = isset($filter['start']) && $filter['start'] != '' ? $filter['start'] : '00:00:00';
                $end = isset($filter['end']) && $filter['end'] != '' ? $filter['end'] : '23:59:59';

                if ($end < $start) {
                    $query->where(function ($subQuery) use ($index, $start, $end) {
                        $subQuery->whereBetween($this->getColumnField($index)[0], [$start, '23:59'])
                            ->orWhereBetween($this->getColumnField($index)[0], ['00:00', $end]);
                    });
                } else {
                    $query->whereBetween($this->getColumnField($index)[0], [$start, $end]);
                }
            }
        });

        return $this;
    }

    public function addSort()
    {
        if (isset($this->sort)) {
            $this->query->orderBy(DB::raw($this->getSortString()), $this->direction ? 'asc' : 'desc');
        }

        return $this;
    }

    public function getCallbacksProperty()
    {
        return collect($this->freshColumns)->filter->callback->mapWithKeys(function ($column) {
            return [$column['name'] => $column['callback']];
        });
    }

    public function getEditablesProperty()
    {
        return collect($this->freshColumns)->filter(function ($column) {
            return $column['type'] === 'editable';
        })->mapWithKeys(function ($column) {
            return [$column['name'] => true];
        });
    }

    public function mapCallbacks($paginatedCollection)
    {
        $paginatedCollection->getCollection()->map(function ($row, $i) {
            foreach ($row as $name => $value) {
                if (isset($this->editables[$name])) {
                    $row->$name = view('datatables::editable', [
                        'value' => $value,
                        'table' => $this->builder()->getModel()->getTable(),
                        'column' => Str::after($name, '.'),
                        'rowId' => $row->{$this->builder()->getModel()->getTable().'.'.$this->builder()->getModel()->getKeyName()},
                    ]);
                } elseif (isset($this->callbacks[$name]) && is_string($this->callbacks[$name])) {
                    $row->$name = $this->{$this->callbacks[$name]}($value, $row);
                } elseif (Str::startsWith($name, 'callback_')) {
                    $row->$name = $this->callbacks[$name](...explode(static::SEPARATOR, $value));
                } elseif (isset($this->callbacks[$name]) && is_callable($this->callbacks[$name])) {
                    $row->$name = $this->callbacks[$name]($value, $row);
                }

                if ($this->search && ! config('livewire-datatables.suppress_search_highlights') && $this->searchableColumns()->firstWhere('name', $name)) {
                    $row->$name = $this->highlight($row->$name, $this->search);
                }
            }

            return $row;
        });

        return $paginatedCollection;
    }

    public function getDisplayValue($index, $value)
    {
        return is_array($this->freshColumns[$index]['filterable']) && is_numeric($value)
            ? collect($this->freshColumns[$index]['filterable'])->firstWhere('id', '=', $value)['name'] ?? $value
            : $value;
    }

    /*  This can be called to apply highlting of the search term to some string.
     *  Motivation: Call this from your Column::Callback to apply highlight to a chosen section of the result.
     */
    public function highlightStringWithCurrentSearchTerm(string $originalString)
    {
        if (! $this->search) {
            return $originalString;
        } else {
            return static::highlightString($originalString, $this->search);
        }
    }

    /* Utility function for applying highlighting to given string */
    public static function highlightString(string $originalString, string $searchingForThisSubstring)
    {
        $searchStringNicelyHighlightedWithHtml = view(
            'datatables::highlight',
            ['slot' => $searchingForThisSubstring]
        )->render();
        $stringWithHighlightedSubstring = str_ireplace(
            $searchingForThisSubstring,
            $searchStringNicelyHighlightedWithHtml,
            $originalString
        );

        return $stringWithHighlightedSubstring;
    }

    public function highlight($value, $string)
    {
        $output = substr($value, stripos($value, $string), strlen($string));

        if ($value instanceof View) {
            return $value
                ->with(['value' => str_ireplace($string, view('datatables::highlight', ['slot' => $output]), $value->gatherData()['value'] ?? $value->gatherData()['slot'])]);
        }

        return str_ireplace($string, view('datatables::highlight', ['slot' => $output]), $value);
    }

    public function render()
    {
        $this->emit('refreshDynamic');

        return view('datatables::datatable');
    }

    public function export()
    {
        $this->forgetComputed();

        return Excel::download(new DatatableExport($this->getQuery(true)->get()), 'DatatableExport.xlsx');
    }

    public function getQuery($export = false)
    {
        $this->buildDatabaseQuery($export);

        return $this->query->toBase();
    }

    public function checkboxQuery()
    {
        $select = $this->resolveCheckboxColumnName(collect($this->freshColumns)->firstWhere('type', 'checkbox'));

        return $this->query->reorder()->get()->map(function ($row) {
            return (string) $row->checkbox_attribute;
        });
    }

    public function toggleSelectAll()
    {
        if (count($this->selected) === $this->getQuery()->count()) {
            $this->selected = [];
        } else {
            $this->selected = $this->checkboxQuery()->values()->toArray();
        }
        $this->forgetComputed();
    }
}
