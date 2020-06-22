<?php

namespace Mediconesystems\LivewireDatatables;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Mediconesystems\LivewireDatatables\Skeleton\SkeletonClass
 */
class LivewireDatatablesFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'livewire-datatables';
    }
}
