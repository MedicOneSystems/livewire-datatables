<div @if($persistKey) x-data="{
        rules: $persist('').as('{{ $persistKey }}'),
        init() {
            Livewire.on('complexQuery', rules => this.rules = rules)
            if (this.rules && this.rules !== '') {
                $wire.set('rules', this.rules)
                $wire.runQuery()
            }
        }
    }" @endif class="bg-gray-50 -mx-4 px-4 py-4 rounded-lg rounded-t-none shadow-lg"
>
    <div class="my-4 text-xl uppercase tracking-wide font-medium leading-none">Query Builder</div>

    @if(count($this->rules[0]['content']))
        <pre class="my-4 px-4 py-2 bg-gray-800 @if($errors->any())text-red-500 @else text-green-400 @endif rounded">{{ $this->rulesString }}@if($errors->any()) Invalid rules @endif</pre>
    @endif

    <div>@include('datatables::complex-query-group', ['rules' => $rules, 'parentIndex' => null])</div>

    @if(count($this->rules[0]['content']))
        @if($errors->any())
            <div class="mt-4 text-red-500">You have missing values in your rules</div>
        @else
            <div class="flex w-full justify-between">
                <button class="mt-4 bg-blue-500 px-3 py-2 rounded text-white" wire:click="runQuery">Apply Query</button>
                <button x-show="rules" class="mt-4 bg-red-600 px-3 py-2 rounded text-white" wire:click="resetQuery">Reset</button>
            </div>
        @endif
    @endif
</div>
