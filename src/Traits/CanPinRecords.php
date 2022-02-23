<?php

namespace Mediconesystems\LivewireDatatables\Traits;

use Mediconesystems\LivewireDatatables\Action;

/**
 * Use this trait to enable the functionality to pin specific records to the
 * top of the table.
 *
 * Ensure to;
 *
 * 1) have at least one Checkbox Column in your table
 * 2) have session storage activated (it is by default)
 * 3) to enable the mass bulk action dropdown as described in:
 *
 * @link https://github.com/MedicOneSystems/livewire-datatables#mass-bulk-action
 */
trait CanPinRecords
{
    public array $pinnedRecords = [];

    public string $sessionKeyPrefix = '_pinned_records';

    public function buildActions()
    {
        return array_merge(parent::buildActions() ?? [], [
            Action::value('pin')
                ->label(__('Pin selected Records'))
                ->callback(function ($mode, $items) {
                    $this->pinnedRecords = array_merge($this->pinnedRecords, $items);
                    $this->selected = $this->pinnedRecords;

                    session()->put($this->sessionKey(), $this->pinnedRecords);
                }),

            Action::value('unpin')
                ->label(__('Unpin selected Records'))
                ->callback(function ($mode, $items) {
                    $this->pinnedRecords = array_diff($this->pinnedRecords, $items);
                    $this->selected = $this->pinnedRecords;

                    session()->put($this->sessionKey(), $this->pinnedRecords);
                }),
        ]);
    }

    protected function initialisePinnedRecords()
    {
        if (session()->has($this->sessionKey())) {
            $this->pinnedRecords = session()->get($this->sessionKey());
        }

        $this->selected = $this->pinnedRecords;
    }

    private function sessionKey(): string
    {
        return $this->sessionStorageKey() . $this->sessionKeyPrefix;
    }
}
