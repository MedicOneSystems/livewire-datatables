<div class="flex justify-between">
<!-- Previous Page Link -->
@if ($paginator->onFirstPage())
<div class="w-32 flex justify-between items-center relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm leading-5 font-medium rounded-md text-gray-400 bg-gray-50">
    <x-icons.arrow-left />
    Previous
</div>
@else
<a class="w-32 flex justify-between items-center relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm leading-5 font-medium rounded-md text-gray-700 bg-white hover:text-gray-500 focus:outline-none focus:shadow-outline-blue focus:border-blue-300 active:bg-gray-100 active:text-gray-700 transition ease-in-out duration-150" href="{{ $paginator->previousPageUrl() }}" rel="prev">
    <x-icons.arrow-left />
    Previous
</a>
@endif


<!-- Next Page pnk -->
@if ($paginator->hasMorePages())
<a class="w-32 flex justify-between items-center relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm leading-5 font-medium rounded-md text-gray-700 bg-white hover:text-gray-500 focus:outline-none focus:shadow-outline-blue focus:border-blue-300 active:bg-gray-100 active:text-gray-700 transition ease-in-out duration-150" href="{{ $paginator->nextPageUrl() }}" rel="next">
    Next
    <x-icons.arrow-right />
</a>
@else
<div class="w-32 flex justify-between items-center relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm leading-5 font-medium rounded-md text-gray-400 bg-gray-50">
    Next
    <x-icons.arrow-right class="inline" />
</div>
@endif
</div>
