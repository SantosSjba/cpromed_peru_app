# Reporte: Migración Backend y Frontend a TailAdmin (Cepromed)

Este documento describe qué existía en **topico_backend_v2** y **topico_frontend_v2** y cómo se replica o debe replicar en la aplicación Laravel TailAdmin para tener **toda la funcionalidad** en un solo proyecto.

---

## 1. Estado actual en TailAdmin (ya migrado)

| Funcionalidad | Backend/Front origen | Estado en TailAdmin |
|---------------|----------------------|---------------------|
| **Login** | Backend: POST /auth/login (JWT). Front: Vue, store Pinia | ✅ Login con sesión Laravel, vista en español, sin Google/X |
| **Registro** | Backend: POST /auth/register. Front: formulario (sin enviar) | ✅ Registro funcional, vista en español, sin Google/X |
| **Logout** | Front: clearAuthData + redirect | ✅ Cerrar sesión en dropdown del header |
| **Usuarios** | Backend: modelo User (name, email, password, description) | ✅ Modelo User + columna description |
| **Storage (archivos)** | Backend: modelo Storage, servicio registerUpload, multer | ✅ Modelo Storage + migración; pendiente controlador de subida |
| **Menú** | Front: Dashboard, Pacientes, Citas, Médicos, Registros, Farmacia, Inventario | ✅ Menú en MenuHelper con rutas placeholder |
| **Dashboard** | Front: layout con sidebar/header, sin contenido | ✅ Dashboard con layout TailAdmin y menú Cepromed |
| **Usuario admin** | No existía | ✅ Seeder `AdminUserSeeder`: admin@cepromed.peru / AdminCepromed2025! |
| **Idioma** | Front: textos en español parcial | ✅ Login/registro 100% español, validaciones en español (lang/es) |

---

## 2. Funcionalidad a implementar (módulos de negocio)

En el frontend Vue solo existía la **estructura de menú**; no había CRUD ni pantallas de datos. Para tener “todo tal cual” y poder **manejar datos, reportes, etc.**, hay que implementar en Laravel los siguientes módulos.

### 2.1 Pacientes

- **Listado:** tabla con pacientes (nombre, documento, fecha nacimiento, contacto, etc.).
- **Crear / Editar:** formulario con datos del paciente.
- **Registro de pacientes:** pantalla de registro (alta masiva o formulario según negocio).
- **Perfil del paciente:** ficha de un paciente con historial o datos ampliados.

**Backend a crear:** modelo `Paciente`, migración, `PacienteController` (index, create, store, edit, update, show, destroy), rutas, vistas Blade con tablas y formularios TailAdmin.

### 2.2 Citas

- **Calendario:** vista de citas por día/semana (usar componente calendario de TailAdmin).
- **Reservar cita:** formulario (paciente, médico, fecha, hora).
- **Ver / Editar cita:** detalle y edición.

**Backend a crear:** modelo `Cita` (relación con Paciente y Médico), migración, controlador, rutas, vistas.

### 2.3 Médicos

- **Listado de médicos:** tabla (nombre, especialidad, departamento, contacto).
- **Crear / Editar médico:** formulario.
- **Asignar departamento:** relación médico–departamento.
- **Gestión de turnos:** horarios o turnos del médico.
- **Perfil médico:** ficha del médico.

**Backend a crear:** modelos `Medico`, `Departamento`, `Turno` (o similar), migraciones, controladores, rutas, vistas.

### 2.4 Registros (civil)

- **Registro de nacimiento:** formulario y listado de registros de nacimiento.
- **Registro de defunción:** formulario y listado de defunciones.

**Backend a crear:** modelos `RegistroNacimiento`, `RegistroDefuncion` (o uno polimórfico), migraciones, controladores, vistas.

### 2.5 Farmacia

- **Lista de medicamentos:** tabla (nombre, presentación, stock, etc.).
- **Agregar medicamento:** formulario de alta.

**Backend a crear:** modelo `Medicamento`, migración, controlador CRUD, vistas.

### 2.6 Inventario

- **Stock de artículos:** listado y movimientos.
- **Artículos emitidos:** registros de salidas.

**Backend a crear:** modelos `Articulo`, `MovimientoInventario` (o similar), migraciones, controladores, vistas.

---

## 3. Reportes y datos

- **Reportes:** definir qué reportes se necesitan (por período, por médico, por paciente, inventario, etc.) e implementarlos como rutas que devuelvan vistas con tablas o export (PDF/Excel) usando Laravel.
- **Manejo de datos:** todo vía Eloquent, validación con Form Request, políticas si hay roles (admin vs otro). Mantener la BD en MySQL (factosys_bd_cpromed).

---

## 4. Orden sugerido de implementación

1. **Pacientes** (modelo base para Citas).
2. **Médicos y departamentos** (para Citas y turnos).
3. **Citas** (calendario + CRUD).
4. **Farmacia** (medicamentos).
5. **Inventario** (artículos y movimientos).
6. **Registros** (nacimiento y defunción).
7. **Reportes** según necesidad.
8. **Subida de archivos (Storage):** controlador y rutas protegidas si se requiere.

---

## 5. Cómo ejecutar el seeder del admin

Después de las migraciones, crear el usuario administrador:

```bash
php artisan db:seed
```

O solo el seeder de admin:

```bash
php artisan db:seed --class=AdminUserSeeder
```

**Credenciales del administrador:**

- **Correo:** admin@cepromed.peru  
- **Contraseña:** AdminCepromed2025!

(Conviene cambiar la contraseña después del primer acceso.)

---

## 6. Resumen

- **Auth, usuarios, menú, idioma y admin** están migrados y en español.
- **Storage** está a nivel de modelo; falta el flujo de subida si se usa.
- **El resto de la funcionalidad** (Pacientes, Citas, Médicos, Registros, Farmacia, Inventario, reportes) debe implementarse en Laravel con modelos, controladores y vistas Blade usando la plantilla TailAdmin, siguiendo el orden anterior para no dejar ningún detalle sin cubrir.
