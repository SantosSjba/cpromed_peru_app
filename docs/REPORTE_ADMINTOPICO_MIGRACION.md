# Migración admintopico → TailAdmin (Cepromed)

Resumen de la funcionalidad de **admintopicoback-dev_santos** (backend NestJS) y **admintopico-santos_dev** (frontend Vue) migrada al proyecto **tailadmin-app_cepromed_peru** (Laravel + TailAdmin).

---

## Backend admintopico (NestJS + MongoDB)

| Módulo        | Endpoints / funcionalidad | Estado en TailAdmin |
|---------------|---------------------------|----------------------|
| **Auth**      | POST /auth/login, POST /auth/refresh (JWT access + refresh) | ✅ Login/registro con sesión Laravel (sin JWT; equivalente funcional) |
| **Users**     | CRUD: POST/GET/PATCH/DELETE /users | ⚠️ Modelo User existe; CRUD de usuarios en admin no implementado (opcional) |
| **PDF**       | POST /pdf/generate (template + data), GET /pdf/templates | ✅ Módulo **Notas de venta**: guardado en BD + generación PDF (Blade + DomPDF) |

---

## Frontend admintopico (Vue 3 + Pinia)

| Pantalla / flujo | Descripción | Estado en TailAdmin |
|-------------------|-------------|----------------------|
| **Login**         | Email/password, enlace registro | ✅ Vista en español, sin Google/X |
| **Registro**      | Placeholder en Vue | ✅ Registro funcional en español |
| **Dashboard**     | Home con mensaje bienvenida | ✅ Dashboard con menú |
| **Notas de venta**| Listado + botón «Nueva Nota Venta», modal con formulario (negocio, boleta, cliente, detalles, totales), «Guardar» → genera PDF y descarga | ✅ **Completo**: listado, crear nota, guardar en BD, descargar PDF (formato A4, misma estructura que notesale) |
| **Perfil**        | Placeholder | ✅ Ruta /profile (vista TailAdmin) |
| **Configuración** | Placeholder | ✅ Se puede usar /form-elements o añadir vista luego |

---

## Implementado en TailAdmin

### 1. Notas de venta (módulo nuevo)

- **Tabla:** `nota_ventas` (user_id, numero_documento, ruc, razon_social, direccion, sucursal, cliente JSON, boleta JSON, detalles JSON, subtotal, descuento_total, igv, total, notas).
- **Modelo:** `App\Models\NotaVenta`.
- **Controlador:** `NotaVentaController` (index, create, store, show, pdf).
- **Rutas:** 
  - `GET /notas-venta` → listado
  - `GET /notas-venta/create` → formulario nueva nota
  - `POST /notas-venta` → guardar (opción “Guardar y descargar PDF” redirige a la descarga)
  - `GET /notas-venta/{id}` → ver detalle
  - `GET /notas-venta/{id}/pdf` → descargar PDF
- **Vistas:** 
  - `pages/notas-venta/index.blade.php` (listado con enlaces Ver y PDF)
  - `pages/notas-venta/create.blade.php` (formulario: negocio, boleta, cliente, detalles dinámicos, totales)
  - `pages/notas-venta/show.blade.php` (detalle y botón “Descargar PDF”)
  - `pdf/nota-venta.blade.php` (plantilla PDF, equivalente a notesale del backend NestJS)
- **Menú:** ítem «Notas de venta» en el sidebar (ruta `/notas-venta`).
- **PDF:** paquete `barryvdh/laravel-dompdf`; generación con vista Blade y descarga con `Pdf::loadView(...)->download(...)`.

### 2. Auth y usuarios

- Login, registro y cierre de sesión ya existían; se mantienen en español y sin redes sociales.
- Usuario admin por seeder (`AdminUserSeeder`: admin@cepromed.peru / AdminCepromed2025!).

### 3. Estructura de datos de una nota de venta

- **Negocio:** RUC, razón social, dirección, sucursal.
- **Boleta:** número, fecha emisión, fecha vencimiento, moneda, forma de pago.
- **Cliente:** nombre, DNI/RUC, dirección.
- **Detalles:** filas con descripción, cantidad, precio unitario, descuento unitario, importe total (calculado).
- **Totales:** descuento total, subtotal, IGV, total (calculados en el formulario con Alpine.js y validados en el servidor).

---

## Cómo usar

1. Instalar dependencias (incluye DomPDF). Si en Windows falla la extracción de paquetes, ejecutar en una terminal con permisos o desde otra carpeta y luego copiar `vendor`:
   ```bash
   composer install
   ```
   O solo DomPDF: `composer require barryvdh/laravel-dompdf`
2. Ejecutar migraciones (incluye `nota_ventas`):
   ```bash
   php artisan migrate
   ```
3. En el menú de la app, entrar a **Notas de venta** → **Nueva nota de venta**, completar el formulario y usar **Guardar** o **Guardar y descargar PDF**.

---

## Pendiente / opcional

- **CRUD de usuarios** en el panel (listar/crear/editar usuarios), si se desea paridad total con el backend NestJS.
- **Ticket 58/80mm:** en admintopico existe plantilla `ticketsale`; en TailAdmin solo está la nota A4. Se puede añadir una segunda vista Blade y otra ruta o parámetro para “formato ticket”.
- **Refresh token:** en el backend NestJS hay JWT con refresh; en Laravel no es necesario al usar sesión web.
