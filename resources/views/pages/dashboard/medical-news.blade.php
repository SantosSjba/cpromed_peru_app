@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb :pageTitle="$title" />
    <div class="mb-6">
        <p class="text-gray-600 dark:text-gray-400 text-sm">
            Noticias de salud y medicina a nivel mundial (GNews, NewsAPI o fuentes RSS públicas).
        </p>
    </div>

    @if(empty($articles))
        <div class="rounded-2xl border border-gray-200 bg-white p-8 text-center dark:border-gray-800 dark:bg-white/[0.03]">
            <p class="text-gray-600 dark:text-gray-400 mb-2">
                No se pudieron cargar noticias en este momento.
            </p>
            <p class="text-gray-500 dark:text-gray-500 text-sm max-w-lg mx-auto">
                Para más fuentes, añade en <code class="px-1.5 py-0.5 rounded bg-gray-100 dark:bg-gray-800">.env</code>: <code class="px-1.5 py-0.5 rounded bg-gray-100 dark:bg-gray-800">GNEWS_API_TOKEN=tu_clave</code> (clave gratuita en <a href="https://gnews.io/register" target="_blank" rel="noopener" class="text-brand-500 hover:underline">gnews.io</a>). NewsAPI en plan gratis solo funciona desde localhost.
            </p>
        </div>
    @else
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach($articles as $article)
                @if(!empty($article['title']) && $article['title'] !== '[Removed]')
                    <article class="rounded-2xl border border-gray-200 bg-white overflow-hidden dark:border-gray-800 dark:bg-white/[0.03] hover:shadow-md transition-shadow">
                        @if(!empty($article['urlToImage']))
                            <a href="{{ $article['url'] ?? '#' }}" target="_blank" rel="noopener" class="block aspect-video overflow-hidden bg-gray-100 dark:bg-gray-800">
                                <img src="{{ $article['urlToImage'] }}" alt="" class="w-full h-full object-cover" loading="lazy" />
                            </a>
                        @endif
                        <div class="p-4">
                            <h3 class="font-semibold text-gray-900 dark:text-white line-clamp-2 mb-1">
                                <a href="{{ $article['url'] ?? '#' }}" target="_blank" rel="noopener" class="hover:text-brand-500">
                                    {{ $article['title'] }}
                                </a>
                            </h3>
                            @if(!empty($article['description']))
                                <p class="text-sm text-gray-600 dark:text-gray-400 line-clamp-2 mb-2">
                                    {{ $article['description'] }}
                                </p>
                            @endif
                            <div class="flex items-center justify-between text-xs text-gray-500 dark:text-gray-500">
                                @if(!empty($article['source']['name']))
                                    <span>{{ $article['source']['name'] }}</span>
                                @endif
                                @if(!empty($article['publishedAt']))
                                    <time datetime="{{ $article['publishedAt'] }}">
                                        {{ \Carbon\Carbon::parse($article['publishedAt'])->diffForHumans() }}
                                    </time>
                                @endif
                            </div>
                        </div>
                    </article>
                @endif
            @endforeach
        </div>
    @endif
@endsection
