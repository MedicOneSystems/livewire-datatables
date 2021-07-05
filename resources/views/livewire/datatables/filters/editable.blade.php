<div x-data class="flex flex-col">
    <input
        x-ref="input"
        type="text"
        class="pr-8 m-1 text-sm leading-4 block rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
        wire:change="doTextFilter('{{ $index }}', $event.target.value)"
        x-on:change="$refs.input.value = ''"
    />
    <div class="flex flex-wrap max-w-48 space-x-1">
        @foreach($this->activeTextFilters[$index] ?? [] as $key => $value)
        <button wire:click="removeTextFilter('{{ $index }}', '{{ $key }}')" class="m-1 pl-1 flex items-center uppercase tracking-wide bg-gray-300 text-white hover:bg-red-600 rounded-full focus:outline-none text-xs space-x-1">
            <span>{{ $this->getDisplayValue($index, $value) }}</span>
            <x-icons.x-circle  class="h-5 w-5 stroke-current text-red-500"  />
        </button>
        @endforeach
    </div>
</div>
