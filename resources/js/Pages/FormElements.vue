<template>
  <AppLayout>
    <div class="space-y-8">
      <PageBreadcrumb :page-title="title" :items="[{ label: 'Form Elements', url: null }]" />

      <!-- Alerts (componentes UI) -->
      <Card title="Alertas" desc="Componente Alert con variantes success, error, warning, info.">
        <div class="space-y-4">
          <Alert variant="success" title="Éxito" message="Operación completada correctamente." />
          <Alert variant="error" title="Error" message="Ha ocurrido un problema." />
          <Alert variant="warning" title="Aviso" message="Revise los datos ingresados." />
          <Alert variant="info" title="Información" message="Estos son los componentes Vue migrados desde Blade." />
        </div>
      </Card>

      <!-- Buttons & Badges -->
      <Card title="Botones y Badges" desc="Button (primary/outline) y Badge (variantes y tamaños).">
        <div class="flex flex-wrap items-center gap-4">
          <Button variant="primary" size="md">Primario</Button>
          <Button variant="outline" size="md">Outline</Button>
          <Button variant="primary" size="sm" start-icon="mdi:plus">Con icono</Button>
          <Badge variant="light" color="primary" size="md">Primary</Badge>
          <Badge variant="light" color="success" size="sm">Success</Badge>
          <Badge variant="solid" color="error" size="md">Error</Badge>
        </div>
      </Card>

      <!-- Avatar -->
      <Card title="Avatar" desc="Avatar con tamaños y estado (online/offline/busy).">
        <div class="flex flex-wrap items-center gap-6">
          <Avatar size="small" fallback-text="A" />
          <Avatar size="medium" fallback-text="B" />
          <Avatar size="large" status="online" fallback-text="C" />
          <Avatar size="xlarge" status="offline" fallback-text="D" />
        </div>
      </Card>

      <!-- Form: Input, Textarea, Select, Checkbox -->
      <Card title="Inputs por defecto" desc="FormInput, FormTextarea, FormSelect, FormCheckbox (Tailwind).">
        <div class="space-y-6">
          <FormInput v-model="demoText" label="Input de texto" placeholder="Escriba aquí..." />
          <FormInput v-model="demoPassword" label="Contraseña" type="password" placeholder="••••••••" />
          <FormTextarea v-model="demoTextarea" label="Descripción" placeholder="Descripción..." :rows="4" />
          <FormSelect v-model="demoSelect" label="Select" placeholder="Seleccione una opción">
            <option value="a">Opción A</option>
            <option value="b">Opción B</option>
            <option value="c">Opción C</option>
          </FormSelect>
          <FormCheckbox v-model="demoCheck">Acepto términos y condiciones</FormCheckbox>
        </div>
      </Card>

      <!-- Modal -->
      <Card title="Modal" desc="Componente Modal (v-model) con botón de cierre y backdrop.">
        <Button variant="primary" @click="modalOpen = true">Abrir modal</Button>
        <Modal v-model="modalOpen" :show-close-button="true">
          <div class="p-6">
            <h3 class="mb-2 text-lg font-semibold text-gray-800 dark:text-white/90">Título del modal</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400">
              Contenido del modal. Cierre con el botón X, clic fuera o tecla Escape.
            </p>
          </div>
        </Modal>
      </Card>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref } from 'vue';
import AppLayout from './Layouts/AppLayout.vue';
import PageBreadcrumb from '@/components/PageBreadcrumb.vue';
import { Button, Modal, Badge, Alert, Avatar, Card } from '@/components/ui';
import { FormInput, FormTextarea, FormSelect, FormCheckbox } from '@/components/form';

defineProps({ title: { type: String, default: 'Form Elements' } });

const modalOpen = ref(false);
const demoText = ref('');
const demoPassword = ref('');
const demoTextarea = ref('');
const demoSelect = ref('');
const demoCheck = ref(false);
</script>
