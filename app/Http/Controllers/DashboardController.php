<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Dashboard con noticias de salud/medicina.
     * Orden: 1) GNews (servidor), 2) NewsAPI (localhost), 3) RSS público (sin clave).
     */
    public function index(): View
    {
        $articles = $this->fetchMedicalNews();

        return view('pages.dashboard.medical-news', [
            'title' => 'Dashboard',
            'articles' => $articles,
        ]);
    }

    private function fetchMedicalNews(): array
    {
        $articles = $this->fetchFromGNews();
        if (!empty($articles)) {
            return $articles;
        }

        $articles = $this->fetchFromNewsApi();
        if (!empty($articles)) {
            return $articles;
        }

        return $this->fetchFromRss();
    }

    /** GNews: funciona desde servidor, 100 req/día gratis en gnews.io */
    private function fetchFromGNews(): array
    {
        $token = config('services.gnews.token');
        if (empty($token)) {
            return [];
        }

        try {
            $response = Http::timeout(12)->get('https://gnews.io/api/v4/top-headlines', [
                'topic' => 'health',
                'max' => 24,
                'lang' => 'es',
                'country' => 'es',
                'token' => $token,
            ]);

            if (!$response->successful()) {
                return [];
            }

            $data = $response->json();
            $items = $data['articles'] ?? [];
            $out = [];
            foreach ($items as $a) {
                if (empty($a['title'])) {
                    continue;
                }
                $out[] = [
                    'title' => $a['title'],
                    'description' => $a['description'] ?? '',
                    'url' => $a['url'] ?? '#',
                    'urlToImage' => $a['image'] ?? null,
                    'publishedAt' => $a['publishedAt'] ?? null,
                    'source' => ['name' => $a['source']['name'] ?? ''],
                ];
            }
            return $out;
        } catch (\Throwable $e) {
            report($e);
            return [];
        }
    }

    /** NewsAPI: en plan gratis solo permite peticiones desde localhost (no desde servidor) */
    private function fetchFromNewsApi(): array
    {
        $apiKey = config('services.newsapi.key');
        if (empty($apiKey)) {
            return [];
        }

        try {
            $response = Http::timeout(10)->get('https://newsapi.org/v2/top-headlines', [
                'category' => 'health',
                'language' => 'es',
                'country' => 'es',
                'pageSize' => 24,
                'apiKey' => $apiKey,
            ]);

            if (!$response->successful()) {
                return [];
            }

            $data = $response->json();
            $items = $data['articles'] ?? [];
            $out = [];
            foreach ($items as $a) {
                if (empty($a['title']) || ($a['title'] ?? '') === '[Removed]') {
                    continue;
                }
                $out[] = [
                    'title' => $a['title'],
                    'description' => $a['description'] ?? '',
                    'url' => $a['url'] ?? '#',
                    'urlToImage' => $a['urlToImage'] ?? null,
                    'publishedAt' => $a['publishedAt'] ?? null,
                    'source' => ['name' => $a['source']['name'] ?? ''],
                ];
            }
            return $out;
        } catch (\Throwable $e) {
            report($e);
            return [];
        }
    }

    /** RSS público en español: sin API key, fuentes de salud */
    private function fetchFromRss(): array
    {
        $feeds = [
            'https://www.paho.org/es/rss/todas-noticias',
            'https://www.who.int/es/news-room/releases/rss.xml',
            'https://www.who.int/news-room/releases/rss.xml',
            'https://www.cdc.gov/rss/news.xml',
        ];

        foreach ($feeds as $url) {
            try {
                $response = Http::timeout(8)->withHeaders(['User-Agent' => 'Laravel-HealthNews/1.0'])->get($url);
                if (!$response->successful()) {
                    continue;
                }
                $xml = @simplexml_load_string($response->body());
                if ($xml === false || !isset($xml->channel->item)) {
                    continue;
                }
                $out = [];
                $count = 0;
                foreach ($xml->channel->item as $item) {
                    if ($count >= 24) {
                        break;
                    }
                    $title = trim((string) ($item->title ?? ''));
                    if ($title === '') {
                        continue;
                    }
                    $out[] = [
                        'title' => $title,
                        'description' => trim((string) ($item->description ?? '')),
                        'url' => trim((string) ($item->link ?? '#')),
                        'urlToImage' => null,
                        'publishedAt' => isset($item->pubDate) ? date('Y-m-d\TH:i:s\Z', strtotime((string) $item->pubDate)) : null,
                        'source' => ['name' => trim((string) ($xml->channel->title ?? 'OMS / OPS'))],
                    ];
                    $count++;
                }
                if (!empty($out)) {
                    return $out;
                }
            } catch (\Throwable $e) {
                report($e);
                continue;
            }
        }

        return [];
    }
}
