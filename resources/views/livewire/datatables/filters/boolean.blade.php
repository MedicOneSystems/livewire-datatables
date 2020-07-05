<div class="flex">
    <select name="{{ $name }}" class="m-1 text-sm leading-4 flex-grow form-select" wire:input="doBooleanFilter('{{ $index }}', $event.target.value)">
        <option value=""></option>
        <option value="0">No</option>
        <option value="1">Yes</option>
    </select>
</div>