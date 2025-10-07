<div>
    @if(filled($value))
        <div class="relative group">
            <x-icons.check-circle class="mx-auto text-green-600" />

            <div class="z-10 mt-2 px-2 py-1 rounded-lg bg-base-100 dark:bg-base-900 dark:text-primary-200 border border-{{ $colour ?? 'gray' }}-300 shadow-xl text-{{ $colour ?? 'gray' }}-700 text-left whitespace-normal absolute hidden group-hover:block shadow-xl">
                {{ $value }}
            </div>
        </div>
    @else
        <x-icons.x-circle class="mx-auto text-red-300" />
    @endif
</div>
