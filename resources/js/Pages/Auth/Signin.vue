<template>
  <GuestLayout>
    <div>
      <div class="mb-5 sm:mb-8">
        <h1 class="mb-2 text-2xl font-semibold text-gray-800 dark:text-white/90 sm:text-3xl">Iniciar sesión</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400">Ingresa tu correo y contraseña para acceder.</p>
      </div>
      <div v-if="status" class="mb-4 rounded-lg border border-amber-200 bg-amber-50 p-3 text-sm text-amber-800 dark:border-amber-800 dark:bg-amber-900/20 dark:text-amber-300">
        {{ status }}
      </div>
      <div v-if="errors && Object.keys(errors).length" class="mb-4 rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-700 dark:border-red-800 dark:bg-red-900/20 dark:text-red-400">
        <p v-for="(msg, key) in errors" :key="key">{{ Array.isArray(msg) ? msg[0] : msg }}</p>
      </div>
      <form @submit.prevent="submit" class="space-y-5">
        <div>
          <label for="email" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
            Email <span class="text-red-500">*</span>
          </label>
          <input
            id="email"
            v-model="form.email"
            type="email"
            autocomplete="email"
            required
            class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder-gray-400 shadow-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder-white/30"
            :class="{ 'border-red-500': errors && errors.email }"
            placeholder="info@gmail.com"
          />
          <p v-if="errors && errors.email" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ Array.isArray(errors.email) ? errors.email[0] : errors.email }}</p>
        </div>
        <div>
          <label for="password" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
            Contraseña <span class="text-red-500">*</span>
          </label>
          <div class="relative">
            <input
              id="password"
              v-model="form.password"
              :type="showPassword ? 'text' : 'password'"
              autocomplete="current-password"
              required
              class="h-11 w-full rounded-lg border border-gray-300 bg-transparent py-2.5 pl-4 pr-11 text-sm text-gray-800 placeholder-gray-400 shadow-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder-white/30"
              :class="{ 'border-red-500': errors && errors.password }"
              placeholder="Contraseña"
            />
            <button
              type="button"
              class="absolute right-4 top-1/2 z-30 -translate-y-1/2 text-gray-500 dark:text-gray-400"
              @click="showPassword = !showPassword"
            >
              <Icon v-show="!showPassword" icon="mdi:eye" class="h-5 w-5" />
              <Icon v-show="showPassword" icon="mdi:eye-off" class="h-5 w-5" />
            </button>
          </div>
          <p v-if="errors && errors.password" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ Array.isArray(errors.password) ? errors.password[0] : errors.password }}</p>
        </div>
        <div class="flex items-center justify-between">
          <label class="flex cursor-pointer select-none items-center text-sm font-normal text-gray-700 dark:text-gray-400">
            <input v-model="form.remember" type="checkbox" class="sr-only" />
            <span class="mr-3 flex h-5 w-5 items-center justify-center rounded border-[1.25px] transition"
              :class="form.remember ? 'border-brand-500 bg-brand-500' : 'border-gray-300 bg-transparent dark:border-gray-700'">
              <Icon v-show="form.remember" icon="mdi:check" class="h-3.5 w-3.5 text-white" />
            </span>
            Recordarme
          </label>
        </div>
        <div>
          <button
            type="submit"
            :disabled="form.processing"
            class="flex w-full items-center justify-center rounded-lg bg-brand-500 px-4 py-3 text-sm font-medium text-white shadow transition hover:bg-brand-600 disabled:opacity-50"
          >
            {{ form.processing ? 'Entrando...' : 'Iniciar sesión' }}
          </button>
        </div>
      </form>
      <p class="mt-5 text-center text-sm text-gray-700 dark:text-gray-400 sm:text-start">
        ¿No tienes cuenta?
        <Link href="/signup" class="text-brand-500 hover:text-brand-600 dark:text-brand-400">Registrarse</Link>
      </p>
    </div>
  </GuestLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useForm, Link, usePage } from '@inertiajs/vue3';
import { Icon } from '@iconify/vue';
import GuestLayout from '../Layouts/GuestLayout.vue';

defineProps({ status: String });

const page = usePage();
const errors = computed(() => page.props.errors || {});

const form = useForm({
  email: '',
  password: '',
  remember: false,
});

const showPassword = ref(false);

const submit = () => form.post('/login');
</script>
