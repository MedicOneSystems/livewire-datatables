<div>
@isset($value)
    <x-icons.check-circle class="text-green-600 mx-auto" />
@else
    <x-icons.x-circle class="text-red-300 mx-auto" />
@endisset
</div>