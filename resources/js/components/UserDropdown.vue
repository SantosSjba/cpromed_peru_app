<template>
  <div class="relative" ref="dropdownRef">
    <button
      type="button"
      class="flex items-center text-gray-700 dark:text-gray-400"
      @click.prevent="dropdownOpen = !dropdownOpen"
    >
      <span
        class="mr-3 flex h-11 w-11 shrink-0 items-center justify-center overflow-hidden rounded-full bg-brand-500 text-sm font-semibold text-white dark:bg-brand-600"
      >
        {{ initials }}
      </span>
      <span class="mr-1 block font-medium text-theme-sm text-gray-800 dark:text-gray-200">{{ user?.name ?? 'Usuario' }}</span>
      <Icon
        icon="mdi:chevron-down"
        class="h-5 w-5 transition-transform duration-200"
        :class="{ 'rotate-180': dropdownOpen }"
      />
    </button>

    <Transition
      enter-active-class="transition ease-out duration-100"
      enter-from-class="transform opacity-0 scale-95"
      enter-to-class="transform opacity-100 scale-100"
      leave-active-class="transition ease-in duration-75"
      leave-from-class="transform opacity-100 scale-100"
      leave-to-class="transform opacity-0 scale-95"
    >
      <div
        v-show="dropdownOpen"
        class="absolute right-0 z-50 mt-[17px] flex w-[260px] flex-col rounded-2xl border border-gray-200 bg-white p-3 shadow-theme-lg dark:border-gray-800 dark:bg-gray-900"
      >
        <div>
          <span class="block font-medium text-gray-700 text-theme-sm dark:text-gray-400">{{ user?.name ?? 'Usuario' }}</span>
          <span class="mt-0.5 block text-theme-xs text-gray-500 dark:text-gray-400">{{ user?.email ?? '' }}</span>
        </div>

        <ul class="flex flex-col gap-1 border-b border-gray-200 pt-4 pb-3 dark:border-gray-800">
          <li>
            <Link
              href="/profile"
              class="menu-dropdown-item menu-dropdown-item-inactive flex items-center gap-3 rounded-lg px-3 py-2 font-medium text-theme-sm hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-white/5 dark:hover:text-gray-300"
              @click="dropdownOpen = false"
            >
              <span class="text-gray-500 dark:text-gray-400">
                <Icon icon="mdi:account" class="h-5 w-5" />
              </span>
              Mi perfil
            </Link>
          </li>
          <li>
            <Link
              href="/profile"
              class="menu-dropdown-item menu-dropdown-item-inactive flex items-center gap-3 rounded-lg px-3 py-2 font-medium text-theme-sm hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-white/5 dark:hover:text-gray-300"
              @click="dropdownOpen = false"
            >
              <span class="text-gray-500 dark:text-gray-400">
                <Icon icon="mdi:cog" class="h-5 w-5" />
              </span>
              Configuración
            </Link>
          </li>
        </ul>

        <form method="POST" action="/logout" class="mt-3" @submit="dropdownOpen = false">
          <input type="hidden" name="_token" :value="csrfToken" />
          <button
            type="submit"
            class="menu-dropdown-item menu-dropdown-item-inactive flex w-full items-center gap-3 rounded-lg px-3 py-2 font-medium text-theme-sm hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-white/5 dark:hover:text-gray-300"
          >
            <span class="text-gray-500 dark:text-gray-400">
              <Icon icon="mdi:logout" class="h-5 w-5" />
            </span>
            Cerrar sesión
          </button>
        </form>
      </div>
    </Transition>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { Icon } from '@iconify/vue';

const page = usePage();
const user = computed(() => page.props.auth?.user ?? null);
const dropdownOpen = ref(false);
const dropdownRef = ref(null);

const initials = computed(() => {
  const name = user.value?.name ?? 'Usuario';
  const words = name.trim().split(/\s+/).filter(Boolean);
  if (words.length >= 2) {
    return (words[0].charAt(0) + words[words.length - 1].charAt(0)).toUpperCase();
  }
  return (name.slice(0, 2) || name.slice(0, 1) || 'U').toUpperCase();
});

const csrfToken = computed(() => page.props.csrf_token ?? document.querySelector('meta[name="csrf-token"]')?.content ?? '');

function onClickOutside(e) {
  if (dropdownRef.value && !dropdownRef.value.contains(e.target)) {
    dropdownOpen.value = false;
  }
}

onMounted(() => {
  document.addEventListener('click', onClickOutside);
});
onUnmounted(() => {
  document.removeEventListener('click', onClickOutside);
});
</script>
