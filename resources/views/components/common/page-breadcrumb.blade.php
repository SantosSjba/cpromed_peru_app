@php
    $trail = $trail ?? [];
    $lastItem = collect($trail)->last();
    $pageTitleSafe = $lastItem['label'] ?? $pageTitle ?? 'Page';
@endphp
<div class="mb-6 flex flex-wrap items-center justify-between gap-3">
    <h2 class="text-xl font-semibold text-gray-800 dark:text-white/90">
        {{ $pageTitleSafe }}
    </h2>
    <nav aria-label="Breadcrumb">
        <ol class="flex flex-wrap items-center gap-1.5 text-sm">
            @foreach ($trail as $index => $item)
                @if ($index > 0)
                    <li class="text-gray-400 dark:text-gray-500" aria-hidden="true">/</li>
                @endif
                <li>
                    @if (!empty($item['url']))
                        <a href="{{ $item['url'] }}" class="text-gray-500 hover:text-brand-600 dark:text-gray-400 dark:hover:text-brand-400">
                            {{ $item['label'] }}
                        </a>
                    @else
                        <span class="font-medium text-gray-800 dark:text-white/90">{{ $item['label'] }}</span>
                    @endif
                </li>
            @endforeach
        </ol>
    </nav>
</div>
