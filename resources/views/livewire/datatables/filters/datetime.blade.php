<div x-data class="flex flex-col">
    <div class="w-full relative flex">
        <input x-ref="start" class="w-full pr-8 m-1 text-sm leading-4 block rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50" type="datetime-local"
            wire:change="doDatetimeFilterStart('{{ $index }}', $event.target.value)" style="padding-bottom: 5px"
            value="{{ $this->activeDatetimeFilters[$index]['start'] ?? ''}}"
            />
        <div class="absolute inset-y-0 right-0 pr-2 flex items-center">
            <button x-on:click="$refs.start.value=''" wire:click="doDatetimeFilterStart('{{ $index }}', '')" class="-mb-0.5 pr-1 flex text-gray-400 hover:text-red-600 focus:outline-none" tabindex="-1">
                <x-datatables.icons.x-circle class="h-5 w-5 stroke-current" />
            </button>
        </div>
    </div>
    <div class="w-full relative flex items-center">
        <input x-ref="end" class="w-full pr-8 m-1 text-sm leading-4 block rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50" type="datetime-local"
            wire:change="doDatetimeFilterEnd('{{ $index }}', $event.target.value)" style="padding-bottom: 5px"
            value="{{ $this->activeDatetimeFilters[$index]['end'] ?? ''}}"
            />
        <div class="absolute inset-y-0 right-0 pr-2 flex items-center">
            <button x-on:click="$refs.end.value=''" wire:click="doDatetimeFilterEnd('{{ $index }}', '')" class="-mb-0.5 pr-1 flex text-gray-400 hover:text-red-600 focus:outline-none" tabindex="-1">
                <x-datatables.icons.x-circle class="h-5 w-5 stroke-current" />
            </button>
        </div>
    </div>
</div>