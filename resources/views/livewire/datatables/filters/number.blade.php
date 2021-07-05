<div class="flex flex-col">
    <div x-data class="relative flex">
        <input
            x-ref="min"
            type="number"
            wire:input.debounce.500ms="doNumberFilterStart('{{ $index }}', $event.target.value)"
            class="w-full pr-8 m-1 text-sm leading-4 block rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
            placeholder="{{ __('MIN') }}"
        />
        <div class="absolute inset-y-0 right-0 pr-2 flex items-center">
            <button x-on:click="$refs.min.value=''" wire:click="doNumberFilterStart('{{ $index }}', '')" class="-mb-0.5 pr-1 flex text-gray-400 hover:text-red-600 focus:outline-none" tabindex="-1">
                <x-icons.x-circle class="h-5 w-5 stroke-current" />
            </button>
        </div>
    </div>

    <div x-data class="relative flex">
        <input
            x-ref="max"
            type="number"
            wire:input.debounce.500ms="doNumberFilterEnd('{{ $index }}', $event.target.value)"
            class="w-full pr-8 m-1 text-sm leading-4 block rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
            placeholder="{{ __('MAX') }}"
        />
        <div class="absolute inset-y-0 right-0 pr-2 flex items-center">
            <button x-on:click="$refs.max.value=''" wire:click="doNumberFilterEnd('{{ $index }}', '')" class="-mb-0.5 pr-1 flex text-gray-400 hover:text-red-600 focus:outline-none" tabindex="-1">
                <x-icons.x-circle class="h-5 w-5 stroke-current" />
            </button>
        </div>
    </div>
</div>
