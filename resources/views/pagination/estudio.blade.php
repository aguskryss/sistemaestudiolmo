@if ($paginator->hasPages())
    <nav class="flex items-center justify-between border-t border-linea pt-4 text-sm" aria-label="Paginación">
        <span class="font-mono text-xs text-gris">{{ $paginator->firstItem() }}–{{ $paginator->lastItem() }} de {{ $paginator->total() }}</span>
        <div class="flex items-center gap-1">
            @if ($paginator->onFirstPage())
                <span class="px-3 py-1.5 text-gris/50">←</span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="px-3 py-1.5 hover:bg-hueso" aria-label="Anterior">←</a>
            @endif

            @foreach ($elements as $element)
                @if (is_string($element))
                    <span class="px-2 text-gris">…</span>
                @endif
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span class="bg-tinta px-3 py-1.5 font-mono text-papel" aria-current="page">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}" class="px-3 py-1.5 font-mono hover:bg-hueso">{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="px-3 py-1.5 hover:bg-hueso" aria-label="Siguiente">→</a>
            @else
                <span class="px-3 py-1.5 text-gris/50">→</span>
            @endif
        </div>
    </nav>
@endif
