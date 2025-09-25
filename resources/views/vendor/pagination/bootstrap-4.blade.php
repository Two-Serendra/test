@if ($paginator->hasPages())
    <div class="d-flex justify-content-between align-items-center mt-2">

        {{-- Left side: Showing text --}}
        <div class="small text-muted">
            Showing
            <span>{{ $paginator->firstItem() }}</span>
            to
            <span>{{ $paginator->lastItem() }}</span>
            of
            <span>{{ $paginator->total() }}</span>
            results
        </div>

        {{-- Right side: Pagination --}}
        <nav>
            <ul class="pagination pagination-sm mb-0">
                {{-- Previous Page Link --}}
                @if ($paginator->onFirstPage())
                    <li class="page-item disabled" aria-disabled="true">
                        <span class="page-link">Prev</span>
                    </li>
                @else
                    <li class="page-item">
                        <a class="page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev">Prev</a>
                    </li>
                @endif

                {{-- Current Page --}}
                <li class="page-item active" aria-current="page">
                    <span class="page-link">{{ $paginator->currentPage() }}</span>
                </li>

                {{-- Next Page Link --}}
                @if ($paginator->hasMorePages())
                    <li class="page-item">
                        <a class="page-link" href="{{ $paginator->nextPageUrl() }}" rel="next">Next</a>
                    </li>
                @else
                    <li class="page-item disabled" aria-disabled="true">
                        <span class="page-link">Next</span>
                    </li>
                @endif
            </ul>
        </nav>
    </div>
@endif