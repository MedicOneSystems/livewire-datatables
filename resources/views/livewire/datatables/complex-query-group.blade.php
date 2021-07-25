<div class="space-y-4">
    @foreach($rules as $index => $rule)
        @php
            $key = $parentIndex !== null
                ? $parentIndex . '.' . $index
                : $index;
        @endphp

        <div>
            @if($rule['type'] === 'rule')
                @include('datatables::complex-query-rule', ['parentIndex' => $key, 'rule' => $rule])
            @elseif($rule['type'] === 'group')
                <div class="p-4 bg-gray-{{ strlen($parentIndex) }}00 rounded-lg space-y-8 text-gray-{{ strlen($parentIndex) > 4 ? 1 : 9 }}00 {{ strlen($parentIndex) > 9 ? 'border-2 border-gray-100' : '' }}">
                    <select class="form-select" wire:model="rules.{{ collect(explode('.', $key))->join(".content.") }}.logic">
                        <option value="and">AND</option>
                        <option value="or">OR</option>
                    </select>

                    @include('datatables::complex-query-group', [
                        'parentIndex' => $key,
                        'rules' => $rule['content'],
                        'logic' => $rule['logic']
                    ])

                    <span class="flex justify-between">
                        <span class="flex space-x-4">
                            <button wire:click="addRule('{{ collect(explode('.', $key))->join(".content.") . ".content" }}')" class="px-3 py-2 rounded bg-blue-200 text-blue-900">ADD RULE</button>
                            <button wire:click="addGroup('{{ collect(explode('.', $key))->join(".content.") . ".content" }}')" class="px-3 py-2 rounded bg-blue-200 text-blue-900">ADD GROUP</button>
                        </span>
                        <button wire:click="removeRule('{{ collect(explode('.', $key))->join(".content.") . ".content" }}')" class="px-3 py-2 rounded bg-red-600 text-white"><x-icons.trash /></button>
                    </span>
                </div>
            @endif
        </div>
    @endforeach
</div>
