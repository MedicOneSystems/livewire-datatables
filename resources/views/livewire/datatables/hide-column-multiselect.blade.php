<div x-data="{ show: false }" class="flex flex-col items-center">
    <div class="flex flex-col items-center relative">
        <button x-on:click="show = !show" class="px-3 py-2 border border-blue-400 rounded-md bg-white text-blue-500 text-xs leading-4 font-medium uppercase tracking-wider hover:bg-blue-200 focus:outline-none">
            <div class="flex items-center h-5">
                {{ __('Show / Hide Columns')}}
            </div>
        </button>
        <div x-show="show" x-on:click.away="show = false" class="z-50 absolute mt-16 -mr-4 shadow-2xl top-100 bg-white w-96 right-0 rounded max-h-select overflow-y-auto" x-cloak>
            <div class="flex flex-col w-full">
                @foreach($this->columns as $index => $column)
                <div>
                    <div class="@unless($column['hidden']) hidden @endif cursor-pointer w-full border-gray-800 border-b bg-gray-700 text-gray-500 hover:bg-blue-600 hover:text-white" wire:click="toggle({{$index}})">
                        <div class="relative flex w-full items-center p-2 group">
                            <div class=" w-full items-center flex">
                                <div class="mx-2 leading-6">{{ $column['label'] }}</div>
                            </div>
                            <div class="absolute inset-y-0 right-0 pr-2 flex items-center">
                                <x-icons.check-circle class="h-3 w-3 stroke-current text-gray-700" />
                            </div>
                        </div>
                    </div>
                    <div class="@if($column['hidden']) hidden @endif cursor-pointer w-full border-gray-800 border-b bg-gray-700 text-white hover:bg-red-600" wire:click="toggle({{$index}})">
                        <div class="relative flex w-full items-center p-2 group">
                            <div class=" w-full items-center flex">
                                <div class="mx-2 leading-6">{{ $column['label'] }}</div>
                            </div>
                            <div class="absolute inset-y-0 right-0 pr-2 flex items-center">
                                <x-icons.x-circle class="h-3 w-3 stroke-current text-gray-700" />
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

<style>
    .top-100 {
        top: 100%
    }

    .bottom-100 {
        bottom: 100%
    }

    .max-h-select {
        max-height: 300px;
    }

</style>
