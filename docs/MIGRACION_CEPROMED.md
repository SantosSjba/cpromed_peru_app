# Plan de migración a TailAdmin – Cepromed Perú

Migración del frontend (Vue/topico_frontend_v2) y backend (Node/Express/MongoDB – topico_backend_v2) a una única aplicación Laravel con plantilla **TailAdmin**, usando la base de datos MySQL de cPanel.

---

## Resumen de proyectos origen

| Proyecto | Stack | Funcionalidad actual |
|----------|--------|----------------------|
| **topico_backend_v2** | Node, Express, TypeScript, MongoDB, JWT | Auth (registro/login), modelo User (name, email, password, description), modelo Storage (archivos) |
| **topico_frontend_v2** | Vue 3, Vite, Pinia, Tailwind | Login funcional, registro solo UI, dashboard con menú: Pacientes, Citas, Médicos, Registros, Farmacia, Inventario |
| **tailadmin-app_cepromed_peru** | Laravel 12, Blade, Alpine.js, Tailwind 4, Vite | Plantilla con layouts, componentes, vistas de auth (sin lógica) |

---

## Base de datos (cPanel)

- **Base de datos:** `factosys_bd_cpromed`
- **Usuario:** `factosys_cpromed`
- **Contraseña:** configurar en `.env` (no commitear)
- **Host:** En cPanel suele ser `localhost` si la app corre en el mismo servidor. Si usas acceso remoto: `195.250.27.211`
- **Puerto:** `3306`

Configuración en `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=factosys_bd_cpromed
DB_USERNAME=factosys_cpromed
DB_PASSWORD=tu_contraseña_cpanel
```

---

## Fases de migración

### Fase 1 – Entorno y base de datos ✅

- [x] Documentar plan de migración (este archivo).
- [x] Configurar `.env.example` con variables para MySQL de cPanel (`factosys_bd_cpromed`, `factosys_cpromed`).
- [x] Añadir migración para columna `description` en `users` (compatibilidad con backend actual).
- [x] Crear migración para tabla `storage` (file_name, user_id, path, timestamps).
- [ ] Ejecutar migraciones en entorno local: `php artisan migrate` (y en cPanel cuando despliegues).

**Entregables:** `.env.example` actualizado, migraciones creadas.

---

### Fase 2 – Autenticación en Laravel ✅

- [x] Crear `App\Http\Controllers\Auth\AuthController` (login, register, logout).
- [x] Rutas POST: `/login`, `/register`, `/logout`.
- [x] Validación de registro (nombre, email, contraseña, confirmación) y login (email, contraseña).
- [x] Usar guard `web` (sesión) y redirección a dashboard tras login.
- [x] Conectar formularios de las vistas TailAdmin (`signin.blade.php`, `signup.blade.php`) con CSRF y mensajes de error.
- [x] Middleware `auth`: rutas del dashboard solo para usuarios autenticados; redirección a `/signin` (`redirectGuestsTo` en `bootstrap/app.php`).
- [x] Redirección: si ya está logueado y accede a `/signin` o `/signup`, enviar a dashboard. Cerrar sesión en header (dropdown usuario).

**Entregables:** Login y registro funcionales con sesión Laravel, rutas protegidas.

---

### Fase 3 – Menú y estructura Cepromed (frontend en TailAdmin) ✅

- [x] Actualizar `MenuHelper` con menú de negocio: Dashboard, Pacientes, Citas, Médicos, Registros, Farmacia, Inventario, Mi Perfil.
- [x] Iconos existentes en `MenuHelper` reutilizados para cada ítem.
- [x] Rutas y vistas placeholder (página blank con título) para cada sección.
- [x] Layout del dashboard usa menú Cepromed; header muestra usuario logueado y cierre de sesión.

**Entregables:** Menú Cepromed en sidebar, rutas y vistas placeholder por módulo.

---

### Fase 4 – Modelo Storage y subida de archivos ✅ (parcial)

- [x] Modelo Eloquent `Storage` (file_name, user_id, path) y relación con `User`.
- [ ] Controlador para subida (y listado si aplica), almacenamiento en `storage/app` o disco configurado.
- [ ] Rutas protegidas para upload (y descarga si se requiere).
- [ ] Revisar políticas de acceso y validación (tipo/tamaño de archivo).

**Entregables:** Modelo y migración listos; CRUD de subida de archivos pendiente en Fase 5 si se necesita.

---

### Fase 5 – Módulos de negocio (CRUD y pantallas)

Implementación por prioridad (según necesidad del proyecto):

1. **Pacientes:** modelo, migración, CRUD, listado, formularios agregar/editar, perfil.
2. **Médicos:** modelo, migración, CRUD, departamentos, turnos.
3. **Citas:** modelo, migración, calendario (vista), reservar/editar cita.
4. **Registros:** nacimiento y defunción (modelos y formularios).
5. **Farmacia:** medicamentos (lista, agregar/editar).
6. **Inventario:** stock y artículos emitidos.

Cada módulo: migraciones, modelos, controladores, rutas, vistas Blade con componentes TailAdmin, validación y permisos si aplica.

---

### Fase 6 – Despliegue y revisión

- [ ] Configurar `.env` en producción (cPanel) con credenciales de BD y `APP_KEY`.
- [ ] Revisar `APP_DEBUG=false`, `APP_ENV=production`, logs.
- [ ] Configurar sesión y caché (database o file según disponibilidad).
- [ ] Probar login, registro, menú y al menos un flujo por módulo implementado.
- [ ] Documentar URL de acceso y usuarios de prueba si se entregan.

---

## Estructura de directorios recomendada (Laravel)

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── Auth/
│   │   │   └── AuthController.php
│   │   ├── DashboardController.php
│   │   ├── Pacientes/
│   │   ├── Citas/
│   │   ├── Medicos/
│   │   └── ...
│   └── Middleware/
├── Models/
│   ├── User.php
│   ├── Storage.php
│   ├── Paciente.php
│   └── ...
app/Helpers/
├── MenuHelper.php
database/migrations/
resources/views/
├── layouts/
├── pages/
│   ├── auth/
│   ├── dashboard/
│   ├── pacientes/
│   ├── citas/
│   └── ...
routes/
├── web.php
└── auth.php (opcional, para agrupar rutas de auth)
```

---

## Checklist general (para no olvidar detalles)

- [ ] Variables sensibles solo en `.env`; `.env` en `.gitignore`.
- [ ] CSRF en todos los formularios Blade.
- [ ] Contraseñas hasheadas (Laravel por defecto).
- [ ] Mensajes de error de validación visibles en signin/signup.
- [ ] Logout cierra sesión y redirige a signin.
- [ ] Menú solo visible cuando hay sesión; ítems con rutas correctas.
- [ ] Iconos y nombres del menú alineados con el frontend actual (Pacientes, Citas, Médicos, etc.).
- [ ] Migraciones reversibles (`down()`).
- [ ] Nombres de tablas/campos coherentes con el backend anterior donde tenga sentido (ej. `description` en users, `storage`).

---

*Documento vivo: actualizar según avance de cada fase.*
