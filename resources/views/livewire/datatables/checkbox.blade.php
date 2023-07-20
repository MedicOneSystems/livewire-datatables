<div class="flex justify-center">
    <input
        type="checkbox"
        wire:model.live="selected"
        value="{{ $value }}"
        @if (property_exists($this, 'pinnedRecords') && in_array($value, $this->pinnedRecords)) checked @endif
        class="w-4 h-4 mt-1 text-blue-600 form-checkbox transition duration-150 ease-in-out"
    />
</div>
