<template>
  <AppLayout>
    <div class="space-y-5">
      <PageBreadcrumb :page-title="title" :items="[{ label: 'Ecommerce', url: null }]" />
      <div class="grid grid-cols-12 gap-4 md:gap-6">
        <div class="col-span-12 space-y-6 xl:col-span-7">
          <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 md:gap-6">
            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-6">
              <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-gray-100 dark:bg-gray-800">
                <Icon icon="mdi:account-group" class="h-6 w-6 text-gray-800 dark:text-white/90" />
              </div>
              <div class="mt-5 flex items-end justify-between">
                <div>
                  <span class="text-sm text-gray-500 dark:text-gray-400">Customers</span>
                  <h4 class="mt-2 text-title-sm font-bold text-gray-800 dark:text-white/90">—</h4>
                </div>
              </div>
            </div>
            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-6">
              <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-gray-100 dark:bg-gray-800">
                <Icon icon="mdi:cart" class="h-6 w-6 text-gray-800 dark:text-white/90" />
              </div>
              <div class="mt-5 flex items-end justify-between">
                <div>
                  <span class="text-sm text-gray-500 dark:text-gray-400">Orders</span>
                  <h4 class="mt-2 text-title-sm font-bold text-gray-800 dark:text-white/90">—</h4>
                </div>
              </div>
            </div>
          </div>
          <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white px-5 pt-5 dark:border-gray-800 dark:bg-white/[0.03] sm:px-6 sm:pt-6">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Ventas mensuales</h3>
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Gráficos y métricas en desarrollo.</p>
          </div>
        </div>
        <div class="col-span-12 xl:col-span-5">
          <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-6">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Objetivo mensual</h3>
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Contenido en desarrollo.</p>
          </div>
        </div>
        <div class="col-span-12">
          <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Estadísticas</h3>
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Gráficos en desarrollo.</p>
          </div>
        </div>
        <div class="col-span-12 xl:col-span-5">
          <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Demografía</h3>
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Contenido en desarrollo.</p>
          </div>
        </div>
        <div class="col-span-12 xl:col-span-7">
          <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Pedidos recientes</h3>
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Contenido en desarrollo.</p>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { Icon } from '@iconify/vue';
import AppLayout from './Layouts/AppLayout.vue';
import PageBreadcrumb from '../components/PageBreadcrumb.vue';

defineProps({
  title: { type: String, default: 'Ecommerce' },
});
</script>
