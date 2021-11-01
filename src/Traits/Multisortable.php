<?php

namespace Mediconesystems\LivewireDatatables\Traits;

use Illuminate\Support\Str;
use Mediconesystems\LivewireDatatables\Column;

trait Multisortable
{
    public function addSort()
    {
        if (!empty($this->sort)){
            foreach (collect($this->sort)->toArray() as $column){
                if (!is_numeric($column) && !is_null(($index = optional(collect($this->freshColumns)->where('name', Str::before($column,'|')))->keys()->first()))){
                    $sortString = Str::after($this->getSortString($index), '.').' '. $this->columnSortDirection($column);
                }
                else{
                    $direction = $this->freshColumns[$column]['defaultSort'] ?? 'desc';
                    $sortString = Str::after($this->getSortString($column), '.').' '.$direction;
                }

                $this->query->orderByRaw($sortString);
            }
        }
        return $this;
    }

    /**
     * Order the table by a given column index starting from 0.
     *
     * @param  array  $indexes  which columns to sort by
     * @param  string|null  $direction  needs to be 'asc' or 'desc'. set to null to toggle the current direction.
     * @return void
     */
    public function sort(array $indexes, $direction = null)
    {
        if (! in_array($direction, [null, 'asc', 'desc'])) {
            throw new \Exception("Invalid direction $direction given in sort() method. Allowed values: asc, desc.");
        }

        foreach ($indexes as $index){
            if (!in_array($index, $this->sort)){
                array_push($this->sort,$index);
            }
        }

        $this->page = 1;

//        $key = Str::snake(Str::afterLast(get_called_class(), '\\'));
//        session()->put([$key . $this->name . '_sort' => $this->sort, $key . $this->name . '_direction' => $this->direction]);
    }

    public function initialiseSort()
    {
        $freshColumns = collect($this->freshColumns)->reject(function ($column) {
            return in_array($column['type'], Column::UNSORTABLE_TYPES) || $column['hidden'];
        })->keys();

        $this->sort = ($columns = $this->defaultSort())
                ? $columns->pluck('key')->toArray()
                : $freshColumns->toArray();


        $this->getSessionStoredSort();
    }
}
