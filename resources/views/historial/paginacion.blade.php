@if ($paginator->hasPages())
<nav style="display:flex; gap:6px; align-items:center;">
    {{-- Anterior --}}
    @if ($paginator->onFirstPage())
        <span class="pag-btn pag-disabled">&#8592;</span>
    @else
        <a href="{{ $paginator->previousPageUrl() }}" class="pag-btn">&#8592;</a>
    @endif

    {{-- Páginas --}}
    @foreach ($elements as $element)
        @if (is_string($element))
            <span class="pag-btn pag-disabled">{{ $element }}</span>
        @endif
        @if (is_array($element))
            @foreach ($element as $page => $url)
                @if ($page == $paginator->currentPage())
                    <span class="pag-btn pag-activo">{{ $page }}</span>
                @else
                    <a href="{{ $url }}" class="pag-btn">{{ $page }}</a>
                @endif
            @endforeach
        @endif
    @endforeach

    {{-- Siguiente --}}
    @if ($paginator->hasMorePages())
        <a href="{{ $paginator->nextPageUrl() }}" class="pag-btn">&#8594;</a>
    @else
        <span class="pag-btn pag-disabled">&#8594;</span>
    @endif
</nav>

<style>
    .pag-btn {
        display: inline-flex; align-items: center; justify-content: center;
        width: 34px; height: 34px; border-radius: 7px;
        font-size: 13px; font-weight: 500; text-decoration: none;
        color: #3a4255; border: 1px solid #D1DCF0;
        background: #fff; transition: background 0.15s;
    }
    .pag-btn:hover   { background: #E8F0FB; color: #1A4FA8; }
    .pag-activo      { background: #1A4FA8 !important; color: #fff !important; border-color: #1A4FA8; }
    .pag-disabled    { color: #ccc; cursor: default; }
</style>
@endif