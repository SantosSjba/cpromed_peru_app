<template>
  <div class="min-h-screen bg-gradient-to-br from-slate-50 via-white to-emerald-50/30 dark:from-gray-900 dark:via-gray-900 dark:to-emerald-950/20">
    <!-- Header compacto: logo + título (siempre visible, sin panel lateral) -->
    <header class="sticky top-0 z-10 border-b border-gray-200/80 bg-white/90 backdrop-blur-md dark:border-gray-700/80 dark:bg-gray-900/90">
      <div class="mx-auto flex max-w-5xl items-center justify-between gap-4 px-4 py-3 sm:px-6">
        <a href="/" class="flex items-center gap-3">
          <img src="/logo_cpromed.jpg" alt="CPROMED" class="h-10 w-10 rounded-lg object-cover sm:h-11 sm:w-11" />
          <span class="text-base font-semibold text-gray-800 dark:text-white sm:text-lg">VitaTrack</span>
        </a>
        <div class="text-right">
          <p class="text-sm font-medium text-gray-900 dark:text-white">{{ paciente.nombre }}</p>
          <p class="text-xs text-gray-500 dark:text-gray-400">Mi seguimiento</p>
        </div>
      </div>
    </header>

    <!-- Contenido principal: una sola columna centrada, ancho cómodo en escritorio -->
    <main class="mx-auto w-full max-w-5xl px-4 py-6 sm:px-6 sm:py-8 lg:px-8 lg:py-10">
      <div class="space-y-6 sm:space-y-8">
        <!-- Alertas -->
        <div v-if="flash.success" class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 shadow-sm dark:border-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-200">
          {{ flash.success }}
        </div>
        <div v-else-if="flash.error" class="rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 shadow-sm dark:border-red-800 dark:bg-red-900/30 dark:text-red-200">
          {{ flash.error }}
        </div>

        <!-- Resumen: 4 tarjetas responsive -->
        <section class="grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-4">
          <div class="rounded-2xl border border-gray-200/80 bg-white p-4 shadow-sm transition hover:shadow-md dark:border-gray-700/80 dark:bg-white/[0.03] dark:hover:bg-white/[0.05]">
            <p class="mb-1 text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Peso actual</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-white sm:text-3xl">
              {{ resumen.peso_actual }}<span class="text-sm font-normal text-gray-500"> kg</span>
            </p>
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Inicio: {{ config.peso_inicial }} kg</p>
          </div>
          <div class="rounded-2xl border border-gray-200/80 bg-white p-4 shadow-sm transition hover:shadow-md dark:border-gray-700/80 dark:bg-white/[0.03] dark:hover:bg-white/[0.05]">
            <p class="mb-1 text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">IMC actual</p>
            <p class="text-2xl font-bold sm:text-3xl" :class="imcTextColor(resumen.imc_color)">{{ resumen.imc_actual }}</p>
            <span class="mt-1 inline-block rounded-full px-2.5 py-0.5 text-xs font-semibold" :class="imcBadgeClass(resumen.imc_color)">
              {{ resumen.imc_categoria }}
            </span>
          </div>
          <div class="rounded-2xl border border-gray-200/80 bg-white p-4 shadow-sm transition hover:shadow-md dark:border-gray-700/80 dark:bg-white/[0.03] dark:hover:bg-white/[0.05]">
            <p class="mb-1 text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Pérdida total</p>
            <p class="text-2xl font-bold sm:text-3xl" :class="resumen.kg_perdidos > 0 ? 'text-emerald-600 dark:text-emerald-400' : resumen.kg_perdidos < 0 ? 'text-red-500' : 'text-gray-600 dark:text-gray-400'">
              {{ resumen.kg_perdidos > 0 ? '-' : resumen.kg_perdidos < 0 ? '+' : '' }}{{ Math.abs(resumen.kg_perdidos) }}<span class="text-sm font-normal text-gray-500"> kg</span>
            </p>
          </div>
          <div class="rounded-2xl border border-gray-200/80 bg-white p-4 shadow-sm transition hover:shadow-md dark:border-gray-700/80 dark:bg-white/[0.03] dark:hover:bg-white/[0.05]">
            <p class="mb-1 text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Hacia la meta</p>
            <p v-if="resumen.progreso_pct !== null" class="text-2xl font-bold text-emerald-600 dark:text-emerald-400 sm:text-3xl">{{ resumen.progreso_pct }}%</p>
            <p v-else class="text-sm text-gray-400 dark:text-gray-500">Sin meta definida</p>
          </div>
        </section>

        <!-- Barra de progreso -->
        <section v-if="resumen.progreso_pct !== null" class="rounded-2xl border border-gray-200/80 bg-white px-5 py-4 shadow-sm dark:border-gray-700/80 dark:bg-white/[0.03] sm:px-6">
          <div class="mb-2 flex items-center justify-between text-sm">
            <span class="font-medium text-gray-700 dark:text-gray-300">Progreso hacia la meta</span>
            <span class="font-bold text-emerald-600 dark:text-emerald-400">{{ Math.min(resumen.progreso_pct, 100) }}%</span>
          </div>
          <div class="h-3 w-full overflow-hidden rounded-full bg-gray-200 dark:bg-gray-700">
            <div
              class="h-full rounded-full bg-gradient-to-r from-emerald-400 to-emerald-600 shadow-sm transition-all duration-500 dark:from-emerald-500 dark:to-emerald-600"
              :style="{ width: Math.min(resumen.progreso_pct, 100) + '%' }"
            />
          </div>
        </section>

        <!-- Registrar mi peso -->
        <section class="rounded-2xl border border-gray-200/80 bg-white p-5 shadow-sm dark:border-gray-700/80 dark:bg-white/[0.03] sm:p-6">
          <h2 class="mb-4 flex items-center gap-2 text-base font-semibold text-gray-900 dark:text-white sm:text-lg">
            <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-emerald-100 dark:bg-emerald-900/40">
              <Icon icon="mdi:plus-circle-outline" class="h-5 w-5 text-emerald-600 dark:text-emerald-400" />
            </span>
            Registrar mi peso
          </h2>
          <form @submit.prevent="submitRegistro" class="space-y-4">
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
              <FormInput v-model="formRegistro.fecha" type="date" label="Fecha *" required />
              <FormInput v-model.number="formRegistro.peso" type="number" step="0.1" min="1" max="500" label="Peso (kg) *" placeholder="Ej: 90.5" required />
              <div class="sm:col-span-2 lg:col-span-1">
                <FormInput v-model="formRegistro.notas" type="text" label="Notas (opcional)" placeholder="Ej: Medido en ayunas" :maxlength="500" />
              </div>
            </div>
            <button
              type="submit"
              :disabled="submitting"
              class="inline-flex items-center gap-2 rounded-xl bg-emerald-500 px-6 py-3 text-sm font-semibold text-white shadow-lg shadow-emerald-500/25 transition hover:bg-emerald-600 focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 disabled:opacity-50 dark:focus:ring-offset-gray-900"
            >
              <Icon icon="mdi:check" class="h-5 w-5" />
              {{ submitting ? 'Guardando…' : 'Guardar registro' }}
            </button>
          </form>
        </section>

        <!-- Tabla de progreso -->
        <section class="overflow-hidden rounded-2xl border border-gray-200/80 bg-white shadow-sm dark:border-gray-700/80 dark:bg-white/[0.03]">
          <div class="border-b border-gray-200 px-4 py-4 dark:border-gray-700 sm:px-6">
            <h2 class="flex items-center gap-2 text-base font-semibold text-gray-900 dark:text-white sm:text-lg">
              <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-emerald-100 dark:bg-emerald-900/40">
                <Icon icon="mdi:chart-timeline-variant" class="h-5 w-5 text-emerald-600 dark:text-emerald-400" />
              </span>
              Mi progreso
            </h2>
          </div>
          <div v-if="tabla.length === 0" class="py-16 text-center">
            <Icon icon="mdi:scale-off" class="mx-auto mb-3 h-12 w-12 text-gray-300 dark:text-gray-600" />
            <p class="text-sm text-gray-600 dark:text-gray-400">Aún no hay registros. Puedes agregar el primero arriba.</p>
          </div>
          <div v-else class="overflow-x-auto">
            <table class="w-full min-w-[520px] text-left text-sm">
              <thead>
                <tr class="border-b border-gray-100 bg-gray-50/80 text-xs font-semibold uppercase tracking-wide text-gray-600 dark:border-gray-700 dark:bg-gray-800/80 dark:text-gray-400">
                  <th class="px-4 py-3.5 sm:px-5">#</th>
                  <th class="px-4 py-3.5 sm:px-5">Fecha</th>
                  <th class="px-4 py-3.5 sm:px-5">Peso (kg)</th>
                  <th class="px-4 py-3.5 sm:px-5">% Cambio</th>
                  <th class="px-4 py-3.5 sm:px-5">IMC</th>
                  <th class="px-4 py-3.5 sm:px-5">Categoría</th>
                  <th class="px-4 py-3.5 sm:px-5">Notas</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                <tr
                  v-for="r in tablaOrdenada"
                  :key="r.id"
                  class="transition hover:bg-gray-50/50 dark:hover:bg-white/[0.02]"
                >
                  <td class="px-4 py-3.5 text-gray-400 dark:text-gray-500 sm:px-5">{{ r.semana }}</td>
                  <td class="px-4 py-3.5 font-medium text-gray-700 dark:text-gray-200 sm:px-5">{{ formatFecha(r.fecha) }}</td>
                  <td class="px-4 py-3.5 font-bold text-gray-900 dark:text-white sm:px-5">{{ r.peso }}</td>
                  <td class="px-4 py-3.5 font-medium sm:px-5" :class="pctColor(r.pct_cambio)">
                    <span v-if="r.pct_cambio !== 0">{{ r.pct_cambio > 0 ? '+' : '' }}{{ r.pct_cambio }}%</span>
                    <span v-else class="text-gray-400">—</span>
                  </td>
                  <td class="px-4 py-3.5 font-semibold sm:px-5" :class="imcTextColor(r.imc_color)">{{ r.imc }}</td>
                  <td class="px-4 py-3.5 sm:px-5">
                    <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-semibold" :class="imcBadgeClass(r.imc_color)">
                      {{ r.imc_cat }}
                    </span>
                  </td>
                  <td class="px-4 py-3.5 text-xs text-gray-500 dark:text-gray-400 sm:px-5">{{ r.notas || '—' }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </section>
      </div>
    </main>

    <!-- Pie discreto -->
    <footer class="border-t border-gray-200/60 py-4 dark:border-gray-700/60">
      <p class="text-center text-xs text-gray-500 dark:text-gray-400">Sistema de gestión Cepromed Perú · VitaTrack</p>
    </footer>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { usePage, router } from '@inertiajs/vue3';
import { FormInput } from '@/components/form';
import { Icon } from '@iconify/vue';

const props = defineProps({
  title:   { type: String, default: 'Mi seguimiento' },
  token:   { type: String, required: true },
  config:  { type: Object, required: true },
  paciente: { type: Object, required: true },
  tabla:   { type: Array, default: () => [] },
  resumen: { type: Object, required: true },
});

const page  = usePage();
const flash = computed(() => page.props.flash ?? {});

const tablaOrdenada = computed(() => [...props.tabla].reverse());

const formRegistro = ref({
  fecha: new Date().toISOString().split('T')[0],
  peso:  null,
  notas: '',
});
const submitting = ref(false);

function submitRegistro() {
  submitting.value = true;
  router.post(`/seguimiento/${props.token}/registro`, {
    fecha: formRegistro.value.fecha,
    peso:  formRegistro.value.peso,
    notas: formRegistro.value.notas || '',
  }, {
    onSuccess: () => {
      formRegistro.value.peso  = null;
      formRegistro.value.notas = '';
    },
    onFinish: () => { submitting.value = false; },
  });
}

function imcTextColor(color) {
  const m = { green: 'text-emerald-600', yellow: 'text-yellow-600', orange: 'text-orange-600', red: 'text-red-600', blue: 'text-blue-600' };
  return m[color] || 'text-gray-600';
}
function imcBadgeClass(color) {
  const m = {
    green:  'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400',
    yellow: 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400',
    orange: 'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400',
    red:    'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
    blue:   'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
  };
  return m[color] || 'bg-gray-100 text-gray-700';
}
function pctColor(pct) {
  if (pct < 0) return 'text-emerald-600 dark:text-emerald-400';
  if (pct > 0) return 'text-red-500 dark:text-red-400';
  return 'text-gray-400';
}
function formatFecha(fecha) {
  if (!fecha) return '—';
  const [y, m, d] = fecha.split('-');
  return `${d}/${m}/${y}`;
}
</script>
