<div class="relative">
    <div class="flex justify-between items-center">
        <div class="flex-grow">
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
        </div>

        <div>
            <x-icons.cog wire:loading class="h-9 w-9 spinner text-gray-400" />
        </div>
    </div>
    <div class="rounded-lg shadow-lg bg-white">
        <div
            class="rounded-lg @unless($this->hidePagination) rounded-b-none @endif max-w-screen overflow-x-scroll bg-white">
            <div class="table align-middle min-w-full">
                @unless($this->hideHeader)
                <div class="table-row divide-x divide-gray-200">
                    @foreach($this->columns as $index => $column)

                    <div wire:click.prefetch="toggle('{{ $index }}')"
                        class="@if($column['hidden']) relative table-cell h-12 w-3 bg-blue-100 hover:bg-blue-300 overflow-none align-top group @else hidden @endif"
                        style="min-width:12px; max-width:12px">
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
                    <div
                        class="@if($column['hidden']) hidden @else relative table-cell h-12 overflow-hidden align-top @endif">
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
</div>