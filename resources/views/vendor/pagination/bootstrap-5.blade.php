@if ($paginator->hasPages())
    <div class="row">
        <div class="col d-flex justify-content-center align-items-center">
            <nav class="">
                <ul class="pagination">
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

                    {{-- Page Number --}}
                    <li class="page-item active" aria-current="page" >
                        <span class="page-link mx-2">{{ $paginator->currentPage() }}</span>
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
    </div>
    
    <div class="col">
        <div class="col text-center">
            <p class="small text-muted">
                Showing
                <span class="">{{ $paginator->firstItem() }}</span>
                to
                <span class="">{{ $paginator->lastItem() }}</span>
                of
                <span class="">{{ $paginator->total() }}</span>
                results
            </p>
        </div>
    </div>
@endif
