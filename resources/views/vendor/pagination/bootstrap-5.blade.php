<style>
    [dir="rtl"] .pagination-arrow { transform: scaleX(-1); }
</style>
@if ($paginator->hasPages())
    <nav class="d-flex justify-content-center mt-4" role="navigation" aria-label="Pagination Navigation">
        <ul class="pagination pagination-sm m-0 align-items-center">
            @if ($paginator->onFirstPage())
                <li class="page-item disabled" aria-disabled="true"><span class="page-link" style="border: 1px solid rgba(15, 61, 62, 0.1) !important; border-radius: 8px !important; background: var(--white) !important; color: #ccc !important; padding: 0.5rem 0.85rem;"><i class="fas fa-chevron-left pagination-arrow"></i></span></li>
            @else
                <li class="page-item"><a class="page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev" style="border: 1px solid rgba(15, 61, 62, 0.12) !important; border-radius: 8px !important; background: var(--white) !important; color: var(--secondary) !important; padding: 0.5rem 0.85rem; transition: all 0.2s;"><i class="fas fa-chevron-left pagination-arrow"></i></a></li>
            @endif

            @foreach ($elements as $element)
                @if (is_string($element))
                    <li class="page-item disabled" aria-disabled="true"><span class="page-link" style="border: none !important; background: transparent !important; color: var(--text-muted) !important;">{{ $element }}</span></li>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <li class="page-item active" aria-current="page"><span class="page-link" style="border: 1px solid var(--secondary, #0f3d3e) !important; border-radius: 8px !important; background-color: var(--secondary, #0f3d3e) !important; color: var(--white) !important; font-weight: 600; padding: 0.5rem 0.85rem; margin: 0 3px;">{{ $page }}</span></li>
                        @else
                            <li class="page-item"><a class="page-link" href="{{ $url }}" style="border: 1px solid rgba(15, 61, 62, 0.12) !important; border-radius: 8px !important; background-color: var(--white) !important; color: var(--secondary) !important; padding: 0.5rem 0.85rem; margin: 0 3px; transition: all 0.2s;" onmouseover="this.style.backgroundColor='rgba(15, 61, 62, 0.05)'" onmouseout="this.style.backgroundColor='var(--white)'">{{ $page }}</a></li>
                        @endif
                    @endforeach
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <li class="page-item"><a class="page-link" href="{{ $paginator->nextPageUrl() }}" rel="next" style="border: 1px solid rgba(15, 61, 62, 0.12) !important; border-radius: 8px !important; background: var(--white) !important; color: var(--secondary) !important; padding: 0.5rem 0.85rem; transition: all 0.2s;"><i class="fas fa-chevron-right pagination-arrow"></i></a></li>
            @else
                <li class="page-item disabled" aria-disabled="true"><span class="page-link" style="border: 1px solid rgba(15, 61, 62, 0.1) !important; border-radius: 8px !important; background: var(--white) !important; color: #ccc !important; padding: 0.5rem 0.85rem;"><i class="fas fa-chevron-right pagination-arrow"></i></span></li>
            @endif
        </ul>
    </nav>
@endif
