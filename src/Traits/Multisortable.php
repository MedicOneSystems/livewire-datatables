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

    public function columnSortDirection(string $sort):string
    {
        if (Str::contains($sort,'|')){
            return Str::after($sort, '|');
        }

        return 'desc';
    }

    /**
     * Order the table by the given columns.
     *
     * @param array $columns
     * @param null $direction
     * @throws \Exception
     */
    public function sort(array $columns, $direction = null)
    {
        if (! in_array($direction, [null, 'asc', 'desc'])) {
            throw new \Exception("Invalid direction $direction given in sort() method. Allowed values: asc, desc.");
        }

        foreach ($columns as $column){
            if (!in_array($column, $this->sort)){
                array_push($this->sort,$column);
            }
        }

        $this->page = 1;
    }

    public function initialiseSort()
    {
        $freshColumns = collect($this->freshColumns)->reject(function ($column) {
            return in_array($column['type'], Column::UNSORTABLE_TYPES) || $column['hidden'];
        })->keys();

        $this->sort = ($columns = $this->defaultSort())->isNotEmpty()
                ? $columns->pluck('key')->toArray()
                : [$freshColumns->first()];
    }
}
