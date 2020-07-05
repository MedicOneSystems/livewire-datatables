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