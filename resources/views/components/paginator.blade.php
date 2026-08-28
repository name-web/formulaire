@if ($paginator->hasPages())
    <div class="pagination">
        {{-- Previous --}}
        @if ($paginator->onFirstPage())
            <span class="disabled"><i class="fa-solid fa-chevron-left"></i></span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}"><i class="fa-solid fa-chevron-left"></i></a>
        @endif

        {{-- Pages --}}
        @php
            $currentPage = $paginator->currentPage();
            $lastPage = $paginator->lastPage();
            $range = 2;
            $start = max(1, $currentPage - $range);
            $end = min($lastPage, $currentPage + $range);
        @endphp

        @if($start > 1)
            <a href="{{ $paginator->url(1) }}">1</a>
            @if($start > 2)
                <span class="disabled">...</span>
            @endif
        @endif

        @for($i = $start; $i <= $end; $i++)
            @if($i == $currentPage)
                <span class="active">{{ $i }}</span>
            @else
                <a href="{{ $paginator->url($i) }}">{{ $i }}</a>
            @endif
        @endfor

        @if($end < $lastPage)
            @if($end < $lastPage - 1)
                <span class="disabled">...</span>
            @endif
            <a href="{{ $paginator->url($lastPage) }}">{{ $lastPage }}</a>
        @endif

        {{-- Next --}}
        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}"><i class="fa-solid fa-chevron-right"></i></a>
        @else
            <span class="disabled"><i class="fa-solid fa-chevron-right"></i></span>
        @endif
    </div>
@endif
