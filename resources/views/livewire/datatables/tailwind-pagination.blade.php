<div class="pagination flex rounded border border-gray-300 overflow-hidden divide-x divide-gray-300">
    <!-- Previous Page Link -->
    @if ($paginator->onFirstPage())
    <button class="relative inline-flex items-center px-2 py-2 bg-white text-sm leading-5 font-medium text-gray-500"
        disabled>
        <span>&laquo;</span>
    </button>
    @else
    <button wire:click="previousPage"
        class="relative inline-flex items-center px-2 py-2 bg-white text-sm leading-5 font-medium text-gray-500 hover:text-gray-400 focus:z-10 focus:outline-none focus:border-blue-300 focus:shadow-outline-blue active:bg-gray-100 active:text-gray-500 transition ease-in-out duration-150">
        <span>&laquo;</span>
    </button>
    @endif

    <div class="divide-x divide-gray-300">
        @foreach ($elements as $element)
        @if (is_string($element))
        <button class="-ml-px relative inline-flex items-center px-4 py-2 bg-white text-sm leading-5 font-medium text-gray-700" disabled>
            <span>{{ $element }}</span>
        </button>
        @endif

        <!-- Array Of Links -->

        @if (is_array($element))
        @foreach ($element as $page => $url)
        <button wire:click="gotoPage({{ $page }})"
                class="-mx-1 relative inline-flex items-center px-4 py-2 text-sm leading-5 font-medium text-gray-700 hover:text-gray-500 focus:z-10 focus:outline-none focus:border-blue-300 focus:shadow-outline-blue active:bg-gray-100 active:text-gray-700 transition ease-in-out duration-150 {{ $page === $paginator->currentPage() ? 'bg-gray-200' : 'bg-white' }}">
            {{ $page }}
            </button>
        @endforeach
        @endif
        @endforeach
    </div>

    <!-- Next Page Link -->
    @if ($paginator->hasMorePages())
    <button wire:click="nextPage"
        class="-ml-px relative inline-flex items-center px-2 py-2 bg-white text-sm leading-5 font-medium text-gray-500 hover:text-gray-400 focus:z-10 focus:outline-none focus:border-blue-300 focus:shadow-outline-blue active:bg-gray-100 active:text-gray-500 transition ease-in-out duration-150">
        <span>&raquo;</span>
    </button>
    @else
    <button
        class="-ml-px relative inline-flex items-center px-2 py-2 bg-white text-sm leading-5 font-medium text-gray-500 "
        disabled><span>&raquo;</span></button>
    @endif
</div>
