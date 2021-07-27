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
                <div drag-target
                    x-on:dragenter.prevent="dragenter"
                    x-on:dragleave.prevent="dragleave"
                    x-on:dragover.prevent
                    x-on:drop.stop="drop"
                    x-data="{
                        key: '{{ collect(explode('.', $key))->join(".content.") . ".content" }}',
                        source: () => document.querySelector('[dragging]'),
                        dragstart: (e, id) =>{
                            e.target.setAttribute('dragging', id)
                        },
                        dragend(e) {
                            e.target.removeAttribute('dragging')
                        },
                        dragenter(e) {},
                        dragleave(e) {},
                        drop(e) {
                          $wire.call('moveRule', this.source().getAttribute('dragging'), this.key)
                        },
                    }" class="p-4 space-y-4 bg-gray-{{ strlen($parentIndex) + 1 }}00 rounded-lg text-gray-{{ strlen($parentIndex) > 4 ? 1 : 9 }}00 border border-blue-400"
                >
                    <span class="flex space-x-4">
                        <button wire:click="addRule('{{ collect(explode('.', $key))->join(".content.") . ".content" }}')" class="px-3 py-2 rounded bg-blue-200 text-blue-900 hover:bg-blue-600 hover:text-blue-100">ADD RULE</button>
                        <button wire:click="addGroup('{{ collect(explode('.', $key))->join(".content.") . ".content" }}')" class="px-3 py-2 rounded bg-blue-200 text-blue-900 hover:bg-blue-600 hover:text-blue-100">ADD GROUP</button>
                    </span>
                    <div class="flex items-center">
                        @if(count($rule['content']) > 1)
                            <div class="mr-8">
                                <label class="block uppercase tracking-wide text-xs font-bold py-1 rounded flex justify-between">Logic</label>
                                <select
                                    wire:model="rules.{{ collect(explode('.', $key))->join(".content.") }}.logic"
                                    class="w-24 text-sm leading-4 block rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                                >
                                    <option value="and">AND</option>
                                    <option value="or">OR</option>
                                </select>
                            </div>
                        @endif

                        <div class="flex-grow">
                            @include('datatables::complex-query-group', [
                                'parentIndex' => $key,
                                'rules' => $rule['content'],
                                'logic' => $rule['logic']
                            ])
                        </div>
                    </div>

                    <div class="flex justify-end">

                        @unless($key === 0)
                            <button wire:click="removeRule('{{ collect(explode('.', $key))->join(".content.") . ".content" }}')" class="px-3 py-2 rounded bg-red-600 text-white"><x-icons.trash /></button>
                        @endunless
                    </div>
                </div>
            @endif
        </div>
    @endforeach
</div>
