@php
    $options = $options ?? [10, 25, 50, 100];
    $current = $current ?? (int) request('per_page', 10);
@endphp

<div class="btn-group">
    <button type="button" class="btn btn-sm btn-primary dropdown-toggle" data-bs-toggle="dropdown"
        aria-expanded="false">
        แถว: {{ $current }}
    </button>
    <ul class="dropdown-menu dropdown-menu-end">
        @foreach ($options as $n)
            <li>
                <a class="dropdown-item {{ $current == $n ? 'active' : '' }}"
                    href="{{ request()->fullUrlWithQuery(['per_page' => $n]) }}">{{ $n }} แถว</a>
            </li>
        @endforeach
    </ul>
</div>
