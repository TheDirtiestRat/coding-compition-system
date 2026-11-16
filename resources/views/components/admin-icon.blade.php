@props(['name'])

@php
    $paths = [
        'code' => 'M7 8l-4 4 4 4m10-8 4 4-4 4M14 4l-4 16',
        'overview' => 'M4 3h5v7H3V4a1 1 0 0 1 1-1Zm11 0h5a1 1 0 0 1 1 1v3h-6ZM3 16h6v5H4a1 1 0 0 1-1-1Zm12-3h6v7a1 1 0 0 1-1 1h-5Z',
        'calendar' => 'M8 3v4m8-4v4M4 11h16M6 5h12a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2Z',
        'users' => 'M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2m20 0v-2a4 4 0 0 0-3-3.87M9 3a4 4 0 1 0 0 8 4 4 0 0 0 0-8m8 .13a4 4 0 0 1 0 7.75',
        'flag' => 'M5 21V4m0 0c5-4 9 4 14 0v11c-5 4-9-4-14 0',
        'submission' => 'M14 3H6a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9Zm0 0v6h6M8 15l3 3 5-6',
        'timer' => 'M9 2h6m-3 0v3m6 2 2-2M12 9v5m0-9a8 8 0 1 0 0 16 8 8 0 0 0 0-16',
        'trophy' => 'M8 3h8v6a4 4 0 0 1-8 0Zm0 2H4v2a4 4 0 0 0 4 4m8-6h4v2a4 4 0 0 1-4 4m-4 2v5m-4 3h8m-6-3h4v3h-4Z',
        'sun' => 'M12 8a4 4 0 1 0 0 8 4 4 0 0 0 0-8M12 2v2m0 16v2M2 12h2m16 0h2M5 5l1.5 1.5m11 11L19 19M5 19l1.5-1.5m11-11L19 5',
        'moon' => 'M20.9 13a9 9 0 1 1-9.9-9.9 7 7 0 0 0 9.9 9.9Z',
        'logout' => 'M9 4H5a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h4m6-12 4 4-4 4m-8-4h12',
        'copy' => 'M9 9h10v11H9ZM5 15H4V4h10v1',
        'external' => 'M7 17 17 7M7 7h10v10',
    ];
@endphp

<svg {{ $attributes->class(['size-5 shrink-0']) }} viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
    <path d="{{ $paths[$name] }}" />
</svg>
