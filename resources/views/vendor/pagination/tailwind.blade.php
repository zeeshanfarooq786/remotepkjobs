@if ($paginator->hasPages())
    <nav role="navigation" aria-label="{{ __('Pagination Navigation') }}" class="flex items-center justify-between">
        <div class="flex flex-1 justify-between sm:hidden">
            @if ($paginator->onFirstPage())
                <span class="pagination-disabled relative inline-flex items-center rounded-lg px-4 py-2 text-sm font-medium">Previous</span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" class="pagination-link relative inline-flex items-center rounded-lg px-4 py-2 text-sm font-medium">Previous</a>
            @endif

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" class="pagination-link relative ml-3 inline-flex items-center rounded-lg px-4 py-2 text-sm font-medium">Next</a>
            @else
                <span class="pagination-disabled relative ml-3 inline-flex items-center rounded-lg px-4 py-2 text-sm font-medium">Next</span>
            @endif
        </div>

        <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
            <div>
                <p class="pagination-summary text-sm">
                    Showing
                    <strong>{{ $paginator->firstItem() }}</strong>
                    to
                    <strong>{{ $paginator->lastItem() }}</strong>
                    of
                    <strong>{{ $paginator->total() }}</strong>
                    results
                </p>
            </div>

            <div>
                <span class="relative z-0 inline-flex rounded-lg" style="box-shadow: var(--color-card-shadow);">
                    @if ($paginator->onFirstPage())
                        <span aria-disabled="true" class="pagination-disabled relative inline-flex items-center rounded-l-lg px-2 py-2 text-sm font-medium">&lsaquo;</span>
                    @else
                        <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="pagination-link relative inline-flex items-center rounded-l-lg px-2 py-2 text-sm font-medium">&lsaquo;</a>
                    @endif

                    @foreach ($elements as $element)
                        @if (is_string($element))
                            <span aria-disabled="true" class="pagination-disabled relative inline-flex items-center border-l-0 px-4 py-2 text-sm font-medium">{{ $element }}</span>
                        @endif

                        @if (is_array($element))
                            @foreach ($element as $page => $url)
                                @if ($page == $paginator->currentPage())
                                    <span aria-current="page" class="pagination-active relative inline-flex items-center px-4 py-2 text-sm font-medium">{{ $page }}</span>
                                @else
                                    <a href="{{ $url }}" class="pagination-link relative inline-flex items-center border-l-0 px-4 py-2 text-sm font-medium" aria-label="{{ __('Go to page :page', ['page' => $page]) }}">{{ $page }}</a>
                                @endif
                            @endforeach
                        @endif
                    @endforeach

                    @if ($paginator->hasMorePages())
                        <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="pagination-link relative inline-flex items-center rounded-r-lg px-2 py-2 text-sm font-medium">&rsaquo;</a>
                    @else
                        <span aria-disabled="true" class="pagination-disabled relative inline-flex items-center rounded-r-lg px-2 py-2 text-sm font-medium">&rsaquo;</span>
                    @endif
                </span>
            </div>
        </div>
    </nav>
@endif
