<template>
  <div class="relative z-[1] min-h-screen bg-white dark:bg-gray-900">
    <div class="relative flex min-h-screen w-full flex-col justify-center sm:p-0 lg:flex-row">
      <div class="flex w-full flex-1 flex-col lg:w-1/2">
        <div class="mx-auto flex w-full max-w-md flex-1 flex-col justify-center px-4 pt-10 pb-10">
          <slot />
        </div>
      </div>
      <div class="relative hidden h-full min-h-[200px] w-full items-center bg-brand-950 dark:bg-white/5 lg:grid lg:min-h-screen lg:w-1/2">
        <div class="z-[1] flex w-full flex-col items-center justify-center p-8">
          <a href="/" class="mb-4 block">
            <img src="/logo_cpromed.jpg" alt="CPROMED PERU" class="mx-auto h-16 max-w-[200px] object-contain" />
          </a>
          <p class="text-center text-gray-400 dark:text-white/60">Sistema de gestión Cepromed Perú</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
</script>
