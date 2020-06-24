<div class="p-8">
    <div class="mb-4 grid grid-cols-4 md:grid-cols-8 lg:grid-cols-10 gap-2">
        @foreach($fields as $index => $field)
        <button wire:click.prefetch="toggle('{{ $index }}')" class="px-3 py-2 rounded text-white text-xs focus:outline-none {{ $field['hidden'] ? 'bg-blue-100 hover:bg-blue-300 text-blue-600' : 'bg-blue-500 hover:bg-blue-800' }}">
            {{ str_replace('_', ' ', $field['name']) }}
        </button>
        @endforeach
    </div>

    <div class="rounded-lg shadow bg-white">
        <div class="rounded-lg rounded-b-none max-w-screen overflow-x-scroll bg-white">
            <div class="table align-middle min-w-full">
                <div class="table-row divide-x-2 divide-gray-200">
                    @foreach ($this->columns as $key)
                    <div class="table-cell h-12 overflow-hidden align-top">
                        <button wire:click.prefetch="sort('{{ $key }}')" class="w-full h-full px-6 py-3 border-b border-gray-200 bg-gray-50 text-left text-xs leading-4 font-medium text-gray-500 uppercase tracking-wider flex justify-between items-center focus:outline-none">
                            <span class="inline ">{{ str_replace('_', ' ', $key) }}</span>
                            <span class="inline text-xs text-blue-400">
                                @if($sort === $key)
                                @if($direction)
                                <x-icons.chevron-up class="h-6 w-6 text-green-600 stroke-current" />
                                @else
                                <x-icons.chevron-down class="h-6 w-6 text-green-600 stroke-current" />
                                @endif
                                @endif
                            </span>
                        </button>
                    </div>
                    @endforeach
                </div>
                @foreach($this->results as $result)
                <div class="table-row p-1 divide-x divide-gray-100 {{ $loop->even ? 'bg-gray-100' : 'bg-gray-50' }}">
                    @foreach($this->columns as $key)
                    <div class="table-cell px-6 py-2 whitespace-no-wrap text-sm leading-5 text-gray-900">
                        @if($result->$key === 'check-circle')
                        <x-icons.check-circle class="text-green-600 mx-auto" />
                        @elseif($result->$key === 'x-circle')
                        <x-icons.x-circle class="text-red-300 mx-auto" />
                        @else
                        {!! $result->$key !!}
                        @endif
                    </div>
                    @endforeach
                </div>
                @endforeach
            </div>
        </div>
        <div class="rounded-lg rounded-t-none max-w-screen rounded-lg border-b border-gray-200 bg-white">
            <div class="p-2 flex items-center justify-between">
                <div class="flex items-center">
                    <select name="perPage" class="mt-1 form-select block w-full pl-3 pr-10 py-2 text-base leading-6 border-gray-300 focus:outline-none focus:shadow-outline-blue focus:border-blue-300 sm:text-sm sm:leading-5" wire:model="perPage">
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                        <option value="99999999">All</option>
                    </select>
                </div>

                <div>
                    <div class="flex lg:hidden justify-center">
                        <span class="flex items-center space-x-2">{{ $this->results->links('datatables::tailwind-simple-pagination') }}</span>
                    </div>

                    <div class="hidden lg:flex justify-center">
                        <span>{{ $this->results->links('datatables::tailwind-pagination') }}</span>
                    </div>
                </div>

                <div class="text-gray-600">
                    Results {{ $this->results->firstItem() }} - {{ $this->results->lastItem() }} of {{ $this->results->total() }}
                </div>
            </div>

        </div>
    </div>

    <div class="mt-4 p-4 rounded overflow-hidden align-middle min-w-full shadow sm:rounded-lg border-b border-gray-200 bg-white">
        <div class="h-6 flex justify-between items-center">
            <label class="uppercase tracking-wide text-blue-600 text-lg">Active Filters</label>
            <button wire:click="clearAllFilters" class="px-2 py-1 flex items-center uppercase tracking-wide border-2 border-red-600 text-red-600 rounded-full focus:outline-none text-xs space-x-2">
                <span>CLEAR ALL</span>
                <x-icons.x-circle />
            </button>
        </div>
        <div class="flex flex-wrap">
            @if(isset($dates['field']) && $dates['field'] !== '' && ((isset($dates['start']) && $dates['start'] !== '') ||
            (isset($dates['end']) && $dates['end'] !== '')))
            <button wire:click="clearDateFilter" class="mt-2 mr-2 px-2 py-1 flex items-center uppercase tracking-wide border-2 border-orange-400 text-orange-600 rounded-full focus:outline-none text-xs space-x-2">
                <span class="">{{ $this->getFieldColumn($dates['field']) . ' between ' . Carbon\Carbon::parse($dates['start'] ?? '2000-01-01')->format('d/m/Y') . ' and ' . Carbon\Carbon::parse($dates['end'] ?? now())->format('d/m/Y') }}</span>
                <x-icons.x-circle />
            </button>
            @endif
            @if(isset($times['field']) && $times['field'] !== '')
            <button wire:click="clearTimeFilter" class="mt-2 mr-2 px-2 py-1 flex items-center uppercase tracking-wide border-2 border-orange-400 text-orange-600 rounded-full focus:outline-none text-xs space-x-2">
                <span class="">{{ $this->getFieldColumn($times['field'])  . ' between ' . ($times['start'] ?? '00:00') . ' and ' . ($times['end'] ?? '23:59') }}</span>
                <x-icons.x-circle />
            </button>
            @endif
            @foreach($activeSelectFilters as $index => $activeSelectFilter)
            @foreach($activeSelectFilter as $key => $value)
            <button wire:click="removeSelectFilter('{{ $index }}', '{{ $key }}')" class="mt-2 mr-2 px-2 py-1 flex items-center uppercase tracking-wide border-2 border-orange-400 text-orange-600 rounded-full focus:outline-none text-xs space-x-2">
                <span>{{ $this->getFieldName($index) . ": " . $this->getDisplayValue($index, $value) }}</span>
                <x-icons.x-circle />
            </button>
            @endforeach
            @endforeach
            @foreach($activeBooleanFilters as $index => $activeBooleanFilter)
            <button wire:click=" removeBooleanFilter('{{ $index }}')" class="mt-2 mr-2 px-2 py-1 flex items-center uppercase tracking-wide border-2 border-orange-400 text-orange-600 rounded-full focus:outline-none text-xs space-x-2">
                <span class="">{{ $this->getFieldName($index) . ": " . ($activeBooleanFilter == 1 ? 'Yes' : 'No') }}</span>
                <x-icons.x-circle />
            </button>
            @endforeach
            @foreach($activeTextFilters as $index => $activeTextFilter)
            <button wire:click="removeTextFilter('{{ $index }}')" class="mt-2 mr-2 px-2 py-1 flex items-center uppercase tracking-wide border-2 border-orange-400 text-orange-600 rounded-full focus:outline-none text-xs space-x-2">
                <span class="">{{ $this->getFieldName($index) . ": " . $activeTextFilter }}</span>
                <x-icons.x-circle />
            </button>
            @endforeach
        </div>
    </div>

    @if(count($this->dateFilters))
    <div class="mt-4 p-4 rounded overflow-hidden align-middle min-w-full shadow sm:rounded-lg border-b border-gray-200 bg-white">
        <div class="h-6 flex justify-between items-center">
            <label class="uppercase tracking-wide text-blue-600 text-lg">Date Range</label>
            <button wire:click="clearDateFilter" class="@if(!isset($dates['field']) || $dates['field'] === '') hidden @endif px-2 py-1 flex items-center uppercase trackingborder-2 -wide orderg-red-600 text-6ed-100 rounded-full focus:outline-none text-xs space-x-2">
                <span>CLEAR</span>
                <x-icons.x-circle />
            </button>
        </div>
        <div class="mt-2 grid grid-cols-3 gap-4">
            <select name="dateField" wire:model="dates.field" class="w-full form-select">
                <option></option>
                @foreach($this->dateFilters as $index => $field)
                <option value="{{ $index }}">{{ $field['name'] }}</option>
                @endforeach
            </select>

            <input type="date" name="start" wire:model="dates.start" class="w-full form-input" />
            <input type="date" name='end' wire:model="dates.end" class="w-full form-input" />
        </div>
        <div class="mt-4 grid grid-cols-3 md:grid-cols-6 gap-2">
            <button class="px-3 py-2 rounded text-white text-xs focus:outline-none bg-blue-500 hover:bg-blue-800" wire:click="lastMonth">Last Month</button>
            <button class="px-3 py-2 rounded text-white text-xs focus:outline-none bg-blue-500 hover:bg-blue-800" wire:click="lastQuarter">Last Quarter</button>
            <button class="px-3 py-2 rounded text-white text-xs focus:outline-none bg-blue-500 hover:bg-blue-800" wire:click="lastYear">Last Year</button>
            <button class="px-3 py-2 rounded text-white text-xs focus:outline-none bg-blue-500 hover:bg-blue-800" wire:click="monthToToday">Month to today</button>
            <button class="px-3 py-2 rounded text-white text-xs focus:outline-none bg-blue-500 hover:bg-blue-800" wire:click="quarterToToday">Quarter to today</button>
            <button class="px-3 py-2 rounded text-white text-xs focus:outline-none bg-blue-500 hover:bg-blue-800" wire:click="yearToToday">Year to today</button>
        </div>
    </div>
    @endif

    @if(count($this->timeFilters))
    <div class="mt-4 p-4 rounded overflow-hidden align-middle min-w-full shadow sm:rounded-lg border-b border-gray-200 bg-white">
        <div class="h-6 flex justify-between items-center">
            <label class="uppercase tracking-wide text-blue-600 text-lg">Time Range</label>
            <button wire:click="clearTimeFilter" class="@if(!isset($times['field']) || $times['field'] === '') hidden @endif px-2 py-1 flex items-center uppercase trackingborder-2 -wide orderg-red-600 text-6ed-100 rounded-full focus:outline-none text-xs space-x-2">
                <span>CLEAR</span>
                <x-icons.x-circle />
            </button>
        </div>
        <div class="mt-2 grid grid-cols-3 gap-4">
            <select name="timeField" wire:model="times.field" class="w-full form-select">
                <option></option>
                @foreach($this->timeFilters as $index => $field)
                <option value="{{ $index }}">{{ $field['name']}}</option>
                @endforeach
            </select>
            <input type="time" name="start" wire:model="times.start" class="w-full form-input">
            <input type="time" name="end" wire:model="times.end" class="w-full form-input">
        </div>
    </div>
    @endif

    @if(count($this->selectFilters) || count($this->booleanFilters) || count($this->textFilters))
    <div class="mt-4 p-4 rounded overflow-hidden align-middle min-w-full shadow sm:rounded-lg border-b border-gray-200 bg-white">
        <div class="h-6 flex justify-between items-center">
            <label class="uppercase tracking-wide text-blue-600 text-lg">Filters</label>
            <button wire:click="clearDateFilter" class="@if(!isset($dates['field']) || $dates['field'] === '') hidden @endif px-2 py-1 flex items-center uppercase trackingborder-2 -wide orderg-red-600 text-6ed-100 rounded-full focus:outline-none text-xs space-x-2">
                <span>CLEAR</span>
                <x-icons.x-circle />
            </button>
        </div>

        <div class="mt-2 grid grid-cols-3 gap-4">
            @foreach($this->selectFilters as $i => $filter)
            <div class="w-full relative">
                <label class="uppercase tracking-wide text-gray-600 text-xs py-1 rounded flex justify-between" for="{{ $filter['name'] }}">
                    {{ ucwords(str_replace('_', ' ', str_replace('_id', '', $filter['name']))) }}
                </label>
                <div class="">
                    <select name="{{ $filter['name'] }}" class="w-full form-select" wire:input="doSelectFilter('{{ $i }}', $event.target.value)">
                        <option value=""></option>
                        @foreach($filter['selectFilter'] as $value => $label)
                        @if(is_object($label))
                        <option value="{{ $label->id }}">{{ $label->name }}</option>
                        @elseif(is_array($label))
                        <option value="{{ $label['id'] }}">{{ $label['name'] }}</option>
                        @elseif(is_numeric($value))
                        <option value="{{ $label }}">{{ $label }}</option>
                        @else
                        <option value="{{ $value }}">{{ $label }}</option>
                        @endif
                        @endforeach
                    </select>
                </div>
            </div>
            @endforeach

            @foreach($this->booleanFilters as $i => $filter)
            <div class="w-full relative">
                <label class="uppercase tracking-wide text-gray-600 text-xs py-1 rounded flex justify-between" for="{{ $filter['name'] }}">
                    {{ ucwords(str_replace('_', ' ', str_replace('_id', '', $filter['name']))) }}
                </label>
                <div class="relative">
                    <select name="{{ $filter['name'] }}" class="w-full form-select" wire:input="doBooleanFilter('{{ $i }}', $event.target.value)">
                        <option value=""></option>
                        <option value="0">No</option>
                        <option value="1">Yes</option>
                    </select>
                </div>
            </div>
            @endforeach

            @foreach($this->textFilters as $i => $filter)
            <div class="w-full relative">
                <label class="uppercase tracking-wide text-gray-600 text-xs py-1 rounded flex justify-between" for="{{ $filter['name'] }}">
                    <span>{{ ucwords(str_replace('_', ' ', $filter['name'])) }}</span>
                </label>
                <div class="relative">
                    <input name="{{ $filter['name'] }}" type="text" class="w-full form-input" wire:change="doTextFilter('{{ $i }}', $event.target.value)" />
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif


    <div wire:loading>
        <div class="fixed z-50 bottom-0 inset-x-0 px-4 pb-4 sm:inset-0 sm:flex sm:items-center sm:justify-center">
            <div x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 transition-opacity">
                <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
            </div>
            <x-icons.cog class="h-36 w-36 spinner" />
        </div>
    </div>
</div>
