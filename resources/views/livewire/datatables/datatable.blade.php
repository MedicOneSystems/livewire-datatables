<div>
    @if($beforeTableSlot)
        <div class="mt-8">
            @include($beforeTableSlot)
        </div>
    @endif
    <div class="relative">
        <div class="flex justify-between items-center mb-1">
            <div class="h-10 flex items-center">
                @if($this->searchableColumns()->count())
                <div class="w-96 flex rounded-lg shadow-sm">
                    <div class="relative flex-grow focus-within:z-10">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" viewBox="0 0 20 20" stroke="currentColor" fill="none">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                        <input wire:model.debounce.500ms="search" class="w-full pl-10 py-3 text-sm leading-4 block rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 focus:outline-none" placeholder="{{__('Search in')}} {{ $this->searchableColumns()->map->label->join(', ') }}" type="text" />
                        <div class="absolute inset-y-0 right-0 pr-2 flex items-center">
                            <button wire:click="$set('search', null)" class="text-gray-300 hover:text-red-600 focus:outline-none">
                                <x-icons.x-circle class="h-5 w-5 stroke-current" />
                            </button>
                        </div>
                    </div>
                </div>
                @endif
            </div>

            @if($this->activeFilters)
                <span class="text-xl text-blue-400 uppercase">FILTERS ACTIVE</span>
            @endif

            <div class="flex flex-wrap items-center space-x-1">
                <x-icons.cog wire:loading class="h-9 w-9 animate-spin text-gray-400" />

                @if($this->activeFilters)
                <button wire:click="clearAllFilters" class="flex items-center space-x-2 px-3 border border-red-400 rounded-md bg-white text-red-500 text-xs leading-4 font-medium uppercase tracking-wider hover:bg-red-200 focus:outline-none"><span>{{ __('Reset') }}</span>
                    <x-icons.x-circle class="m-2" />
                </button>
                @endif

                @if($exportable)
                <div x-data="{ init() {
                    window.livewire.on('startDownload', link => window.open(link, '_blank'))
                } }" x-init="init">
                    <button wire:click="export" class="flex items-center space-x-2 px-3 border border-green-400 rounded-md bg-white text-green-500 text-xs leading-4 font-medium uppercase tracking-wider hover:bg-green-200 focus:outline-none"><span>{{ __('Export') }}</span>
                        <x-icons.excel class="m-2" /></button>
                </div>
                @endif

                @if($hideable === 'select')
                @include('datatables::hide-column-multiselect')
                @endif

                @foreach ($columnGroups as $name => $group)
                    <button wire:click="toggleGroup('{{ $name }}')"
                        class="px-3 py-2 border border-green-400 rounded-md bg-white text-green-500 text-xs leading-4 font-medium uppercase tracking-wider hover:bg-green-200 focus:outline-none">
                        <span class="flex items-center h-5">{{ isset($this->groupLabels[$name]) ? __($this->groupLabels[$name]) : __('Toggle :group', ['group' => $name]) }}</span>
                    </button>
                @endforeach
            </div>
        </div>

        @if($hideable === 'buttons')
        <div class="p-2 grid grid-cols-8 gap-2">
            @foreach($this->columns as $index => $column)
                @if ($column['hideable'])
                    <button wire:click.prefetch="toggle('{{ $index }}')" class="px-3 py-2 rounded text-white text-xs focus:outline-none
                    {{ $column['hidden'] ? 'bg-blue-100 hover:bg-blue-300 text-blue-600' : 'bg-blue-500 hover:bg-blue-800' }}">
                        {{ $column['label'] }}
                    </button>
            @endif
            @endforeach
        </div>
        @endif

        <div wire:loading.class="opacity-50" class="rounded-lg @unless($complex || $this->hidePagination) rounded-b-none @endunless shadow-lg bg-white max-w-screen overflow-x-scroll border-4 @if($this->activeFilters) border-blue-500 @else border-transparent @endif @if($complex) rounded-b-none border-b-0 @endif">
            <div>
                <div class="table align-middle min-w-full">
                    @unless($this->hideHeader)
                    <div class="table-row divide-x divide-gray-200">
                        @foreach($this->columns as $index => $column)
                            @if($hideable === 'inline')
                                @include('datatables::header-inline-hide', ['column' => $column, 'sort' => $sort])
                            @elseif($column['type'] === 'checkbox')
                                @unless($column['hidden'])
                                    <div class=" table-cell h-12 w-32 py-4 flex justify-center overflow-hidden align-top px-6 border-b border-gray-200 bg-gray-50 text-left text-xs leading-4 font-medium text-gray-500 uppercase tracking-wider focus:outline-none">
                                        <div class="px-3 py-1 rounded @if(count($selected)) bg-orange-400 @else bg-gray-200 @endif text-white text-center">
                                            {{ count($selected) }}
                                        </div>
                                    </div>
                                @endunless
                            @else
                                @include('datatables::header-no-hide', ['column' => $column, 'sort' => $sort])
                            @endif
                        @endforeach
                    </div>

                    <div class="table-row divide-x divide-blue-200 bg-blue-100">
                        @foreach($this->columns as $index => $column)
                            @if($column['hidden'])
                                @if($hideable === 'inline')
                                    <div class="table-cell w-5 overflow-hidden align-top bg-blue-100"></div>
                                @endif
                            @elseif($column['type'] === 'checkbox')
                                <div class="overflow-hidden align-top bg-blue-100 px-6 py-5 border-b border-gray-200 text-left text-xs leading-4 font-medium text-gray-500 uppercase tracking-wider flex h-full flex-col items-center space-y-2 focus:outline-none">
                                    <div>SELECT ALL</div>
                                    <div>
                                        <input type="checkbox" wire:click="toggleSelectAll" @if(count($selected) === $this->results->total()) checked @endif class="form-checkbox mt-1 h-4 w-4 text-blue-600 transition duration-150 ease-in-out" />
                                    </div>
                                </div>
                            @elseif($column['type'] === 'label')
                                <div class="table-cell overflow-hidden align-top">
                                    {{ $column['label'] ?? '' }}
                                </div>
                            @else
                                <div class="table-cell overflow-hidden align-top">
                                    @isset($column['filterable'])
                                        @if( is_iterable($column['filterable']) )
                                            <div wire:key="{{ $index }}">
                                                @include('datatables::filters.select', ['index' => $index, 'name' => $column['label'], 'options' => $column['filterable']])
                                            </div>
                                        @else
                                            <div wire:key="{{ $index }}">
                                                @include('datatables::filters.' . ($column['filterView'] ?? $column['type']), ['index' => $index, 'name' => $column['label']])
                                            </div>
                                        @endif
                                    @endisset
                                </div>
                            @endif
                        @endforeach
                    </div>
                    @endif
                    @forelse($this->results as $row)
                        <div class="table-row p-1 {{ $this->rowClasses($row, $loop) }}">
                            @foreach($this->columns as $column)
                                @if($column['hidden'])
                                    @if($hideable === 'inline')
                                    <div class="table-cell w-5 overflow-hidden align-top"></div>
                                    @endif
                                @elseif($column['type'] === 'checkbox')
                                    @include('datatables::checkbox', ['value' => $row->checkbox_attribute])
                                @elseif($column['type'] === 'label')
                                    @include('datatables::label')
                                @else
                                    <div class="table-cell px-6 py-2 whitespace-no-wrap @if($column['align'] === 'right') text-right @elseif($column['align'] === 'center') text-center @else text-left @endif {{ $this->cellClasses($row, $column) }}">
                                        {!! $row->{$column['name']} !!}
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    @empty
                        <p class="p-3 text-lg text-teal-600">
                            {{ __("There's Nothing to show at the moment") }}
                        </p>
                    @endforelse
                </div>
            </div>
        </div>

        @unless($this->hidePagination)
            <div class="max-w-screen bg-white @unless($complex) rounded-b-lg @endunless border-4 border-t-0 border-b-0 @if($this->activeFilters) border-blue-500 @else border-transparent @endif">
                <div class="p-2 sm:flex items-center justify-between">
                    {{-- check if there is any data --}}
                    @if(count($this->results))
                        <div class="my-2 sm:my-0 flex items-center">
                            <select name="perPage" class="mt-1 form-select block w-full pl-3 pr-10 py-2 text-base leading-6 border-gray-300 focus:outline-none focus:shadow-outline-blue focus:border-blue-300 sm:text-sm sm:leading-5" wire:model="perPage">
                                @foreach(config('livewire-datatables.per_page_options', [ 10, 25, 50, 100 ]) as $per_page_option)
                                    <option value="{{ $per_page_option }}">{{ $per_page_option }}</option>
                                @endforeach
                                <option value="99999999">{{__('All')}}</option>
                            </select>
                        </div>

                        <div class="my-4 sm:my-0">
                            <div class="lg:hidden">
                                <span class="space-x-2">{{ $this->results->links('datatables::tailwind-simple-pagination') }}</span>
                            </div>

                            <div class="hidden lg:flex justify-center">
                                <span>{{ $this->results->links('datatables::tailwind-pagination') }}</span>
                            </div>
                        </div>

                        <div class="flex justify-end text-gray-600">
                            {{__('Results')}} {{ $this->results->firstItem() }} - {{ $this->results->lastItem() }} {{__('of')}}
                            {{ $this->results->total() }}
                        </div>
                    @endif
                </div>
            </div>
        @endif
    </div>

    @if($complex)
        <div class="bg-gray-50 px-4 py-4 rounded-b-lg rounded-t-none shadow-lg border-4 @if($this->activeFilters) border-blue-500 @else border-transparent @endif @if($complex) border-t-0 @endif">
            <livewire:complex-query :columns="$this->complexColumns" :persistKey="$this->persistKey" :savedQueries="method_exists($this, 'getSavedQueries') ? $this->getSavedQueries() : null" />
        </div>
    @endif

    @if($afterTableSlot)
    <div class="mt-8">
        @include($afterTableSlot)
    </div>
    @endif
    <span class="hidden text-sm leading-5 text-gray-900 text-left text-center text-right bg-gray-50 bg-gray-100 bg-yellow-100"></span>
</div>
