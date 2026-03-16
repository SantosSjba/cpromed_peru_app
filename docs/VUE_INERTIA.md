# Vue.js + Inertia.js en CPROMED

La aplicación está preparada para funcionar con **Vue 3** e **Inertia.js** además de las vistas Blade existentes.

## Estructura

- **Rutas Inertia (Vue):** devuelven `Inertia::render('NombrePagina', $props)`. La primera carga sirve la plantilla raíz `resources/views/app.blade.php` y monta la app Vue; las navegaciones siguientes son SPA (sin recarga).
- **Rutas Blade:** siguen usando `view('...')` y el layout `resources/views/layouts/app.blade.php`, que carga `app-blade.js` (Alpine.js, gráficos, etc.).

## Paquetes instalados

### Composer
- `inertiajs/inertia-laravel` – adaptador servidor Inertia.

### NPM
- `vue` – Vue 3.
- `@vitejs/plugin-vue` – soporte Vue en Vite.
- `@inertiajs/vue3` – adaptador cliente Inertia para Vue 3.

## Archivos clave

| Archivo | Uso |
|--------|-----|
| `resources/views/app.blade.php` | Plantilla raíz de Inertia (solo para respuestas Inertia). |
| `resources/views/layouts/app.blade.php` | Layout Blade (sidebar, header); usa `app-blade.js`. |
| `resources/js/app.js` | Entrada Inertia: `createInertiaApp`, resuelve páginas Vue. |
| `resources/js/app-blade.js` | Entrada Blade: Alpine, ApexCharts, FullCalendar, etc. |
| `resources/js/Pages/*.vue` | Páginas Vue (una por ruta Inertia). |
| `resources/js/Pages/Layouts/AppLayout.vue` | Layout con sidebar y header para páginas autenticadas. |
| `app/Http/Middleware/HandleInertiaRequests.php` | Middleware Inertia; comparte `auth`, `menuGroups`, etc. |

## Cómo convertir una ruta a Vue (Inertia)

1. **Crear la página Vue** en `resources/js/Pages/`, por ejemplo `resources/js/Pages/ConsultaPrecios/Index.vue`.
2. **En el controlador**, sustituir `return view(...)` por:
   ```php
   use Inertia\Inertia;
   return Inertia::render('ConsultaPrecios/Index', [
       'title' => 'Consulta de Precios',
       'departamentos' => $departamentos,
       // ... props que necesite la página
   ]);
   ```
3. En la página Vue usar `defineProps()` para recibir las props y, si aplica, el layout:
   ```vue
   <template>
     <AppLayout>
       <h1>{{ title }}</h1>
       <!-- contenido -->
     </AppLayout>
   </template>
   <script setup>
   import AppLayout from '@/Pages/Layouts/AppLayout.vue';
   defineProps({ title: String, departamentos: Object });
   </script>
   ```

## Rutas ya migradas a Inertia

- `GET /dashboard` → `Dashboard.vue`
- `GET /blank` → `Blank.vue`

El resto de rutas (auth, consulta-precios, notas-venta, historia-clínica, etc.) siguen en Blade y cargan `app-blade.js`.

## Build

```bash
npm run build
```

Se generan ambos bundles:

- `app.js` + chunks Vue/Inertia (para rutas Inertia).
- `app-blade.js` + chunks Alpine/charts (para rutas Blade).

## Desarrollo

```bash
npm run dev
php artisan serve
```

- Al entrar a `/dashboard` o `/blank` se usa la app Vue (Inertia).
- Al entrar a `/consulta-precios`, `/signin`, etc., se usa Blade y Alpine.

## Referencias

- [Inertia.js](https://inertiajs.com/)
- [Laravel + Inertia (server)](https://inertiajs.com/server-side-setup)
- [Vue 3 + Inertia (client)](https://inertiajs.com/client-side-setup)
- [Laravel Vite](https://laravel.com/docs/vite)
