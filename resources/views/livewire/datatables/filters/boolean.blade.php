<div x-data class="flex flex-col">
    <select
        x-ref="select"
        name="{{ $name }}"
        class="m-1 text-sm leading-4 block rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
        wire:input="doBooleanFilter('{{ $index }}', $event.target.value)"
        x-on:input="$refs.select.value=''"
    >
        <option value=""></option>
        <option value="0">{{ __('No') }}</option>
        <option value="1">{{ __('Yes') }}</option>
    </select>

    <div class="flex flex-wrap max-w-48 space-x-1">
        @isset($this->activeBooleanFilters[$index])
        @if($this->activeBooleanFilters[$index] == 1)
        <button wire:click="removeBooleanFilter('{{ $index }}')"
            class="m-1 pl-1 flex items-center uppercase tracking-wide bg-gray-300 text-white hover:bg-red-600 rounded-full focus:outline-none text-xs space-x-1">
            <span>{{ __('YES') }}</span>
            <x-icons.x-circle />
        </button>
        @elseif(strlen($this->activeBooleanFilters[$index]) > 0)
        <button wire:click="removeBooleanFilter('{{ $index }}')"
            class="m-1 pl-1 flex items-center uppercase tracking-wide bg-gray-300 text-white hover:bg-red-600 rounded-full focus:outline-none text-xs space-x-1">
            <span>{{ __('No') }}</span>
            <x-icons.x-circle />
        </button>
        @endif
        @endisset
    </div>
</div>
