<template>
  <AppLayout>
    <div class="space-y-5">
      <PageBreadcrumb :page-title="title" :items="[{ label: 'Dashboard', url: null }]" />
      <div class="mb-6">
        <p class="text-sm text-gray-600 dark:text-gray-400">
          Noticias de salud y medicina a nivel mundial (GNews, NewsAPI o fuentes RSS públicas).
        </p>
      </div>

      <template v-if="!articles || articles.length === 0 || filteredArticles.length === 0">
        <div class="rounded-2xl border border-gray-200 bg-white p-8 text-center dark:border-gray-800 dark:bg-white/[0.03]">
          <p class="mb-2 text-gray-600 dark:text-gray-400">
            No se pudieron cargar noticias en este momento.
          </p>
          <p class="mx-auto max-w-lg text-sm text-gray-500 dark:text-gray-500">
            Para más fuentes, añade en <code class="rounded bg-gray-100 px-1.5 py-0.5 dark:bg-gray-800">.env</code>:
            <code class="rounded bg-gray-100 px-1.5 py-0.5 dark:bg-gray-800">GNEWS_API_TOKEN=tu_clave</code>
            (clave gratuita en
            <a href="https://gnews.io/register" target="_blank" rel="noopener" class="text-brand-500 hover:underline">gnews.io</a>).
            NewsAPI en plan gratis solo funciona desde localhost.
          </p>
        </div>
      </template>

      <div v-else class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <article
          v-for="(article, idx) in filteredArticles"
          :key="idx"
          class="overflow-hidden rounded-2xl border border-gray-200 bg-white transition-shadow hover:shadow-md dark:border-gray-800 dark:bg-white/[0.03]"
        >
          <a
            v-if="article.urlToImage"
            :href="article.url || '#'"
            target="_blank"
            rel="noopener"
            class="block aspect-video overflow-hidden bg-gray-100 dark:bg-gray-800"
          >
            <img
              :src="article.urlToImage"
              alt=""
              class="h-full w-full object-cover"
              loading="lazy"
            />
          </a>
          <div class="p-4">
            <h3 class="mb-1 line-clamp-2 font-semibold text-gray-900 dark:text-white">
              <a
                :href="article.url || '#'"
                target="_blank"
                rel="noopener"
                class="hover:text-brand-500"
              >
                {{ article.title }}
              </a>
            </h3>
            <p v-if="article.description" class="mb-2 line-clamp-2 text-sm text-gray-600 dark:text-gray-400">
              {{ article.description }}
            </p>
            <div class="flex items-center justify-between text-xs text-gray-500 dark:text-gray-500">
              <span v-if="article.source?.name">{{ article.source.name }}</span>
              <time v-if="article.publishedAt" :datetime="article.publishedAt">
                {{ formatDate(article.publishedAt) }}
              </time>
            </div>
          </div>
        </article>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { computed } from 'vue';
import AppLayout from './Layouts/AppLayout.vue';
import PageBreadcrumb from '../components/PageBreadcrumb.vue';

const props = defineProps({
  title: { type: String, default: 'Dashboard' },
  articles: { type: Array, default: () => [] },
});

const filteredArticles = computed(() =>
  (props.articles || []).filter((a) => a.title && a.title !== '[Removed]')
);

function formatDate(iso) {
  if (!iso) return '';
  try {
    const d = new Date(iso);
    const now = new Date();
    const diff = (now - d) / 1000;
    if (diff < 60) return 'hace un momento';
    if (diff < 3600) return `hace ${Math.floor(diff / 60)} min`;
    if (diff < 86400) return `hace ${Math.floor(diff / 3600)} h`;
    if (diff < 604800) return `hace ${Math.floor(diff / 86400)} días`;
    return d.toLocaleDateString('es-PE', { day: 'numeric', month: 'short', year: 'numeric' });
  } catch {
    return iso;
  }
}
</script>
