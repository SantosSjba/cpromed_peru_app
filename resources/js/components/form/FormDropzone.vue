<template>
  <div
    class="cursor-pointer rounded-xl border border-dashed transition-colors hover:border-brand-500 dark:border-gray-700 dark:hover:border-brand-500"
    :class="isDragging ? 'border-brand-500 bg-gray-100 dark:bg-gray-800' : 'border-gray-300 bg-gray-50 dark:bg-gray-900'"
    @drop.prevent="onDrop"
    @dragover.prevent="isDragging = true"
    @dragleave.prevent="isDragging = false"
    @click="fileInput?.click()"
  >
    <input
      ref="fileInput"
      type="file"
      class="hidden"
      :accept="accept"
      multiple
      @change="onInputChange"
      @click.stop
    />
    <div class="flex flex-col items-center p-7 lg:p-10">
      <div class="mb-5 flex h-[68px] w-[68px] items-center justify-center rounded-full bg-gray-200 text-gray-700 dark:bg-gray-800 dark:text-gray-400">
        <Icon icon="mdi:upload" class="h-8 w-8" />
      </div>
      <h4 class="mb-3 text-xl font-semibold text-gray-800 dark:text-white/90">
        {{ isDragging ? 'Suelta aquí' : 'Arrastra archivos aquí' }}
      </h4>
      <span class="mb-5 block max-w-[290px] text-center text-sm text-gray-700 dark:text-gray-400">
        <slot name="hint">{{ hint }}</slot>
      </span>
      <span class="text-sm font-medium text-brand-500 underline">
        <slot name="browse">Examinar</slot>
      </span>
    </div>
    <div v-if="files.length > 0" class="mt-4 border-t border-gray-200 p-4 dark:border-gray-700">
      <h5 class="mb-3 text-sm font-semibold text-gray-700 dark:text-gray-300">Archivos:</h5>
      <ul class="space-y-2">
        <li
          v-for="(file, index) in files"
          :key="index"
          class="flex items-center justify-between rounded-lg bg-gray-50 p-3 dark:bg-gray-800"
        >
          <div class="flex items-center gap-3">
            <Icon icon="mdi:file-document-outline" class="h-5 w-5 text-gray-500" />
            <span class="text-sm text-gray-700 dark:text-gray-300">{{ file.name }}</span>
          </div>
          <button
            type="button"
            class="text-red-500 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300"
            @click.stop="removeFile(index)"
          >
            <Icon icon="mdi:close" class="h-5 w-5" />
          </button>
        </li>
      </ul>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { Icon } from '@iconify/vue';

const props = defineProps({
  accept: { type: String, default: 'image/png,image/jpeg,image/webp,image/svg+xml' },
  hint: { type: String, default: 'Arrastra imágenes aquí o haz clic para seleccionar' },
});

const emit = defineEmits(['change', 'update:files']);

const isDragging = ref(false);
const files = ref([]);
const fileInput = ref(null);

function onDrop(e) {
  isDragging.value = false;
  const dropped = Array.from(e.dataTransfer?.files ?? []);
  addFiles(dropped);
}

function onInputChange(e) {
  const selected = Array.from(e.target.files ?? []);
  addFiles(selected);
  if (fileInput.value) fileInput.value.value = '';
}

function addFiles(newFiles) {
  const valid = props.accept
    ? newFiles.filter((f) => {
        const t = f.type;
        return props.accept.split(',').some((a) => t.match(a.trim().replace('*', '.*')));
      })
    : newFiles;
  if (valid.length) {
    files.value = [...files.value, ...valid];
    emit('change', valid);
    emit('update:files', files.value);
  }
}

function removeFile(index) {
  files.value.splice(index, 1);
  emit('update:files', files.value);
}
</script>
