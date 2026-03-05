# Plan por fases: Módulo Historia Clínica

## Resumen del sistema actual

- **Backend:** Laravel (arquitectura limpia). Rutas en `routes/web.php`, controladores en `app/Http/Controllers/`, modelos en `app/Models/`.
- **Frontend:** Blade + Vite + Alpine.js, componentes en `resources/views/components/` y páginas en `resources/views/pages/`.
- **Menú:** Definido en `app/Helpers/MenuHelper.php`, renderizado en `resources/views/layouts/sidebar.blade.php`.
- **Patrón de referencia:** Módulo **Notas de venta** (listado con tabla, crear con formulario por secciones, validación en controlador, `user_id` en tablas).
- **UI a reutilizar:** `<x-common.page-breadcrumb>`, clases `input-field`, bloques de error, tablas con estilos de `notas-venta/index`, formularios por secciones como `notas-venta/create`, componente dropzone para archivos (`components/form/form-elements/dropzone.blade.php`).

---

## Datos a manejar (formulario + estándares)

### Del formulario solicitado

| Sección | Campos |
|--------|--------|
| **Datos personales** | Nombres, Apellidos, Fecha nacimiento, Género, Dirección, N.º Celular, Ocupación, DNI, Edad |
| **Antecedentes** | Antecedentes Médicos, Antecedentes Personales, Antecedentes Familiares |
| **Historia** | Enfermedades Previas, Cirugías (Sí/No + detalle), Alergias, Medicamentos Actuales |
| **Consulta** | Motivo de Consulta, Enfermedad Actual, Dx, Tx, Plan Dx., Recomendaciones |

### Campos adicionales recomendados (estándares HCE / C-CDA / práctica clínica)

- **Por paciente/ficha:** Grupo sanguíneo, Email, Lugar de nacimiento (opcional).
- **Por consulta/evolución:** Signos vitales: Peso, Talla, IMC (calculado), Presión arterial (sistólica/diastólica), Temperatura, Frecuencia cardíaca (opcionales para futuras fases).

En la Fase 1 se implementan los campos del formulario solicitado; grupo sanguíneo y email se pueden añadir en la misma fase. Signos vitales por consulta se dejan para una fase posterior.

---

## Esquema de base de datos (tablas y relaciones)

Se usan **claves foráneas** y tablas normalizadas.

```
users (existente)
  │
  ├── pacientes (1:N) ...................... Datos demográficos del paciente
  │       │
  │       ├── historia_clinica_ficha (1:1) .. Antecedentes y datos base (una ficha por paciente)
  │       ├── historia_clinica_consultas (1:N) .. Cada consulta/evolución (motivo, Dx, Tx, etc.)
  │       └── paciente_examenes (1:N) ....... Archivos de exámenes (varios por paciente)
```

### Tabla: `pacientes`

| Columna | Tipo | Notas |
|---------|------|--------|
| id | bigint PK | |
| user_id | FK users | Quién registra (cascadeOnDelete) |
| nombres | string | |
| apellidos | string | |
| fecha_nacimiento | date | |
| genero | string (M/F/Otro) | |
| direccion | string nullable | |
| celular | string nullable | |
| ocupacion | string nullable | |
| dni | string nullable, index | |
| edad | unsignedTinyInteger nullable | Opcional; puede calcularse desde fecha_nacimiento |
| email | string nullable | Recomendado estándar |
| grupo_sanguineo | string nullable | Recomendado estándar |
| timestamps | | |

### Tabla: `historia_clinica_ficha`

Una fila por paciente (relación 1:1 con `pacientes`).

| Columna | Tipo | Notas |
|---------|------|--------|
| id | bigint PK | |
| paciente_id | FK pacientes UNIQUE | cascadeOnDelete |
| antecedentes_medicos | text nullable | |
| antecedentes_personales | text nullable | |
| antecedentes_familiares | text nullable | |
| enfermedades_previas | text nullable | |
| cirugias_si_no | boolean | Sí = true, No = false |
| cirugias_detalle | text nullable | |
| alergias | text nullable | |
| medicamentos_actuales | text nullable | |
| timestamps | | |

### Tabla: `historia_clinica_consultas`

Varias consultas/evoluciones por paciente.

| Columna | Tipo | Notas |
|---------|------|--------|
| id | bigint PK | |
| paciente_id | FK pacientes | cascadeOnDelete |
| fecha_consulta | date/datetime | |
| motivo_consulta | text nullable | |
| enfermedad_actual | text nullable | |
| dx | text nullable | Diagnóstico |
| tx | text nullable | Tratamiento |
| plan_dx | text nullable | Plan diagnóstico |
| recomendaciones | text nullable | |
| timestamps | | |

### Tabla: `paciente_examenes`

Varios archivos por paciente (exámenes de laboratorio, imágenes, etc.).

| Columna | Tipo | Notas |
|---------|------|--------|
| id | bigint PK | |
| paciente_id | FK pacientes | cascadeOnDelete |
| user_id | FK users | Quién subió (cascadeOnDelete) |
| path | string | Ruta en disco (storage) |
| file_name | string | Nombre original |
| tipo | string nullable | ej. "laboratorio", "imagen", "otro" |
| fecha_examen | date nullable | |
| descripcion | string nullable | |
| timestamps | | |

---

## Fases de implementación

### Fase 1: Base de datos y menú

**Objetivo:** Crear migraciones, modelos y añadir “Historia clínica” al menú.

1. **Migraciones**
   - `create_pacientes_table`: campos listados arriba, `user_id` FK.
   - `create_historia_clinica_ficha_table`: `paciente_id` FK unique.
   - `create_historia_clinica_consultas_table`: `paciente_id` FK.
   - `create_paciente_examenes_table`: `paciente_id` y `user_id` FK.
   - Ejecutar `php artisan migrate`.

2. **Modelos Eloquent**
   - `Paciente`: fillable, casts (fecha_nacimiento => date), `belongsTo(User)`, `hasOne(HistoriaClinicaFicha)`, `hasMany(HistoriaClinicaConsulta)`, `hasMany(PacienteExamen)`.
   - `HistoriaClinicaFicha`: `belongsTo(Paciente)`.
   - `HistoriaClinicaConsulta`: `belongsTo(Paciente)`.
   - `PacienteExamen`: `belongsTo(Paciente)`, `belongsTo(User)`.

3. **Menú**
   - En `MenuHelper::getMainNavItems()` añadir ítem: nombre "Historia clínica", path `/historia-clinica`, icono p. ej. `user-profile` o uno tipo “formulario” (`forms`).
   - Verificar en `layouts/sidebar.blade.php` que se muestre el nuevo ítem.

**Entregables:** Migraciones ejecutadas, 4 modelos, menú con “Historia clínica”.

---

### Fase 2: Listado de pacientes (Historia clínica)

**Objetivo:** Página que lista pacientes y permite ir a ver/editar historia o crear uno nuevo.

1. **Rutas**
   - En `routes/web.php` (dentro de `middleware('auth')`): recurso o rutas concretas para historia clínica, por ejemplo:
     - `GET /historia-clinica` → listado de pacientes.
     - `GET /historia-clinica/crear` → formulario nuevo paciente + ficha + primera consulta.
     - `GET /historia-clinica/{paciente}` → ver paciente (ficha + consultas + exámenes).
     - Resto en fases siguientes.

2. **Controlador**
   - `HistoriaClinicaController` (o `PacienteController` si se prefiere nombre por entidad).
   - `index()`: `Paciente::where('user_id', Auth::id())->with('historiaClinicaFicha')->orderByDesc('created_at')->paginate(15)`, vista `pages.historia-clinica.index`.

3. **Vista index**
   - Extender `layouts.app`, usar `<x-common.page-breadcrumb :pageTitle="$title" />`.
   - Misma estructura que `notas-venta/index`: título, botón “Nuevo paciente / Nueva historia clínica”, tabla con columnas: Nombres, Apellidos, DNI, Celular, Fecha registro, Acciones (Ver, luego Editar).
   - Paginación y mensajes flash success/error.
   - Estilos de tabla y botones alineados con el resto de la app.

**Entregables:** Rutas, controlador `index`, vista listado con UI existente.

---

### Fase 3: Crear paciente + ficha + primera consulta

**Objetivo:** Formulario único que cree paciente, su ficha de antecedentes y la primera consulta (motivo, enfermedad actual, Dx, Tx, plan, recomendaciones).

1. **Controlador**
   - `create()`: devolver vista con formulario (sin paciente).
   - `store(Request $request)`:
     - Validar todos los campos (paciente, ficha, primera consulta).
     - Crear en orden: `Paciente` → `HistoriaClinicaFicha` → `HistoriaClinicaConsulta`.
     - Redirect a `historia-clinica.show` o `historia-clinica.index` con mensaje success.

2. **Vista create**
   - Un solo formulario por secciones (como en notas-venta):
     - **Datos personales:** nombres, apellidos, fecha nacimiento, género, dirección, celular, ocupación, DNI, edad (opcional), email, grupo sanguíneo.
     - **Antecedentes:** antecedentes médicos, personales, familiares (textarea).
     - **Historia:** enfermedades previas (textarea), Cirugías (radio Sí/No + textarea detalle), alergias, medicamentos actuales (textarea).
     - **Primera consulta:** motivo de consulta, enfermedad actual, Dx, Tx, Plan Dx., Recomendaciones (textarea).
   - Inputs con clase `input-field`, labels al estilo del proyecto, bloques de error con `$errors`.
   - Botón enviar “Guardar historia clínica”.

**Entregables:** `create`/`store`, vista de alta completa usando componentes y estilos existentes.

---

### Fase 4: Ver y editar paciente / ficha / consultas

**Objetivo:** Pantalla de detalle y edición de paciente, ficha y listado de consultas.

1. **Controlador**
   - `show(Paciente $paciente)`: comprobar `$paciente->user_id === Auth::id()`, cargar `historiaClinicaFicha`, `historiaClinicaConsultas`, `pacienteExamenes`. Vista `pages.historia-clinica.show`.
   - `edit(Paciente $paciente)`: mismo scope, vista con formulario de edición (paciente + ficha).
   - `update(Request $request, Paciente $paciente)`: validar y actualizar paciente y ficha (no eliminar consultas ni exámenes).

2. **Vista show**
   - Breadcrumb, nombre del paciente.
   - Secciones: Datos personales (solo lectura o enlace a editar), Antecedentes (ficha), Listado de consultas (tabla: fecha, motivo, Dx resumido, enlace “Ver”), Exámenes (lista de archivos con enlace descarga).
   - Botón “Nueva consulta” y “Subir examen” (rutas de fases siguientes).

3. **Vista edit**
   - Mismo esquema de secciones que create, pre-rellenado para paciente y ficha. POST a `update`.

**Entregables:** `show`, `edit`, `update`, vistas show y edit con UI existente.

---

### Fase 5: Módulo de consultas (evoluciones)

**Objetivo:** Añadir y ver consultas/evoluciones por paciente.

1. **Rutas**
   - `GET /historia-clinica/{paciente}/consultas/crear` → formulario nueva consulta.
   - `POST /historia-clinica/{paciente}/consultas` → guardar.
   - `GET /historia-clinica/{paciente}/consultas/{consulta}` → ver una consulta.

2. **Controlador**
   - Métodos en el mismo controlador o `HistoriaClinicaConsultaController`: `store` (crear consulta), `show` (ver una consulta).
   - Validación: fecha_consulta, motivo_consulta, enfermedad_actual, dx, tx, plan_dx, recomendaciones.

3. **Vistas**
   - Formulario nueva consulta: mismos campos que “Primera consulta”, reutilizando estilos.
   - Vista detalle de una consulta: solo lectura de todos los campos.

**Entregables:** Alta y visualización de consultas por paciente.

---

### Fase 6: Archivos de exámenes

**Objetivo:** Subir, listar y descargar exámenes por paciente.

1. **Almacenamiento**
   - Usar `Storage::disk('local')` o `public` (ej. `paciente_examenes/{paciente_id}/`) y guardar en BD: path, file_name, tipo, fecha_examen, descripcion, user_id.

2. **Controlador**
   - `storeExamen(Request $request, Paciente $paciente)`: validar archivo (max size, tipos permitidos: pdf, imágenes, etc.), guardar en disco y crear registro en `paciente_examenes`.
   - `downloadExamen(PacienteExamen $examen)`: comprobar permisos (paciente pertenece al user) y devolver descarga.

3. **Vistas**
   - En `show` de paciente: sección “Exámenes” con tabla (nombre, tipo, fecha, descripción, descarga).
   - Formulario “Subir examen”: input file o componente dropzone existente (adaptado para múltiples archivos y tipos PDF/imagen), campos opcionales tipo, fecha_examen, descripción.
   - Aceptar múltiples archivos en una sola petición o uno por uno según UX elegida.

**Entregables:** Subida, listado y descarga de exámenes usando UI existente (p. ej. dropzone).

---

### Fase 7: Ajustes y build

**Objetivo:** Validación final, permisos y compilación.

1. Revisar que todas las rutas estén protegidas por `auth` y que solo se acceda a pacientes del usuario.
2. Revisar mensajes de validación en español y nombres de atributos en `$request->validate()`.
3. Ejecutar `npm run build` y corregir errores si aparecen.
4. Actualizar `docs/COMO_EJECUTAR.md` si se añaden pasos específicos para historia clínica (p. ej. permisos de storage para exámenes).

---

## Resumen de archivos a crear/modificar

| Fase | Archivos |
|------|----------|
| 1 | Migraciones (4), `app/Models/Paciente.php`, `HistoriaClinicaFicha.php`, `HistoriaClinicaConsulta.php`, `PacienteExamen.php`, `MenuHelper.php` |
| 2 | `routes/web.php`, `HistoriaClinicaController.php` (index), `resources/views/pages/historia-clinica/index.blade.php` |
| 3 | `HistoriaClinicaController` (create, store), `resources/views/pages/historia-clinica/create.blade.php` |
| 4 | `HistoriaClinicaController` (show, edit, update), `show.blade.php`, `edit.blade.php` |
| 5 | Rutas consultas, controlador consultas, vistas crear/ver consulta |
| 6 | Rutas exámenes, métodos storeExamen/downloadExamen, vista parcial exámenes + formulario subida (dropzone) |
| 7 | Revisión permisos, mensajes, `npm run build`, documentación |

---

## UI y componentes a reutilizar

- **Layout:** `layouts.app`, `@section('content')`.
- **Breadcrumb:** `<x-common.page-breadcrumb :pageTitle="$title" />`.
- **Formularios:** `input-field`, grid `grid-cols-1 sm:grid-cols-2 lg:grid-cols-4`, labels `mb-1.5 block text-sm font-semibold text-gray-800 dark:text-gray-200`.
- **Textareas:** misma clase y contenedor que en formularios existentes.
- **Tablas:** estructura y clases de `notas-venta/index` (thead, tbody, bordes, hover).
- **Botones:** estilos de “Nueva nota de venta” y enlaces Ver/Editar/Eliminar.
- **Alertas:** bloques success/error con `session()` y `$errors->all()`.
- **Archivos:** lógica y estilos del componente `form/form-elements/dropzone.blade.php`, adaptando `accept` y subida vía formulario o AJAX al controlador.

Con este plan se cubre el flujo completo: menú, listado, alta de paciente con ficha y primera consulta, edición, consultas posteriores y exámenes, manteniendo la UI ya existente y la estructura de BD con tablas y claves foráneas bien definidas.
