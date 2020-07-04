<div class="flex flex-col">
    <div class="w-full flex">
        <input class="m-1 text-sm pt-1 flex-grow form-input" type="time"
            wire:change="doTimeFilterStart('{{ $index }}', $event.target.value)" style="padding-bottom: 5px" />
    </div>
    <div class="w-full flex">
        <input class="m-1 text-sm pt-1 flex-grow form-input" type="time"
            wire:change="doTimeFilterEnd('{{ $index }}', $event.target.value)" style="padding-bottom: 5px" />
    </div>
</div>







if ($times['start'] < $times['end']) { $query->whereBetween($times['field'], [$times['start'], $times['end']]);
    } else {
    $query->where(function ($subQuery) use ($times) {
    $subQuery->whereBetween($times['field'], [$times['start'], '23:59'])
    ->orWhereBetween($times['field'], ['00:00', $times['end']]);
    });
    }