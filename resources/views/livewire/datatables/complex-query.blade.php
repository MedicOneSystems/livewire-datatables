<div class="mt-2 bg-gray-50 px-4 py-4 rounded-lg shadow-lg">
    <div>
        @include('datatables::complex-query-group', ['rules' => $rules, 'parentIndex' => null])
    </div>

    @if(count($this->rules[0]['content']))
        <pre>{{ $this->rulesString }}</pre>
        <button class="bg-red-600 px-3 py-2 rounded text-white" wire:click="runQuery">Apply Query</button>
    @endif
</div>
