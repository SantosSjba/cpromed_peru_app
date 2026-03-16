<template>
  <AppLayout>
    <div class="space-y-6">
      <PageBreadcrumb
        :page-title="title"
        :items="[{ label: 'VitaTrack', url: '/control-peso' }, { label: paciente.nombre, url: null }]"
      />

      <Alert v-if="flash.success" variant="success" :message="flash.success" />
      <Alert v-else-if="flash.error" variant="error" :message="flash.error" />

      <Card no-header>
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
          <div class="flex items-center gap-4">
            <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-emerald-500 shadow-lg shadow-emerald-500/25">
              <Icon icon="mdi:account-heart" class="h-7 w-7 text-white" />
            </div>
            <div>
              <h2 class="text-xl font-bold text-gray-900 dark:text-white">{{ paciente.nombre }}</h2>
              <p class="text-sm text-gray-500 dark:text-gray-400">
                <span v-if="paciente.dni">DNI {{ paciente.dni }}</span>
                <span v-if="paciente.dni && paciente.edad"> · </span>
                <span v-if="paciente.edad">{{ paciente.edad }} años</span>
                <span v-if="(paciente.dni || paciente.edad) && paciente.genero"> · </span>
                <span v-if="paciente.genero" class="capitalize">{{ paciente.genero }}</span>
              </p>
            </div>
          </div>
          <div class="flex gap-2">
            <Button type="button" variant="outline" start-icon="mdi:cog" @click="modalConfigura = true">Configurar</Button>
            <Button type="button" variant="outlineDanger" start-icon="mdi:delete-outline" @click="confirmarEliminarSeguimiento">Eliminar</Button>
          </div>
        </div>
      </Card>

      <!-- ── Stats cards ─────────────────────────────────────────────────── -->
      <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
        <!-- Peso actual -->
        <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-white/[0.02]">
          <div class="mb-2 flex items-center gap-2 text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">
            <Icon icon="mdi:weight-kilogram" class="h-4 w-4" />
            Peso actual
          </div>
          <p class="text-2xl font-bold text-gray-900 dark:text-white">
            {{ resumen.peso_actual }}<span class="text-sm font-normal text-gray-500"> kg</span>
          </p>
          <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
            Inicio: {{ config.peso_inicial }} kg
          </p>
        </div>

        <!-- IMC -->
        <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-white/[0.02]">
          <div class="mb-2 flex items-center gap-2 text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">
            <Icon icon="mdi:human-male-height" class="h-4 w-4" />
            IMC actual
          </div>
          <p class="text-2xl font-bold" :class="imcTextColor(resumen.imc_color)">{{ resumen.imc_actual }}</p>
          <p class="mt-0.5">
            <Badge :color="badgeColorFromImc(resumen.imc_color)">{{ resumen.imc_categoria }}</Badge>
          </p>
        </div>

        <!-- kg perdidos -->
        <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-white/[0.02]">
          <div class="mb-2 flex items-center gap-2 text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">
            <Icon icon="mdi:trending-down" class="h-4 w-4" />
            Pérdida total
          </div>
          <p class="text-2xl font-bold" :class="resumen.kg_perdidos > 0 ? 'text-emerald-600' : resumen.kg_perdidos < 0 ? 'text-red-500' : 'text-gray-600'">
            {{ resumen.kg_perdidos > 0 ? '-' : resumen.kg_perdidos < 0 ? '+' : '' }}{{ Math.abs(resumen.kg_perdidos) }}<span class="text-sm font-normal text-gray-500"> kg</span>
          </p>
          <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
            Talla: {{ config.talla }} cm
          </p>
        </div>

        <!-- Progreso meta -->
        <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-white/[0.02]">
          <div class="mb-2 flex items-center gap-2 text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">
            <Icon icon="mdi:flag-checkered" class="h-4 w-4" />
            Hacia la meta
          </div>
          <div v-if="resumen.progreso_pct !== null">
            <p class="text-2xl font-bold text-emerald-600">{{ resumen.progreso_pct }}%</p>
            <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
              Meta: {{ config.peso_meta }} kg
            </p>
          </div>
          <div v-else class="text-sm text-gray-400 dark:text-gray-500 pt-1">Sin meta definida</div>
        </div>
      </div>

      <!-- Barra de progreso hacia meta -->
      <div v-if="resumen.progreso_pct !== null" class="rounded-2xl border border-gray-200 bg-white px-6 py-4 shadow-sm dark:border-gray-700 dark:bg-white/[0.02]">
        <div class="mb-2 flex items-center justify-between text-sm">
          <span class="font-medium text-gray-700 dark:text-gray-300">Progreso hacia la meta</span>
          <span class="font-bold text-emerald-600">{{ Math.min(resumen.progreso_pct, 100) }}%</span>
        </div>
        <div class="h-3 w-full overflow-hidden rounded-full bg-gray-200 dark:bg-gray-700">
          <div
            class="h-full rounded-full bg-gradient-to-r from-emerald-400 to-emerald-600 transition-all"
            :style="{ width: Math.min(resumen.progreso_pct, 100) + '%' }"
          />
        </div>
        <div class="mt-1.5 flex justify-between text-xs text-gray-500 dark:text-gray-400">
          <span>Inicio: {{ config.peso_inicial }} kg</span>
          <span v-if="config.fecha_meta">Meta: {{ formatFecha(config.fecha_meta) }}</span>
          <span>Meta: {{ config.peso_meta }} kg</span>
        </div>
      </div>

      <!-- ── IMC Reference Table ─────────────────────────────────────────── -->
      <div class="grid gap-4 lg:grid-cols-3">
        <!-- Tabla de referencia IMC -->
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-white/[0.02]">
          <h3 class="mb-3 flex items-center gap-2 text-sm font-semibold text-gray-700 dark:text-gray-200">
            <Icon icon="mdi:information-outline" class="h-4 w-4 text-gray-400" />
            Clasificación IMC
          </h3>
          <div class="space-y-1.5">
            <div v-for="cat in imcReference" :key="cat.label"
              class="flex items-center justify-between rounded-lg px-3 py-1.5 text-xs"
              :class="resumen.imc_categoria === cat.label ? cat.activeBg : 'bg-gray-50 dark:bg-gray-800/50'"
            >
              <span :class="resumen.imc_categoria === cat.label ? cat.activeText : 'text-gray-600 dark:text-gray-400'">
                {{ cat.range }}
              </span>
              <span :class="['font-semibold', resumen.imc_categoria === cat.label ? cat.activeText : 'text-gray-700 dark:text-gray-200']">
                {{ cat.label }}
                <Icon v-if="resumen.imc_categoria === cat.label" icon="mdi:arrow-left" class="inline h-3 w-3" />
              </span>
            </div>
          </div>
        </div>

        <!-- Agregar registro -->
        <div class="lg:col-span-2 rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-white/[0.02]">
          <h3 class="mb-4 flex items-center gap-2 text-sm font-semibold text-gray-700 dark:text-gray-200">
            <Icon icon="mdi:plus-circle-outline" class="h-4 w-4 text-emerald-500" />
            Agregar registro de peso
          </h3>
          <form @submit.prevent="submitRegistro" class="space-y-3">
            <div class="grid grid-cols-2 gap-3">
              <div>
                <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-300">Fecha *</label>
                <input v-model="formRegistro.fecha" type="date" required class="w-full rounded-xl border-2 border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 placeholder-gray-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 dark:border-gray-600 dark:bg-gray-800 dark:text-white" />
              </div>
              <div>
                <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-300">Peso (kg) *</label>
                <input v-model.number="formRegistro.peso" type="number" step="0.1" min="1" max="500" required class="w-full rounded-xl border-2 border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 placeholder-gray-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 dark:border-gray-600 dark:bg-gray-800 dark:text-white" placeholder="Ej: 90.5" />
              </div>
            </div>
            <div>
              <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-300">Notas (opcional)</label>
              <input v-model="formRegistro.notas" type="text" class="w-full rounded-xl border-2 border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 placeholder-gray-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 dark:border-gray-600 dark:bg-gray-800 dark:text-white" placeholder="Ej: Medido en ayunas" maxlength="500" />
            </div>

            <!-- Preview IMC con el peso ingresado -->
            <div v-if="formRegistro.peso" class="rounded-lg border border-emerald-200 bg-emerald-50 p-3 dark:border-emerald-800 dark:bg-emerald-900/20">
              <p class="text-xs text-emerald-700 dark:text-emerald-400">
                IMC con este peso:
                <strong>{{ calcularImcPreview(formRegistro.peso, config.talla) }}</strong>
                — {{ categoriaImcPreview(calcularImcPreview(formRegistro.peso, config.talla)) }}
                <span v-if="resumen.peso_actual !== config.peso_inicial" class="ml-2 text-gray-500 dark:text-gray-400">
                  (Δ {{ diferenciaPeso(formRegistro.peso) > 0 ? '+' : '' }}{{ diferenciaPeso(formRegistro.peso) }} kg)
                </span>
              </p>
            </div>

            <button
              type="submit"
              :disabled="submitting"
              class="inline-flex items-center gap-2 rounded-xl bg-emerald-500 px-5 py-2.5 text-sm font-semibold text-white shadow-md shadow-emerald-500/25 transition hover:bg-emerald-600 disabled:opacity-50"
            >
              <Icon icon="mdi:check" class="h-4 w-4" />
              {{ submitting ? 'Guardando…' : 'Guardar registro' }}
            </button>
          </form>
        </div>
      </div>

      <!-- ── Tabla de progreso ───────────────────────────────────────────── -->
      <div class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-white/[0.02]">
        <div class="flex items-center justify-between border-b border-gray-200 px-5 py-4 dark:border-gray-700">
          <h3 class="flex items-center gap-2 font-semibold text-gray-900 dark:text-white">
            <Icon icon="mdi:chart-timeline-variant" class="h-5 w-5 text-emerald-500" />
            Progreso de pérdida de peso
          </h3>
          <span class="text-xs text-gray-500 dark:text-gray-400">{{ tabla.length }} registro(s)</span>
        </div>

        <div v-if="tabla.length === 0" class="py-12 text-center">
          <Icon icon="mdi:scale-off" class="mx-auto mb-3 h-10 w-10 text-gray-400" />
          <p class="text-sm text-gray-600 dark:text-gray-400">Aún no hay registros. Agrega el primer peso.</p>
        </div>

        <div v-else class="overflow-x-auto">
          <table class="w-full text-left text-sm">
            <thead>
              <tr class="border-b border-gray-100 bg-gray-50/80 text-xs font-semibold uppercase tracking-wide text-gray-600 dark:border-gray-700 dark:bg-gray-800/80 dark:text-gray-400">
                <th class="px-5 py-3.5">#</th>
                <th class="px-5 py-3.5">Fecha</th>
                <th class="px-5 py-3.5">Peso (kg)</th>
                <th class="px-5 py-3.5">% Cambio</th>
                <th class="px-5 py-3.5">IMC</th>
                <th class="px-5 py-3.5">Categoría</th>
                <th class="px-5 py-3.5">Notas</th>
                <th class="px-5 py-3.5 text-right">Acción</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
              <tr
                v-for="r in tablaOrdenada"
                :key="r.id"
                class="transition hover:bg-gray-50/50 dark:hover:bg-white/[0.02]"
                :class="r.id === ultimoId ? 'bg-emerald-50/50 dark:bg-emerald-900/10' : ''"
              >
                <td class="px-5 py-3.5 text-gray-400 dark:text-gray-500">{{ r.semana }}</td>
                <td class="px-5 py-3.5 font-medium text-gray-700 dark:text-gray-200">{{ formatFecha(r.fecha) }}</td>
                <td class="px-5 py-3.5 font-bold text-gray-900 dark:text-white">{{ r.peso }}</td>
                <td class="px-5 py-3.5 font-medium" :class="pctColor(r.pct_cambio)">
                  <span v-if="r.pct_cambio !== 0">{{ r.pct_cambio > 0 ? '+' : '' }}{{ r.pct_cambio }}%</span>
                  <span v-else class="text-gray-400">—</span>
                </td>
                <td class="px-5 py-3.5 font-semibold" :class="imcTextColor(r.imc_color)">{{ r.imc }}</td>
                <td class="px-5 py-3.5">
                  <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-semibold" :class="imcBadgeClass(r.imc_color)">
                    {{ r.imc_cat }}
                  </span>
                </td>
                <td class="px-5 py-3.5 text-xs text-gray-500 dark:text-gray-400">{{ r.notas || '—' }}</td>
                <td class="px-5 py-3.5 text-right">
                  <button
                    @click="confirmarEliminarRegistro(r.id)"
                    class="rounded-lg p-1.5 text-red-400 transition hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-900/20"
                    title="Eliminar registro"
                  >
                    <Icon icon="mdi:delete-outline" class="h-4 w-4" />
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- ── Recompensas ─────────────────────────────────────────────────── -->
      <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-white/[0.02]">
        <div class="mb-4 flex items-center justify-between">
          <h3 class="flex items-center gap-2 font-semibold text-gray-900 dark:text-white">
            <Icon icon="mdi:gift-outline" class="h-5 w-5 text-yellow-500" />
            Mis recompensas
          </h3>
          <button
            @click="agregarRecompensa"
            class="inline-flex items-center gap-1 rounded-lg border border-gray-300 px-3 py-1.5 text-xs font-medium text-gray-600 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-400 dark:hover:bg-gray-800"
          >
            <Icon icon="mdi:plus" class="h-3.5 w-3.5" />
            Agregar
          </button>
        </div>

        <div v-if="recompensasLocal.length === 0" class="py-6 text-center">
          <Icon icon="mdi:gift-open-outline" class="mx-auto mb-2 h-8 w-8 text-gray-300" />
          <p class="text-xs text-gray-500">Define recompensas para motivarte en el camino</p>
          <button @click="agregarRecompensaDemo" class="mt-2 text-xs text-emerald-600 underline hover:text-emerald-700">
            Agregar ejemplos
          </button>
        </div>

        <div v-else class="space-y-2">
          <div
            v-for="(r, i) in recompensasLocal"
            :key="i"
            class="flex items-center gap-3 rounded-xl p-3"
            :class="r.done ? 'bg-emerald-50 dark:bg-emerald-900/15' : 'bg-gray-50 dark:bg-gray-800/50'"
          >
            <button @click="toggleRecompensa(i)" class="flex-shrink-0">
              <Icon
                :icon="r.done ? 'mdi:checkbox-marked' : 'mdi:checkbox-blank-outline'"
                class="h-5 w-5 transition"
                :class="r.done ? 'text-emerald-500' : 'text-gray-400'"
              />
            </button>
            <div class="min-w-0 flex-1">
              <input
                v-model="r.descripcion"
                type="text"
                placeholder="Descripción de la recompensa..."
                class="w-full bg-transparent text-sm font-medium text-gray-900 placeholder-gray-400 focus:outline-none dark:text-white"
                :class="r.done ? 'line-through opacity-60' : ''"
              />
            </div>
            <div class="flex flex-shrink-0 items-center gap-1">
              <input
                v-model.number="r.kg_perdidos"
                type="number"
                step="0.5"
                min="0"
                class="w-16 rounded-lg border border-gray-300 bg-white px-2 py-1 text-center text-xs dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                placeholder="kg"
              />
              <span class="text-xs text-gray-400">kg</span>
            </div>
            <button @click="quitarRecompensa(i)" class="flex-shrink-0 rounded-lg p-1 text-red-400 hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-900/20">
              <Icon icon="mdi:close" class="h-4 w-4" />
            </button>
          </div>
        </div>

        <div v-if="recompensasLocal.length > 0" class="mt-4 flex justify-end">
          <button
            @click="guardarRecompensas"
            :disabled="submitting"
            class="inline-flex items-center gap-2 rounded-xl bg-yellow-500 px-4 py-2 text-sm font-semibold text-white hover:bg-yellow-600 disabled:opacity-50"
          >
            <Icon icon="mdi:content-save" class="h-4 w-4" />
            {{ submitting ? 'Guardando…' : 'Guardar recompensas' }}
          </button>
        </div>
      </div>
    </div>

    <!-- ── Modal: Configurar (editar) ─────────────────────────────────────── -->
    <Teleport to="body">
      <div v-if="modalConfigura" class="fixed inset-0 z-[100000] flex items-center justify-center bg-black/50 p-4 backdrop-blur-sm">
        <div class="w-full max-w-lg rounded-2xl bg-white shadow-2xl dark:bg-gray-900">
          <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4 dark:border-gray-700">
            <h3 class="font-bold text-gray-900 dark:text-white">Editar configuración</h3>
            <button @click="modalConfigura = false" class="rounded-lg p-1 hover:bg-gray-100 dark:hover:bg-gray-800">
              <Icon icon="mdi:close" class="h-5 w-5 text-gray-500" />
            </button>
          </div>
          <form @submit.prevent="submitConfigurar" class="space-y-4 p-6">
            <div class="grid grid-cols-2 gap-3">
              <div>
                <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-300">Peso inicial (kg) *</label>
                <input v-model.number="formConfig.peso_inicial" type="number" step="0.1" min="1" max="500" required class="w-full rounded-xl border-2 border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 placeholder-gray-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 dark:border-gray-600 dark:bg-gray-800 dark:text-white" />
              </div>
              <div>
                <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-300">Talla (cm) *</label>
                <input v-model.number="formConfig.talla" type="number" step="0.1" min="50" max="300" required class="w-full rounded-xl border-2 border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 placeholder-gray-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 dark:border-gray-600 dark:bg-gray-800 dark:text-white" />
              </div>
            </div>
            <div class="grid grid-cols-2 gap-3">
              <div>
                <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-300">Peso meta (kg)</label>
                <input v-model.number="formConfig.peso_meta" type="number" step="0.1" min="1" max="500" class="w-full rounded-xl border-2 border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 placeholder-gray-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 dark:border-gray-600 dark:bg-gray-800 dark:text-white" />
              </div>
              <div>
                <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-300">Fecha de inicio *</label>
                <input v-model="formConfig.fecha_inicio" type="date" required class="w-full rounded-xl border-2 border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 placeholder-gray-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 dark:border-gray-600 dark:bg-gray-800 dark:text-white" />
              </div>
            </div>
            <div>
              <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-300">Fecha meta</label>
              <input v-model="formConfig.fecha_meta" type="date" class="w-full rounded-xl border-2 border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 placeholder-gray-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 dark:border-gray-600 dark:bg-gray-800 dark:text-white" />
            </div>
            <div class="flex gap-3 pt-2">
              <button type="button" @click="modalConfigura = false" class="flex-1 rounded-xl border border-gray-300 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300">
                Cancelar
              </button>
              <button type="submit" :disabled="submitting" class="flex-1 rounded-xl bg-emerald-500 py-2.5 text-sm font-semibold text-white hover:bg-emerald-600 disabled:opacity-50">
                {{ submitting ? 'Guardando…' : 'Guardar' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </Teleport>

    <!-- ── Modal: Confirmar eliminación ───────────────────────────────────── -->
    <Teleport to="body">
      <div v-if="modalConfirm.show" class="fixed inset-0 z-[100000] flex items-center justify-center bg-black/50 p-4 backdrop-blur-sm">
        <div class="w-full max-w-sm rounded-2xl bg-white p-6 shadow-2xl dark:bg-gray-900">
          <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-red-100 dark:bg-red-900/30">
            <Icon icon="mdi:alert-outline" class="h-6 w-6 text-red-500" />
          </div>
          <h3 class="mb-1 font-bold text-gray-900 dark:text-white">{{ modalConfirm.title }}</h3>
          <p class="mb-5 text-sm text-gray-500 dark:text-gray-400">{{ modalConfirm.message }}</p>
          <div class="flex gap-3">
            <button @click="modalConfirm.show = false" class="flex-1 rounded-xl border border-gray-300 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300">
              Cancelar
            </button>
            <button @click="modalConfirm.action" class="flex-1 rounded-xl bg-red-500 py-2.5 text-sm font-semibold text-white hover:bg-red-600">
              Eliminar
            </button>
          </div>
        </div>
      </div>
    </Teleport>
  </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { usePage, router } from '@inertiajs/vue3';
import AppLayout from '@/Pages/Layouts/AppLayout.vue';
import PageBreadcrumb from '@/components/PageBreadcrumb.vue';
import { Alert, Card, Button, Badge } from '@/components/ui';
import { Icon } from '@iconify/vue';

const props = defineProps({
  title:    { type: String, default: 'VitaTrack' },
  config:   { type: Object, required: true },
  paciente: { type: Object, required: true },
  tabla:    { type: Array, default: () => [] },
  resumen:  { type: Object, required: true },
});

const page  = usePage();
const flash = computed(() => page.props.flash ?? {});

// Tabla ordenada desc (más reciente primero para visualización)
const tablaOrdenada = computed(() => [...props.tabla].reverse());
const ultimoId      = computed(() => props.tabla[props.tabla.length - 1]?.id ?? null);

// ── Formulario de registro ────────────────────────────────────────────────────
const formRegistro = ref({
  config_id: props.config.id,
  fecha:     new Date().toISOString().split('T')[0],
  peso:      null,
  notas:     '',
});
const submitting = ref(false);

function submitRegistro() {
  submitting.value = true;
  router.post('/guardar-peso-registro', formRegistro.value, {
    onSuccess: () => {
      formRegistro.value.peso  = null;
      formRegistro.value.notas = '';
    },
    onFinish: () => { submitting.value = false; },
  });
}

// ── Configurar (editar) ───────────────────────────────────────────────────────
const modalConfigura = ref(false);
const formConfig     = ref({
  paciente_id:  props.config.paciente_id,
  peso_inicial: props.config.peso_inicial,
  talla:        props.config.talla,
  peso_meta:    props.config.peso_meta,
  fecha_inicio: props.config.fecha_inicio,
  fecha_meta:   props.config.fecha_meta ?? '',
  recompensas:  props.config.recompensas ?? [],
});

function submitConfigurar() {
  submitting.value = true;
  router.post('/configurar-control-peso', formConfig.value, {
    onFinish: () => { submitting.value = false; modalConfigura.value = false; },
  });
}

// ── Confirmar eliminaciones ───────────────────────────────────────────────────
const modalConfirm = ref({ show: false, title: '', message: '', action: null });

function confirmarEliminarRegistro(registroId) {
  modalConfirm.value = {
    show:    true,
    title:   '¿Eliminar registro?',
    message: 'Esta acción no se puede deshacer.',
    action:  () => {
      modalConfirm.value.show = false;
      router.post('/eliminar-peso-registro', { registro_id: registroId });
    },
  };
}

function confirmarEliminarSeguimiento() {
  modalConfirm.value = {
    show:    true,
    title:   '¿Eliminar todo el seguimiento?',
    message: 'Se eliminarán todos los registros de peso. Esta acción no se puede deshacer.',
    action:  () => {
      modalConfirm.value.show = false;
      router.post('/eliminar-control-peso', { config_id: props.config.id });
    },
  };
}

// ── Recompensas ───────────────────────────────────────────────────────────────
const recompensasLocal = ref(
  (props.config.recompensas ?? []).map(r => ({ ...r }))
);

function agregarRecompensa() {
  recompensasLocal.value.push({ kg_perdidos: 0, descripcion: '', done: false });
}

function agregarRecompensaDemo() {
  recompensasLocal.value = [
    { kg_perdidos: 2,  descripcion: 'Nuevos zapatos',       done: false },
    { kg_perdidos: 5,  descripcion: 'Spa o masaje relajante', done: false },
    { kg_perdidos: 8,  descripcion: 'Comprar ropa nueva',   done: false },
    { kg_perdidos: 12, descripcion: 'Viaje de fin de semana', done: false },
  ];
}

function quitarRecompensa(i) {
  recompensasLocal.value.splice(i, 1);
}

function toggleRecompensa(i) {
  recompensasLocal.value[i].done = !recompensasLocal.value[i].done;
}

function guardarRecompensas() {
  submitting.value = true;
  router.post('/actualizar-recompensas', {
    config_id:   props.config.id,
    recompensas: recompensasLocal.value,
  }, {
    onFinish: () => { submitting.value = false; },
  });
}

// ── IMC helpers ───────────────────────────────────────────────────────────────
function calcularImcPreview(peso, talla) {
  if (!talla || talla <= 0) return 0;
  const m = talla / 100;
  return Math.round((peso / (m * m)) * 10) / 10;
}

function categoriaImcPreview(imc) {
  if (imc < 18.5) return 'Bajo peso';
  if (imc < 25)   return 'Normal';
  if (imc < 30)   return 'Sobrepeso';
  if (imc < 35)   return 'Obesidad I';
  if (imc < 40)   return 'Obesidad II';
  return 'Obesidad III';
}

function diferenciaPeso(pesoNuevo) {
  return Math.round((pesoNuevo - props.resumen.peso_actual) * 10) / 10;
}

const imcReference = [
  { range: '< 18.5',     label: 'Bajo peso',   activeBg: 'bg-blue-100 dark:bg-blue-900/30',   activeText: 'text-blue-700 dark:text-blue-400' },
  { range: '18.5 – 24.9', label: 'Normal',      activeBg: 'bg-emerald-100 dark:bg-emerald-900/30', activeText: 'text-emerald-700 dark:text-emerald-400' },
  { range: '25.0 – 29.9', label: 'Sobrepeso',   activeBg: 'bg-yellow-100 dark:bg-yellow-900/30', activeText: 'text-yellow-700 dark:text-yellow-400' },
  { range: '30.0 – 34.9', label: 'Obesidad I',  activeBg: 'bg-orange-100 dark:bg-orange-900/30', activeText: 'text-orange-700 dark:text-orange-400' },
  { range: '35.0 – 39.9', label: 'Obesidad II', activeBg: 'bg-red-100 dark:bg-red-900/30',    activeText: 'text-red-700 dark:text-red-400' },
  { range: '≥ 40',        label: 'Obesidad III', activeBg: 'bg-red-200 dark:bg-red-900/40',   activeText: 'text-red-800 dark:text-red-300' },
];

// ── UI helpers ────────────────────────────────────────────────────────────────
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

function badgeColorFromImc(color) {
  const map = { green: 'success', yellow: 'warning', orange: 'warning', red: 'error', blue: 'primary' };
  return map[color] || 'light';
}
</script>

