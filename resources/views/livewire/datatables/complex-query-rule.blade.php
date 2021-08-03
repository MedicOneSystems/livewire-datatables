<div class="w-full">
    @php $key = collect(explode('.', $parentIndex))->join(".content.") . ".content" @endphp
    <div
        draggable="true"
        x-on:dragstart="dragstart($event, '{{ $key }}')"
        x-on:dragend="dragend"
        key="{{ $key }}"
        class="px-3 py-2 -my-1 sm:flex space-x-4 items-end hover:bg-opacity-20 hover:bg-white hover:shadow-xl"
    >
        <div class="sm:flex flex-grow sm:space-x-4">
            <div class="sm:w-1/3">
                <label
                    class="block uppercase tracking-wide text-xs font-bold py-1 rounded flex justify-between">Column</label>
                <div class="relative">
                    <select wire:model="rules.{{ $key }}.column" name="selectedColumn"
                        class="w-full my-1 text-sm text-gray-900 leading-4 block rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                        <option value=""></option>
                        @foreach ($columns as $i => $column)
                            <option value="{{ $i }}">{{ Str::ucfirst($column['label']) }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            @if ($options = $this->getOperands($key))
                <div class="sm:w-1/3">
                    <label
                        class="block uppercase tracking-wide text-xs font-bold py-1 rounded flex justify-between">Operand</label>
                    <div class="relative">
                        <select name="operand" wire:model="rules.{{ $key }}.operand"
                            class="w-full my-1 text-sm text-gray-900 leading-4 block rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                            <option selected></option>
                            @foreach ($options as $operand)
                                <option value="{{ $operand }}">{{ $operand }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            @endif

            @if (!in_array($rule['content']['operand'], ['is empty', 'is not empty']))
                <div class="sm:w-1/3">
                    @if ($column = $this->getRuleColumn($key))
                        <label
                            class="block uppercase tracking-wide text-xs font-bold py-1 rounded flex justify-between">Value</label>
                        <div class="relative">
                            @if (is_array($column['filterable']))
                                <select name="value" wire:model="rules.{{ $key }}.value"
                                    class="w-full my-1 text-sm text-gray-900 leading-4 block rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                                    <option selected></option>
                                    @foreach ($column['filterable'] as $value => $label)
                                        @if (is_object($label))
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
                            @elseif($column['type'] === 'boolean')
                                <select name="value" wire:model="rules.{{ $key }}.value"
                                    class="w-full my-1 text-sm text-gray-900 leading-4 block rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                                    <option selected></option>
                                    <option value="true">True</option>
                                    <option value="false">False</option>
                                </select>
                            @elseif($column['type'] === 'date')
                                <input type="date" name="value" wire:model.lazy="rules.{{ $key }}.value"
                                    class="w-full px-3 py-2 border my-1 text-sm text-gray-900 leading-4 block rounded-md border-gray-300 shadow-sm focus:outline-none focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50" />
                            @elseif($column['type'] === 'time')
                                <input type="time" name="value" wire:model.lazy="rules.{{ $key }}.value"
                                    class="w-full px-3 py-2 border my-1 text-sm text-gray-900 leading-4 block rounded-md border-gray-300 shadow-sm focus:outline-none focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50" />
                            @else
                                <input name="value" wire:model.lazy="rules.{{ $key }}.value"
                                    class="w-full px-3 py-2 border my-1 text-sm text-gray-900 leading-4 block rounded-md border-gray-300 shadow-sm focus:outline-none focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50" />
                            @endif
                        </div>
                    @endif
                </div>
            @endif
        </div>
        <div class="flex justify-center sm:justify-end">
            <button wire:click="duplicateRule('{{ $key }}')"
                class="mb-px w-9 h-9 flex items-center justify-center rounded text-green-600 hover:text-green-400">
                <x-icons.copy />
            </button>
            <button wire:click="removeRule('{{ $key }}')"
                class="mb-px w-9 h-9 flex items-center justify-center rounded text-red-600 hover:text-red-400">
                <x-icons.trash />
            </button>
        </div>
    </div>
</div>
