
<div>
    @php $key = collect(explode('.', $parentIndex))->join(".content.") . ".content" @endphp
    <div class="flex space-x-4 items-end">
        <div class="flex flex-grow space-x-4">
            <div class="w-1/3">
                <label class="block uppercase tracking-wide text-xs font-bold py-1 rounded flex justify-between">Column</label>
                <div class="relative">
                    <select
                        wire:model="rules.{{ $key }}.column"
                        name="selectedColumn"
                        class="form-select w-full"
                    >
                        <option value=""></option>
                        @foreach($columns as $i => $column)
                            <option value="{{ $i }}">{{ $column['label'] }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

                @if($options = $this->getOperands($key))
            <div class="w-1/3">
                <label class="block uppercase tracking-wide text-xs font-bold py-1 rounded flex justify-between">Operand</label>
                <div class="relative">
                    <select
                        name="operand"

                        wire:model="rules.{{ $key }}.operand"
                        class="form-select w-full"
                    >
                        <option selected></option>
                        @foreach($options as $operand)
                        <option value="{{ $operand }}">{{ $operand }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
                @endif
            <div class="w-1/3">
                @if($column = $this->getRuleColumn($key))
                    <label class="block uppercase tracking-wide text-xs font-bold py-1 rounded flex justify-between">Value</label>
                    <div class="relative">
                        @if(is_array($column['filterable']) && in_array($rule['content']['operand'], [
                            'equals',
                            'does not equal',
                            'is empty',
                            'is not empty',
                            'includes',
                            'does not include'
                        ]))
                            <select name="value" wire:model="rules.{{ $key }}.value" class="form-select w-full">
                                <option selected></option>
                                @foreach($column['filterable'] as $value => $label)
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
                        @elseif($column['type'] === 'boolean')
                            <select name="value" wire:model="rules.{{ $key }}.value" class="form-select w-full">
                                <option selected></option>
                                <option value="true">True</option>
                                <option value="false">False</option>
                            </select>
                        @else
                            <input name="value" wire:model.lazy="rules.{{ $key }}.value" class="form-input w-full" />
                        @endif
                    </div>
                @endif
            </div>
        </div>
        <button wire:click="removeRule('{{ $key }}')" class="mb-px w-9 h-9 flex items-center justify-center rounded text-red-600"><x-icons.trash /></button>
    </div>
</div>
