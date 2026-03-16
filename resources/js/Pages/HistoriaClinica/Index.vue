<template>
  <AppLayout>
    <div class="space-y-6">
      <PageBreadcrumb :page-title="title" :items="[{ label: 'Historia clínica', url: null }]" />
      <Alert v-if="flash.success" variant="success" :message="flash.success" />
      <Alert v-else-if="flash.error" variant="amber" :message="flash.error" />

      <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div></div>
        <Link
          href="/historia-clinica/crear"
          class="inline-flex items-center justify-center gap-2 rounded-xl bg-brand-500 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-brand-500/25 transition hover:bg-brand-600"
        >
          <Icon icon="mdi:plus" class="h-5 w-5" />
          Nueva historia clínica
        </Link>
      </div>

      <Card title="Buscar" desc="Buscar por nombre, apellidos o DNI.">
        <form @submit.prevent="submitSearch" class="flex flex-col gap-3 sm:flex-row sm:items-center">
          <FormInput
            id="buscar"
            v-model="buscar"
            type="search"
            label="Buscar por nombre, apellidos o DNI"
            label-class="sr-only"
            placeholder="Buscar por nombre, apellidos o DNI..."
            wrapper-class="flex-1 min-w-0"
            @update:model-value="onBuscarInput"
          />
          <Button type="submit" variant="outline" start-icon="mdi:magnify">Buscar</Button>
          <Link v-if="buscar.trim()" href="/historia-clinica" class="inline-flex items-center justify-center gap-2 rounded-xl border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-800">
            Limpiar
          </Link>
        </form>
      </Card>

      <Card no-header no-padding class="overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full text-left text-sm">
            <thead>
              <tr class="border-b border-gray-200 bg-gray-50/80 dark:border-gray-700 dark:bg-gray-800/80">
                <th class="px-5 py-4 font-semibold text-gray-700 dark:text-gray-200">Paciente</th>
                <th class="px-5 py-4 font-semibold text-gray-700 dark:text-gray-200">DNI</th>
                <th class="px-5 py-4 font-semibold text-gray-700 dark:text-gray-200">Contacto</th>
                <th class="px-5 py-4 font-semibold text-gray-700 dark:text-gray-200">Registro</th>
                <th class="px-5 py-4 text-right font-semibold text-gray-700 dark:text-gray-200">Acciones</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
              <tr v-for="p in pacientes.data" :key="p.id" class="transition hover:bg-gray-50/50 dark:hover:bg-white/[0.02]">
                <td class="px-5 py-4">
                  <span class="font-medium text-gray-900 dark:text-white">{{ p.nombres }} {{ p.apellidos }}</span>
                </td>
                <td class="px-5 py-4 text-gray-600 dark:text-gray-300">{{ p.dni || '—' }}</td>
                <td class="px-5 py-4 text-gray-600 dark:text-gray-300">{{ p.celular || p.email || '—' }}</td>
                <td class="px-5 py-4 text-gray-500 dark:text-gray-400">{{ formatDate(p.created_at) }}</td>
                <td class="px-5 py-4 text-right">
                  <div class="flex items-center justify-end gap-2">
                    <Link :href="'/ver-historia-clinica?id=' + p.id" class="rounded-lg px-3 py-1.5 font-medium text-brand-600 hover:bg-brand-50 dark:text-brand-400 dark:hover:bg-brand-500/10">Ver</Link>
                    <a :href="'/descargar-historia-clinica-pdf?id=' + p.id" class="rounded-lg px-3 py-1.5 font-medium text-gray-600 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-800">PDF</a>
                    <Link :href="'/editar-historia-clinica?id=' + p.id" class="rounded-lg px-3 py-1.5 font-medium text-gray-600 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-800">Editar</Link>
                    <Button type="button" variant="outlineDanger" size="sm" className="!px-3 !py-1.5 !text-sm" @click="confirmDelete(p)">Eliminar</Button>
                  </div>
                </td>
              </tr>
              <tr v-if="!pacientes.data || pacientes.data.length === 0">
                <td colspan="5" class="px-5 py-12 text-center">
                  <p class="text-gray-500 dark:text-gray-400">No hay pacientes registrados.</p>
                  <Link href="/historia-clinica/crear" class="mt-3 inline-block font-medium text-brand-500 hover:underline">Crear primera historia clínica</Link>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        <PaginationLinks
          v-if="pacientes.total > 0"
          :links="pacientes.links"
          :from="pacientes.from"
          :to="pacientes.to"
          :total="pacientes.total"
          item-label="paciente(s)"
        />
      </Card>
    </div>
  </AppLayout>
</template>

<script setup>
import { computed, ref, watch } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import AppLayout from '../Layouts/AppLayout.vue';
import PageBreadcrumb from '@/components/PageBreadcrumb.vue';
import PaginationLinks from '@/components/PaginationLinks.vue';
import { Alert, Card, Button } from '@/components/ui';
import { FormInput } from '@/components/form';
import { Icon } from '@iconify/vue';

const props = defineProps({
  title: { type: String, default: 'Historia clínica' },
  pacientes: { type: Object, required: true },
  buscar: { type: String, default: '' },
});

const page = usePage();
const flash = computed(() => page.props.flash || {});
const buscar = ref(props.buscar);

watch(() => props.buscar, (val) => { buscar.value = val ?? ''; });

let debounceTimer = null;
function onBuscarInput() {
  clearTimeout(debounceTimer);
  debounceTimer = setTimeout(() => {
    router.get('/historia-clinica', { buscar: (buscar.value || '').trim() }, { preserveState: true });
  }, 400);
}

function submitSearch() {
  clearTimeout(debounceTimer);
  router.get('/historia-clinica', { buscar: buscar.value }, { preserveState: true });
}

function formatDate(val) {
  if (!val) return '—';
  return new Date(val).toLocaleDateString('es-PE', { day: '2-digit', month: '2-digit', year: 'numeric' });
}

function confirmDelete(p) {
  if (!confirm('¿Eliminar toda la historia clínica de este paciente? Se borrarán sus consultas y exámenes.')) return;
  router.post('/eliminar-historia-clinica', { id: p.id, _token: page.props.csrf_token ?? '' });
}
</script>
