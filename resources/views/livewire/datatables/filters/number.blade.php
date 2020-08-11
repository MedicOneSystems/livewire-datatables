<div class="flex flex-col">
    <div x-data class="relative flex">
        <input
            x-ref="input"
            type="number"
            wire:input.debounce.500ms="doNumberFilterStart('{{ $index }}', $event.target.value)"
            class="m-1 pr-6 text-sm leading-4 flex-grow form-input"
            placeholder="MIN"
        />
        <div class="absolute inset-y-0 right-0 pr-2 flex items-center">
            <button x-on:click="$refs.input.value=''" wire:click="doNumberFilterStart('{{ $index }}', '')" class="inline-flex text-gray-400 hover:text-red-600 focus:outline-none" tabindex="-1">
                <x-icons.x-circle class="h-3 w-3 stroke-current" />
            </button>
        </div>
    </div>

    <div x-data class="relative flex">
        <input
            x-ref="input"
            type="number"
            wire:input.debounce.500ms="doNumberFilterEnd('{{ $index }}', $event.target.value)"
            class="m-1 pr-6 text-sm leading-4 flex-grow form-input"
            placeholder="MAX"
        />
        <div class="absolute inset-y-0 right-0 pr-2 flex items-center">
            <button x-on:click="$refs.input.value=''" wire:click="doNumberFilterEnd('{{ $index }}', '')" class="inline-flex text-gray-400 hover:text-red-600 focus:outline-none" tabindex="-1">
                <x-icons.x-circle class="h-3 w-3 stroke-current" />
            </button>
        </div>
    </div>
</div>