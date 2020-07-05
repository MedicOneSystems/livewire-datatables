<div class="flex flex-col">
    <div class="w-full flex">
        <input class="m-1 text-sm pt-1 flex-grow form-input" type="date"
            wire:change="doDateFilterStart('{{ $index }}', $event.target.value)" style="padding-bottom: 5px" />
    </div>
    <div class="w-full flex">
        <input class="m-1 text-sm pt-1 flex-grow form-input" type="date"
            wire:change="doDateFilterEnd('{{ $index }}', $event.target.value)" style="padding-bottom: 5px" />
    </div>
</div>