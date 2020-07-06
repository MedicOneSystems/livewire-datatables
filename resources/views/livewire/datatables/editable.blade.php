<div x-data="{
    edit: false,
    edited: false,
    init() {
        window.livewire.on('fieldEdited', (id) => {
            if (id === '{{ $rowId }}') {
                this.edited = true
                setTimeout(() => {
                    this.edited = false
                }, 5000)
            }
        })
    }
}" x-init="init()" :key="{{ $rowId }}">
    <div x-on:edited.window></div>
    <span x-bind:class="{ 'text-green-600': edited }" x-show="!edit"
        x-on:click="edit = true; $refs.input.focus()">{{ $value }}</span>
    <span x-show="edit">
        <input class="bg-transparent" x-ref="input" value="{{ $value }}"
            wire:change="edited($event.target.value, '{{ $table }}', '{{ $column }}', '{{ $rowId }}')"
            x-on:click.away="edit = false" x-on:blur="edit = false" x-on:keydown.enter="edit = false" />
    </span>
</div>