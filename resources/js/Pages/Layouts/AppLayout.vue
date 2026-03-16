<template>
  <div class="min-h-screen xl:flex">
    <!-- Backdrop (mobile): click cierra sidebar -->
    <div
      v-show="sidebar.isMobileOpen"
      class="fixed z-50 h-screen w-full bg-gray-900/50 xl:hidden"
      @click="sidebar.setMobileOpen(false)"
    />

    <!-- Sidebar (misma estructura y clases que layouts/sidebar.blade.php) -->
    <aside
      id="sidebar"
      class="fixed left-0 top-0 z-[99999] mt-0 flex h-screen flex-col border-r border-gray-200 bg-white px-5 text-gray-900 transition-all duration-300 ease-in-out dark:border-gray-800 dark:bg-gray-900"
      :class="{
        'w-[290px]': sidebar.isExpanded || sidebar.isMobileOpen || sidebar.isHovered,
        'w-[90px]': !sidebar.isExpanded && !sidebar.isHovered,
        'translate-x-0': sidebar.isMobileOpen,
        '-translate-x-full xl:translate-x-0': !sidebar.isMobileOpen,
      }"
      @mouseenter="onSidebarEnter"
      @mouseleave="sidebar.setHovered(false)"
    >
      <!-- Logo -->
      <div
        class="flex pt-8 pb-7"
        :class="
          !sidebar.isExpanded && !sidebar.isHovered && !sidebar.isMobileOpen
            ? 'xl:justify-center'
            : 'justify-start'
        "
      >
        <Link href="/" class="flex items-center">
          <img
            v-show="sidebar.isExpanded || sidebar.isHovered || sidebar.isMobileOpen"
            src="/logo_cpromed.jpg"
            alt="CPROMED PERU"
            class="h-10 w-auto max-w-[150px] object-contain"
          />
          <img
            v-show="!sidebar.isExpanded && !sidebar.isHovered && !sidebar.isMobileOpen"
            src="/logo_cpromed.jpg"
            alt="CPROMED"
            class="h-8 w-8 object-contain"
          />
        </Link>
      </div>

      <!-- Navigation (con submenus como Blade) -->
      <div class="flex flex-1 flex-col overflow-y-auto duration-300 ease-linear no-scrollbar">
        <nav class="mb-6">
          <div class="flex flex-col gap-4">
            <div v-for="(menuGroup, gIdx) in menuGroups" :key="gIdx">
              <h2
                class="mb-4 flex text-xs uppercase leading-[20px] text-gray-400"
                :class="
                  !sidebar.isExpanded && !sidebar.isHovered && !sidebar.isMobileOpen
                    ? 'lg:justify-center'
                    : 'justify-start'
                "
              >
                <span v-show="sidebar.isExpanded || sidebar.isHovered || sidebar.isMobileOpen">
                  {{ menuGroup.title }}
                </span>
                <Icon
                  v-show="!sidebar.isExpanded && !sidebar.isHovered && !sidebar.isMobileOpen"
                  icon="mdi:dots-horizontal"
                  class="h-6 w-6 text-gray-400"
                />
              </h2>
              <ul class="flex flex-col gap-1">
                <li v-for="(item, iIdx) in menuGroup.items" :key="iIdx">
                  <!-- Item con submenú -->
                  <template v-if="item.subItems && item.subItems.length">
                    <button
                      type="button"
                      class="menu-item group w-full"
                      :class="[
                        openSubmenus[subKey(gIdx, iIdx)] ? 'menu-item-active' : 'menu-item-inactive',
                        !sidebar.isExpanded && !sidebar.isHovered ? 'xl:justify-center' : 'xl:justify-start',
                      ]"
                      @click="toggleSubmenu(gIdx, iIdx)"
                    >
                      <span
                        class="flex h-6 w-6 flex-shrink-0 [&>.iconify]:h-6 [&>.iconify]:w-6"
                        :class="openSubmenus[subKey(gIdx, iIdx)] ? 'menu-item-icon-active' : 'menu-item-icon-inactive'"
                      >
                        <Icon :icon="iconifyName(item.icon)" />
                      </span>
                      <span
                        v-show="sidebar.isExpanded || sidebar.isHovered || sidebar.isMobileOpen"
                        class="menu-item-text flex items-center gap-2"
                      >
                        {{ item.name }}
                      </span>
                      <Icon
                        v-show="sidebar.isExpanded || sidebar.isHovered || sidebar.isMobileOpen"
                        icon="mdi:chevron-down"
                        class="ml-auto h-5 w-5 transition-transform duration-200"
                        :class="{ 'rotate-180 text-brand-500': openSubmenus[subKey(gIdx, iIdx)] }"
                      />
                    </button>
                    <div
                      v-show="openSubmenus[subKey(gIdx, iIdx)] && (sidebar.isExpanded || sidebar.isHovered || sidebar.isMobileOpen)"
                      class="mt-2 ml-9 space-y-1"
                    >
                      <ul class="space-y-1">
                        <li v-for="sub in item.subItems" :key="sub.path">
                          <Link
                            :href="sub.path"
                            class="menu-dropdown-item"
                            :class="isActive(sub.path) ? 'menu-dropdown-item-active' : 'menu-dropdown-item-inactive'"
                          >
                            {{ sub.name }}
                          </Link>
                        </li>
                      </ul>
                    </div>
                  </template>
                  <!-- Item simple (sin submenú) -->
                  <Link
                    v-else
                    :href="item.path"
                    class="menu-item group"
                    :class="[
                      isActive(item.path) ? 'menu-item-active' : 'menu-item-inactive',
                      !sidebar.isExpanded && !sidebar.isHovered && !sidebar.isMobileOpen
                        ? 'xl:justify-center'
                        : 'justify-start',
                    ]"
                  >
                    <span
                      class="flex h-6 w-6 flex-shrink-0 [&>.iconify]:h-6 [&>.iconify]:w-6"
                      :class="isActive(item.path) ? 'menu-item-icon-active' : 'menu-item-icon-inactive'"
                    >
                      <Icon :icon="iconifyName(item.icon)" />
                    </span>
                    <span
                      v-show="sidebar.isExpanded || sidebar.isHovered || sidebar.isMobileOpen"
                      class="menu-item-text flex items-center gap-2"
                    >
                      {{ item.name }}
                    </span>
                  </Link>
                </li>
              </ul>
            </div>
          </div>
        </nav>

        <!-- Sidebar Widget (como layouts/sidebar-widget.blade.php) -->
        <div
          v-show="sidebar.isExpanded || sidebar.isHovered || sidebar.isMobileOpen"
          class="mt-auto"
        >
          <div class="mx-auto mb-10 w-full max-w-60 rounded-2xl bg-gray-50 px-4 py-5 text-center dark:bg-white/[0.03]">
            <p class="text-theme-sm text-gray-600 dark:text-gray-400">
              Desarrollado por Factosys Perú <span aria-hidden="true">🐿️</span>
            </p>
          </div>
        </div>
      </div>
    </aside>

    <!-- Main content area -->
    <div
      class="flex-1 transition-all duration-300 ease-in-out"
      :class="{
        'xl:ml-[290px]': sidebar.isExpanded || sidebar.isHovered,
        'xl:ml-[90px]': !sidebar.isExpanded && !sidebar.isHovered,
        'ml-0': sidebar.isMobileOpen,
      }"
    >
      <!-- App Header (como layouts/app-header.blade.php) -->
      <header
        class="sticky top-0 z-[99999] flex w-full border-b border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900 xl:border-b"
      >
        <div class="flex grow flex-col items-center justify-between xl:flex-row xl:px-6">
          <div
            class="flex w-full items-center justify-between gap-2 border-b border-gray-200 px-3 py-3 dark:border-gray-800 sm:gap-4 xl:justify-normal xl:border-b-0 xl:px-0 lg:py-4"
          >
            <!-- Desktop sidebar toggle -->
            <button
              class="hidden h-10 w-10 items-center justify-center rounded-lg border border-gray-200 text-gray-500 dark:border-gray-800 dark:text-gray-400 xl:flex lg:h-11 lg:w-11"
              :class="{ 'bg-gray-100 dark:bg-white/[0.03]': !sidebar.isExpanded }"
              @click="sidebar.toggleExpanded()"
              aria-label="Toggle Sidebar"
            >
              <Icon v-show="!sidebar.isMobileOpen" icon="mdi:menu" class="h-5 w-5" />
              <Icon v-show="sidebar.isMobileOpen" icon="mdi:close" class="h-6 w-6" />
            </button>
            <!-- Mobile menu toggle -->
            <button
              class="flex h-10 w-10 items-center justify-center rounded-lg text-gray-500 dark:text-gray-400 xl:hidden lg:h-11 lg:w-11"
              :class="{ 'bg-gray-100 dark:bg-white/[0.03]': sidebar.isMobileOpen }"
              @click="sidebar.toggleMobileOpen()"
              aria-label="Toggle Mobile Menu"
            >
              <Icon v-show="!sidebar.isMobileOpen" icon="mdi:menu" class="h-5 w-5" />
              <Icon v-show="sidebar.isMobileOpen" icon="mdi:close" class="h-6 w-6" />
            </button>
            <!-- Logo mobile -->
            <Link href="/" class="flex xl:hidden items-center">
              <img src="/logo_cpromed.jpg" alt="CPROMED PERU" class="h-9 w-auto max-w-[140px] object-contain" />
            </Link>
            <!-- Application menu toggle (mobile) -->
            <button
              type="button"
              class="flex h-10 w-10 items-center justify-center rounded-lg text-gray-700 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-800 xl:hidden z-[99999]"
              @click="isApplicationMenuOpen = !isApplicationMenuOpen"
              aria-label="Menú"
            >
              <Icon icon="mdi:dots-horizontal" class="h-6 w-6" />
            </button>
          </div>

          <!-- Right: theme + user (desktop siempre; mobile solo si isApplicationMenuOpen) -->
          <div
            :class="isApplicationMenuOpen ? 'flex' : 'hidden'"
            class="w-full items-center justify-between gap-4 px-5 py-4 shadow-theme-md xl:flex xl:justify-end xl:px-0 xl:shadow-none"
          >
            <div class="flex items-center gap-2 2xsm:gap-3">
              <!-- Theme toggle (Iconify: sol = modo claro, luna = modo oscuro) -->
              <button
                type="button"
                class="relative flex h-11 w-11 items-center justify-center rounded-full border border-gray-200 bg-white text-gray-500 transition-colors hover:bg-gray-100 hover:text-gray-700 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-white"
                @click="themeToggle"
                aria-label="Cambiar tema"
              >
                <Icon v-show="isDark" icon="mdi:white-balance-sunny" class="h-5 w-5" />
                <Icon v-show="!isDark" icon="mdi:weather-night" class="h-5 w-5" />
              </button>
            </div>
            <UserDropdown />
          </div>
        </div>
      </header>

      <main class="mx-auto max-w-(--breakpoint-2xl) p-4 md:p-6">
        <slot />
      </main>
    </div>
  </div>
</template>

<script setup>
import { computed, reactive, ref, onMounted, onUnmounted } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { Icon } from '@iconify/vue';
import UserDropdown from '../../components/UserDropdown.vue';

const page = usePage();
const menuGroups = computed(() => page.props.menuGroups || []);

const isApplicationMenuOpen = ref(false);

// Theme (como Alpine store theme en layouts/app.blade.php)
const isDark = ref(false);
function getStoredTheme() {
  if (typeof window === 'undefined') return 'light';
  return localStorage.getItem('theme') || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
}
function applyTheme(theme) {
  if (typeof document === 'undefined') return;
  const html = document.documentElement;
  const body = document.body;
  if (!body) return;
  if (theme === 'dark') {
    html.classList.add('dark');
    body.classList.add('dark', 'bg-gray-900');
    isDark.value = true;
  } else {
    html.classList.remove('dark');
    body.classList.remove('dark', 'bg-gray-900');
    isDark.value = false;
  }
}
function themeToggle() {
  const next = isDark.value ? 'light' : 'dark';
  localStorage.setItem('theme', next);
  applyTheme(next);
}

// Sidebar state (como Alpine store sidebar)
const sidebar = reactive({
  isExpanded: typeof window !== 'undefined' && window.innerWidth >= 1280,
  isMobileOpen: false,
  isHovered: false,
  toggleExpanded() {
    this.isExpanded = !this.isExpanded;
    this.isMobileOpen = false;
  },
  toggleMobileOpen() {
    this.isMobileOpen = !this.isMobileOpen;
  },
  setMobileOpen(val) {
    this.isMobileOpen = !!val;
  },
  setHovered(val) {
    if (typeof window !== 'undefined' && window.innerWidth >= 1280 && !this.isExpanded) {
      this.isHovered = !!val;
    }
  },
});

const openSubmenus = reactive({});
function subKey(gIdx, iIdx) {
  return `${gIdx}-${iIdx}`;
}
function toggleSubmenu(gIdx, iIdx) {
  const key = subKey(gIdx, iIdx);
  const newState = !openSubmenus[key];
  if (newState) {
    Object.keys(openSubmenus).forEach((k) => { openSubmenus[k] = false; });
  }
  openSubmenus[key] = newState;
}

function onSidebarEnter() {
  if (!sidebar.isExpanded) sidebar.setHovered(true);
}

function isActive(path) {
  if (typeof window === 'undefined') return false;
  const current = (window.location.pathname || '').replace(/^\//, '');
  const p = (path || '').replace(/^\//, '');
  return current === p || current.startsWith(p + '/');
}

// Iconify: mapeo de claves del menú a nombres Iconify (https://iconify.design)
function iconifyName(name) {
  const map = {
    dashboard: 'mdi:view-dashboard',
    ecommerce: 'mdi:cart',
    forms: 'mdi:clipboard-text',
    pill: 'mdi:pill',
  };
  return map[name] || 'mdi:circle';
}

let resizeHandler = null;
onMounted(() => {
  const theme = getStoredTheme();
  applyTheme(theme);
  sidebar.isExpanded = window.innerWidth >= 1280;
  resizeHandler = () => {
    if (window.innerWidth < 1280) {
      sidebar.setMobileOpen(false);
      sidebar.isExpanded = false;
    } else {
      sidebar.setMobileOpen(false);
      sidebar.isExpanded = true;
    }
  };
  window.addEventListener('resize', resizeHandler);
});
onUnmounted(() => {
  if (resizeHandler) window.removeEventListener('resize', resizeHandler);
});
</script>
