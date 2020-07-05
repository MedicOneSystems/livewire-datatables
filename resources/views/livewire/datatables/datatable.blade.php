<div class="">
    @if($this->searchableColumns()->count())
    <div class="w-full sm:w-2/3 md:w-2/5 mt-1 mb-2 flex rounded-lg shadow-sm">
        <div class="relative flex-grow focus-within:z-10">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg class="h-5 w-5 text-gray-400" viewBox="0 0 20 20" stroke="currentColor" fill="none">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </div>
            <input wire:model.debounce.500ms="search"
                class="form-input block bg-gray-50 focus:bg-white w-full rounded-md pl-10 transition ease-in-out duration-150 sm:text-sm sm:leading-5"
                placeholder="search in {{ $this->searchableColumns()->map->label->join(', ') }}" />
            <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                <button wire:click="$set('search', null)" class="text-gray-300 hover:text-red-600 focus:outline-none">
                    <x-icons.x-circle class="h-5 w-5 stroke-current" />
                </button>
            </div>
        </div>
    </div>
    @endif
    <div class="rounded-lg shadow-lg bg-white">
        <div
            class="rounded-lg @unless($this->hidePagination) rounded-b-none @endif max-w-screen overflow-x-scroll bg-white">
            <div class="table align-middle min-w-full">
                @unless($this->hideHeader)
                <div class="table-row divide-x divide-gray-200">
                    {{-- @foreach($this->visibleColumns as $index => $column) --}}
                    @foreach($columns as $index => $column)
                    @if($column['hidden'])
                    <div wire:click.prefetch="toggle('{{ $index }}')"
                        class="relative table-cell h-12 w-3 bg-blue-100 hover:bg-blue-300 overflow-none align-top group"
                        style="min-width:12px max:width:12px">
                        <button class="relative h-12 w-3 focus:outline-none">
                            <span
                                class="w-32 hidden group-hover:inline-block absolute z-10 top-0 left-0 ml-3 bg-blue-300 font-medium leading-4 text-xs text-left text-blue-700 tracking-wider transform uppercase focus:outline-none">
                                {{ str_replace('_', ' ', $column['label']) }}
                            </span>
                        </button>
                        <svg class="absolute text-blue-100 fill-current w-full inset-x-0 bottom-0"
                            viewBox="0 0 314.16 207.25">
                            <path stroke-miterlimit="10" d="M313.66 206.75H.5V1.49l157.65 204.9L313.66 1.49v205.26z" />
                        </svg>
                    </div>
                    @else
                    <div class="relative table-cell h-12 overflow-hidden align-top">
                        <button wire:click.prefetch="sort('{{ $index }}')"
                            class="w-full h-full px-6 py-3 border-b border-gray-200 bg-gray-50 text-left text-xs leading-4 font-medium text-gray-500 uppercase tracking-wider flex justify-between items-center focus:outline-none">
                            <span class="inline ">{{ str_replace('_', ' ', $column['label']) }}</span>
                            <span class="inline text-xs text-blue-400">
                                @if($sort === $index)
                                @if($direction)
                                <x-icons.chevron-up class="h-6 w-6 text-green-600 stroke-current" />
                                @else
                                <x-icons.chevron-down class="h-6 w-6 text-green-600 stroke-current" />
                                @endif
                                @endif
                            </span>
                        </button>
                        <button wire:click.prefetch="toggle('{{ $index }}')"
                            class="absolute bottom-1 right-1 focus:outline-none">
                            <x-icons.arrow-circle-left class="h-3 w-3 text-gray-300 hover:text-blue-400" />
                        </button>
                    </div>
                    @endif
                    @endforeach
                </div>

                <div class="table-row divide-x divide-blue-200 bg-blue-100">
                    @foreach($columns as $index => $column)
                    @if($column['hidden'])
                    <div class="table-cell w-5 overflow-hidden align-top bg-blue-100">
                    </div>
                    @else
                    <div class="table-cell overflow-hidden align-top">
                        @isset($column['filterable'])
                        @if( is_iterable($column['filterable']) )
                        <div wire:key="{{ $index }}">
                            @include('datatables::filters.select', ['index' => $index, 'name' =>
                            $column['label'], 'options' => $column['filterable']])
                        </div>
                        @else
                        <div wire:key="{{ $index }}">
                            @include('datatables::filters.' . $column['type'], ['index' => $index, 'name' =>
                            $column['label']])
                        </div>
                        @endif
                        @endisset
                    </div>
                    @endif
                    @endforeach
                </div>
                @endif
                @foreach($this->results as $result)
                <div class="table-row p-1 divide-x divide-gray-100 {{ $loop->even ? 'bg-gray-100' : 'bg-gray-50' }}">
                    @foreach($columns as $column)
                    @if($column['hidden'])
                    <div class="table-cell w-5 overflow-hidden align-top">
                    </div>
                    @else
                    <div class="table-cell px-6 py-2 whitespace-no-wrap text-sm leading-5 text-gray-900">
                        {!! $result->{$column['label']} !!}
                    </div>
                    @endif
                    @endforeach
                </div>
                @endforeach
            </div>
        </div>
        @unless($this->hidePagination)
        <div class="rounded-lg rounded-t-none max-w-screen rounded-lg border-b border-gray-200 bg-white">
            <div class="p-2 flex items-center justify-between">
                <div class="flex items-center">
                    <select name="perPage"
                        class="mt-1 form-select block w-full pl-3 pr-10 py-2 text-base leading-6 border-gray-300 focus:outline-none focus:shadow-outline-blue focus:border-blue-300 sm:text-sm sm:leading-5"
                        wire:model="perPage">
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                        <option value="99999999">All</option>
                    </select>
                </div>

                <div>
                    <div class="flex lg:hidden justify-center">
                        <span
                            class="flex items-center space-x-2">{{ $this->results->links('datatables::tailwind-simple-pagination') }}</span>
                    </div>

                    <div class="hidden lg:flex justify-center">
                        <span>{{ $this->results->links('datatables::tailwind-pagination') }}</span>
                    </div>
                </div>

                <div class="text-gray-600">
                    Results {{ $this->results->firstItem() }} - {{ $this->results->lastItem() }} of
                    {{ $this->results->total() }}
                </div>
            </div>

        </div>
        @endif
    </div>

    {{--
    @if($this->activeFilters)
    <div class="mt-4 p-4 rounded overflow-hidden align-middle min-w-full shadow sm:rounded-lg border-b border-gray-200 bg-white">
        <div class="h-6 flex justify-between items-center">
            <label class="uppercase tracking-wide text-blue-600 text-lg">Active Filters</label>
            <button wire:click="clearAllFilters" class="px-2 py-1 flex items-center uppercase tracking-wide border-2 border-transparent hover:bg-red-400 text-gray-600 hover:text-white rounded-full focus:outline-none text-xs space-x-1">
                <span>CLEAR ALL</span>
                <x-icons.x-circle />
            </button>
        </div>
        <div class="flex flex-wrap mt-2 -mx-2">
            @if(isset($dates['column']) && ((isset($dates['start']) || isset($dates['end']))))
            <button wire:click="clearDateFilter" class="px-2 py-1 flex items-center uppercase tracking-wide border-2 border-transparent hover:bg-red-400 text-gray-600 hover:text-white rounded-full focus:outline-none text-xs space-x-1">
                <span class="">{{ $this->getColumnColumn($dates['column']) . ' between ' . Carbon\Carbon::parse($dates['start'] ?? '2000-01-01')->format('d/m/Y') . ' and ' . Carbon\Carbon::parse($dates['end'] ?? now())->format('d/m/Y') }}</span>
    <x-icons.x-circle />
    </button>
    @endif

    @if(isset($times['column']) && ((isset($times['start']) || isset($times['end']))))
    <button wire:click="clearTimeFilter"
        class="px-2 py-1 flex items-center uppercase tracking-wide border-2 border-transparent hover:bg-red-400 text-gray-600 hover:text-white rounded-full focus:outline-none text-xs space-x-1">
        <span
            class="">{{ $this->getColumnColumn($times['column'])  . ' between ' . ($times['start'] ?? '00:00') . ' and ' . ($times['end'] ?? '23:59') }}</span>
        <x-icons.x-circle />
    </button>
    @endif
    @foreach($activeSelectFilters as $index => $activeSelectFilter)
    @foreach($activeSelectFilter as $key => $value)
    <button wire:click="removeSelectFilter('{{ $index }}', '{{ $key }}')"
        class="px-2 py-1 flex items-center uppercase tracking-wide border-2 border-transparent hover:bg-red-400 text-gray-600 hover:text-white rounded-full focus:outline-none text-xs space-x-1">
        <span>{{ $this->getColumnLabel($index) . ": " . $this->getDisplayValue($index, $value) }}</span>
        <x-icons.x-circle />
    </button>
    @endforeach
    @endforeach
    @foreach($activeBooleanFilters as $index => $activeBooleanFilter)
    <button wire:click=" removeBooleanFilter('{{ $index }}')"
        class="px-2 py-1 flex items-center uppercase tracking-wide border-2 border-transparent hover:bg-red-400 text-gray-600 hover:text-white rounded-full focus:outline-none text-xs space-x-1">
        <span class="">{{ $this->getColumnLabel($index) . ": " . ($activeBooleanFilter == 1 ? 'Yes' : 'No') }}</span>
        <x-icons.x-circle />
    </button>
    @endforeach
    @foreach($activeTextFilters as $index => $activeTextFilter)
    <button wire:click="removeTextFilter('{{ $index }}')"
        class="px-2 py-1 flex items-center uppercase tracking-wide border-2 border-transparent hover:bg-red-400 text-gray-600 hover:text-white rounded-full focus:outline-none text-xs space-x-1">
        <span class="">{{ $this->getColumnLabel($index) . ": " . $activeTextFilter }}</span>
        <x-icons.x-circle />
    </button>
    @endforeach
    @foreach($activeNumberFilters as $index => $activeNumberFilter)
    <button wire:click="removeNumberFilter('{{ $index }}')"
        class="px-2 py-1 flex items-center uppercase tracking-wide border-2 border-transparent hover:bg-red-400 text-gray-600 hover:text-white rounded-full focus:outline-none text-xs space-x-1">
        <span
            class="">{{ $this->getColumnLabel($index) . ": between " . ($activeNumberFilter['start'] ?? '0') . " and " . ($activeNumberFilter['end'] ?? 'max') }}</span>
        <x-icons.x-circle />
    </button>
    @endforeach
</div>
</div>
@endif

@if(count($this->dateFilters))
<div
    class="mt-4 p-4 rounded overflow-hidden align-middle min-w-full shadow sm:rounded-lg border-b border-gray-200 bg-white">
    <div class="h-6 flex justify-between items-center">
        <label class="uppercase tracking-wide text-blue-600 text-lg">Date Range</label>
        class="@if(!isset($dates['column']) || $dates['column'] === '') hidden @endif px-2 py-1 flex items-center
        uppercase tracking-wide border-2 border-transparent hover:bg-red-400 text-gray-600 hover:text-white rounded-full
        focus:outline-none text-xs space-x-1">
        <span>CLEAR</span>
        <x-icons.x-circle />
        </button>
    </div>
    <div class="xl:grid grid-cols-2 gap-4">
        <div class="mt-2 grid grid-cols-3 gap-4">
            <select name="dateColumn" wire:model="dates.column" class="w-full form-select">
                <option></option>
                @foreach($this->dateFilters as $index => $column)
                <option value="{{ $index }}">{{ $column['label'] }}</option>
                @endforeach
            </select>

            <input type="date" name="start" wire:model="dates.start" class="w-full form-input" />
            <input type="date" name='end' wire:model="dates.end" class="w-full form-input" />
        </div>
        <div class="mt-4 xl:mt-2 grid grid-cols-3 md:grid-cols-6 gap-2">
            @foreach(get_class_methods(Mediconesystems\LivewireDatatables\Traits\WithPresetDateFilters::class) as
            $preset)
            <button
                class="px-3 py-2 rounded text-white text-xs uppercase tracking-wide focus:outline-none bg-blue-500 hover:bg-blue-800"
                wire:click="{{ $preset }}">
                {{ implode(' ', preg_split('/(?=[A-Z])/', $preset)) }}
            </button>
            @endforeach
        </div>
    </div>
</div>
@endif

@if(count($this->timeFilters))
<div
    class="mt-4 p-4 rounded overflow-hidden align-middle min-w-full shadow sm:rounded-lg border-b border-gray-200 bg-white">
    <div class="h-6 flex justify-between items-center">
        <label class="uppercase tracking-wide text-blue-600 text-lg">Time Range</label>
        <button wire:click="clearTimeFilter"
            class="@if(!isset($times['column']) || $times['column'] === '') hidden @endif px-2 py-1 flex items-center uppercase tracking-wide border-2 border-transparent hover:bg-red-400 text-gray-600 hover:text-white rounded-full focus:outline-none text-xs space-x-1">
            <span>CLEAR</span>
            <x-icons.x-circle />
        </button>
    </div>
    <div class="xl:grid grid-cols-2 gap-4">
        <div class="mt-2 grid grid-cols-3 gap-4">
            <select name="timeColumn" wire:model="times.column" class="w-full form-select">
                <option></option>
                @foreach($this->timeFilters as $index => $column)
                <option value="{{ $index }}">{{ $column['label']}}</option>
                @endforeach
            </select>
            <input type="time" name="start" wire:model="times.start" class="w-full form-input">
            <input type="time" name="end" wire:model="times.end" class="w-full form-input">
        </div>
        <div class="mt-4 xl:mt-2 grid grid-cols-3 md:grid-cols-6 gap-2">
            @foreach(get_class_methods(Mediconesystems\LivewireDatatables\Traits\WithPresetTimeFilters::class) as
            $preset)
            <button
                class="px-3 py-2 rounded text-white text-xs uppercase tracking-wide focus:outline-none bg-blue-500 hover:bg-blue-800"
                wire:click="{{ $preset }}">
                {{ implode(' ', preg_split('/(?=[A-Z])/', $preset)) }}
            </button>
            @endforeach
        </div>
    </div>
</div>
@endif

@if(count($this->selectFilters) || count($this->booleanFilters) || count($this->textFilters) ||
count($this->numberFilters))
<div
    class="mt-4 p-4 rounded overflow-hidden align-middle min-w-full shadow sm:rounded-lg border-b border-gray-200 bg-white">
    <div class="h-6 flex justify-between items-center">
        <label class="uppercase tracking-wide text-blue-600 text-lg">Filters</label>
        <button wire:click="clearFilter"
            class="@unless(count($this->activeSelectFilters) || count($this->activeBooleanFilters) || count($this->activeTextFilters)) hidden @endif px-2 py-1 flex items-center uppercase tracking-wide border-2 border-transparent hover:bg-red-400 text-gray-600 hover:text-white rounded-full focus:outline-none text-xs space-x-1">
            <span>CLEAR</span>
            <x-icons.x-circle />
        </button>
    </div>

    <div class="mt-2 grid grid-cols-3 gap-4">
        @foreach($this->selectFilters as $i => $filter)
        <div class="w-full relative">
            <label class="uppercase tracking-wide text-gray-600 text-xs py-1 rounded flex justify-between"
                for="{{ $filter['label'] }}">
                {{ ucwords(str_replace('_', ' ', str_replace('_id', '', $filter['label']))) }}
            </label>
            <div class="">

            </div>
        </div>
        @endforeach


        @foreach($this->booleanFilters as $i => $filter)
        <div class="w-full relative">
            <label class="uppercase tracking-wide text-gray-600 text-xs py-1 rounded flex justify-between"
                for="{{ $filter['label'] }}">
                {{ ucwords(str_replace('_', ' ', str_replace('_id', '', $filter['label']))) }}
            </label>
            <div class="relative">
                <select name="{{ $filter['label'] }}" class="w-full form-select"
                    wire:input="doBooleanFilter('{{ $i }}', $event.target.value)">
                    <option value=""></option>
                    <option value="0">No</option>
                    <option value="1">Yes</option>
                </select>
            </div>
        </div>
        @endforeach

        @foreach($this->textFilters as $i => $filter)
        <div class="w-full relative">
            <label class="uppercase tracking-wide text-gray-600 text-xs py-1 rounded flex justify-between"
                for="{{ $filter['label'] }}">
                <span>{{ ucwords(str_replace('_', ' ', $filter['label'])) }}</span>
            </label>
            <div class="relative">
                <input name="{{ $filter['label'] }}" type="text" class="w-full form-input"
                    wire:input.lazy="doTextFilter('{{ $i }}', $event.target.value)" />
            </div>
        </div>
        @endforeach

        @foreach($this->numberFilters as $i => $filter)
        <div class="w-full relative">
            <label class="uppercase tracking-wide text-gray-600 text-xs py-1 rounded flex justify-between"
                for="{{ $filter['label'] }}">
                <span>{{ ucwords(str_replace('_', ' ', $filter['label'])) }}</span>
            </label>

        </div>
        @endforeach

    </div>
</div>
@endif
--}}

<div wire:loading>
    <div class="fixed z-50 bottom-0 inset-x-0 px-4 pb-4 sm:inset-0 sm:flex sm:items-center sm:justify-center">
        <div x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
            class="fixed inset-0 transition-opacity">
            <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
        </div>
        <x-icons.cog class="h-36 w-36 spinner" />
    </div>
</div>
</div>