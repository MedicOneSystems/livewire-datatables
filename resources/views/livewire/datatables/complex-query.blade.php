<div class="bg-gray-50 px-4 py-4 rounded-lg rounded-t-none shadow-lg">
    <div class="my-4 text-xl uppercase tracking-wide font-medium leading-none">Query Builder</div>
    <div>
        @include('datatables::complex-query-group', ['rules' => $rules, 'parentIndex' => null])
    </div>

    @if(count($this->rules[0]['content']))
        @if($errors->any())
            <div class="mt-4 text-red-500">You have missing values in your rules</div>
        @else
            <button class="mt-4 bg-red-600 px-3 py-2 rounded text-white" wire:click="runQuery">Apply Query</button>
        @endif

        <pre class="my-4 px-4 py-2 bg-gray-800 text-yellow-200 rounded-sm">{{ $this->rulesString }}</pre>
    @endif
</div>
