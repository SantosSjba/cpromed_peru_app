<template>
  <GuestLayout>
    <div>
      <Link href="/" class="inline-flex items-center gap-1.5 text-sm text-gray-500 transition hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300">
        <Icon icon="mdi:arrow-left" class="h-5 w-5" />
        Volver al inicio
      </Link>
      <div class="mb-5 sm:mb-8 mt-4">
        <h1 class="mb-2 text-2xl font-semibold text-gray-800 dark:text-white/90 sm:text-3xl">Crear cuenta</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400">Completa tus datos para registrarte en el sistema.</p>
      </div>
      <div v-if="errors && Object.keys(errors).length" class="mb-4 rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-700 dark:border-red-800 dark:bg-red-900/20 dark:text-red-400">
        <p v-for="(msg, key) in errors" :key="key">{{ Array.isArray(msg) ? msg[0] : msg }}</p>
      </div>
      <form @submit.prevent="form.post('/register')" class="space-y-5">
        <div>
          <label for="name" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Nombre <span class="text-red-500">*</span></label>
          <input id="name" v-model="form.name" type="text" required autocomplete="name" placeholder="Tu nombre"
            class="h-11 w-full rounded-lg border bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder-gray-400 shadow-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"
            :class="{ 'border-red-500': errors && errors.name }" />
          <p v-if="errors && errors.name" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ Array.isArray(errors.name) ? errors.name[0] : errors.name }}</p>
        </div>
        <div>
          <label for="email" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Email <span class="text-red-500">*</span></label>
          <input id="email" v-model="form.email" type="email" required autocomplete="email" placeholder="correo@ejemplo.com"
            class="h-11 w-full rounded-lg border bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder-gray-400 shadow-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"
            :class="{ 'border-red-500': errors && errors.email }" />
          <p v-if="errors && errors.email" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ Array.isArray(errors.email) ? errors.email[0] : errors.email }}</p>
        </div>
        <div>
          <label for="password" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Contraseña <span class="text-red-500">*</span></label>
          <div class="relative">
            <input id="password" v-model="form.password" :type="showPassword ? 'text' : 'password'" required autocomplete="new-password" placeholder="Contraseña"
              class="h-11 w-full rounded-lg border bg-transparent py-2.5 pl-4 pr-11 text-sm text-gray-800 placeholder-gray-400 shadow-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"
              :class="{ 'border-red-500': errors && errors.password }" />
            <button type="button" class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-500" @click="showPassword = !showPassword">👁</button>
          </div>
          <p v-if="errors && errors.password" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ Array.isArray(errors.password) ? errors.password[0] : errors.password }}</p>
        </div>
        <div>
          <label for="password_confirmation" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Confirmar contraseña <span class="text-red-500">*</span></label>
          <input id="password_confirmation" v-model="form.password_confirmation" type="password" required autocomplete="new-password" placeholder="Repetir contraseña"
            class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder-gray-400 shadow-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" />
        </div>
        <button type="submit" :disabled="form.processing"
          class="flex w-full items-center justify-center rounded-lg bg-brand-500 px-4 py-3 text-sm font-medium text-white shadow hover:bg-brand-600 disabled:opacity-50">
          {{ form.processing ? 'Registrando...' : 'Registrarse' }}
        </button>
      </form>
      <p class="mt-5 text-center text-sm text-gray-700 dark:text-gray-400 sm:text-start">
        ¿Ya tienes cuenta? <Link href="/signin" class="text-brand-500 hover:text-brand-600">Iniciar sesión</Link>
      </p>
    </div>
  </GuestLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useForm, Link, usePage } from '@inertiajs/vue3';
import { Icon } from '@iconify/vue';
import GuestLayout from '../Layouts/GuestLayout.vue';

const page = usePage();
const errors = computed(() => page.props.errors || {});

const form = useForm({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
});

const showPassword = ref(false);
</script>
