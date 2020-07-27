<div class="relative">
    <div class="flex justify-between items-center mb-1">
        <div class="flex-grow h-10 flex items-center">
            @if($this->searchableColumns()->count())
            <div class="w-full sm:w-2/3 md:w-2/5 flex rounded-lg shadow-sm">
                <div class="relative flex-grow focus-within:z-10">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" viewBox="0 0 20 20" stroke="currentColor" fill="none">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <input wire:model.debounce.500ms="search"
                        class="form-input block bg-gray-50 focus:bg-white w-full rounded-md pl-10 transition ease-in-out duration-150 sm:text-sm sm:leading-5"
                        placeholder="Search in {{ $this->searchableColumns()->map->label->join(', ') }}" />
                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                        <button wire:click="$set('search', null)" class="text-gray-300 hover:text-red-600 focus:outline-none">
                            <x-icons.x-circle class="h-5 w-5 stroke-current" />
                        </button>
                    </div>
                </div>
            </div>
            @endif
        </div>

        <div class="flex items-center space-x-1">
            <x-icons.cog wire:loading class="h-9 w-9 spinner text-gray-400" />

            @if($exportable)
            <div x-data="{ init() {
                window.livewire.on('startDownload', link => window.open(link,'_blank'))
            } }" x-init="init">
                <button wire:click="export" class="flex items-center space-x-2 px-3 border border-green-400 rounded-md bg-white text-green-500 text-xs leading-4 font-medium uppercase tracking-wider hover:bg-green-200 focus:outline-none"><span>Export</span><x-icons.excel class="m-2" /></button>
            </div>
            @endif

            @if($hideable === 'select')
                @include('datatables::hide-column-multiselect')
            @endif
        </div>
    </div>

    @if($hideable === 'buttons')
    <div class="p-2 grid grid-cols-8 gap-2">
        @foreach($this->columns as $index => $column)
        <button wire:click.prefetch="toggle('{{ $index }}')" class="px-3 py-2 rounded text-white text-xs focus:outline-none
        {{ $column['hidden'] ? 'bg-blue-100 hover:bg-blue-300 text-blue-600' : 'bg-blue-500 hover:bg-blue-800' }}">
            {{ $column['label'] }}
        </button>
        @endforeach
    </div>
    @endif

    <div class="rounded-lg shadow-lg bg-white">
        <div
            class="rounded-lg @unless($this->hidePagination) rounded-b-none @endif max-w-screen overflow-x-scroll bg-white">
            <div class="table align-middle min-w-full">
                @unless($this->hideHeader)
                <div class="table-row divide-x divide-gray-200">
                    @foreach($this->columns as $index => $column)
                        @if($hideable === 'inline')
                            @include('datatables::header-inline-hide', ['column' => $column, 'sort' => $sort])
                        @else
                            @include('datatables::header-no-hide', ['column' => $column, 'sort' => $sort])
                        @endif
                    @endforeach
                </div>

                <div class="table-row divide-x divide-blue-200 bg-blue-100">
                    @foreach($this->columns as $index => $column)
                    @if($column['hidden'])
                    @if($hideable === 'inline')
                    <div class="table-cell w-5 overflow-hidden align-top bg-blue-100">
                    </div>
                    @endif
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
                    @foreach($this->columns as $column)
                    @if($column['hidden'])
                    @if($hideable === 'inline')
                    <div class="table-cell w-5 overflow-hidden align-top">
                    </div>
                    @endif
                    @else
                    <div class="table-cell px-6 py-2 whitespace-no-wrap text-sm leading-5 text-gray-900">
                        {!! $result->{$column['name']} !!}
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
</div>