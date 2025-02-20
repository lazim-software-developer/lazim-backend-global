@php
    // Extract pagination variables from the response
    $pagination = $paginationData['pagination'];
    $currentPage = $pagination['current_page'] ?? 1;
    $perPage = $pagination['per_page'] ?? 10;
    $totalItems = $pagination['total'] ?? 0;
    $totalPages = $pagination['total_pages'] ?? 1;
@endphp

@if ($totalPages > 1)
    <div class="pagination-container" id="pagination-container">
        <ul class="pagination-list">
            {{-- Previous Button --}}
            @if ($currentPage > 1)
                <li class="page-item">
                    <a href="{{ url()->current() . '?page=' . ($currentPage - 1) }}" class="page-link">Previous</a>
                </li>
            @endif

            {{-- Show the first page link --}}
            @if ($currentPage > 3)
                <li class="page-item">
                    <a href="{{ url()->current() . '?page=1' }}" class="page-link">1</a>
                </li>
                <li class="page-item disabled"><span class="page-link">...</span></li>
            @endif

            {{-- Loop through the page numbers --}}
            @for ($i = max(1, $currentPage - 2); $i <= min($totalPages, $currentPage + 2); $i++)
                <li class="page-item {{ $currentPage == $i ? 'active' : '' }}">
                    <a href="{{ url()->current() . '?page=' . $i }}" class="page-link">{{ $i }}</a>
                </li>
            @endfor

            {{-- Show the last page link --}}
            @if ($currentPage < $totalPages - 2)
                <li class="page-item disabled"><span class="page-link">...</span></li>
                <li class="page-item">
                    <a href="{{ url()->current() . '?page=' . $totalPages }}" class="page-link">{{ $totalPages }}</a>
                </li>
            @endif

            {{-- Next Button --}}
            @if ($currentPage < $totalPages)
                <li class="page-item">
                    <a href="{{ url()->current() . '?page=' . ($currentPage + 1) }}" class="page-link">Next</a>
                </li>
            @endif
        </ul>
    </div>
@endif

<p class="pagination-info">Showing {{ ($currentPage - 1) * $perPage + 1 }} to
    {{ min($currentPage * $perPage, $totalItems) }} of
    {{ $totalItems }} items ({{ $perPage }} items per page).</p>
