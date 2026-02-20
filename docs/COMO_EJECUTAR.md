# Cómo ejecutar Cepromed (TailAdmin)

## Requisitos

- PHP 8.2+
- Composer
- Node.js 18+ y npm
- MySQL (o acceso a la BD de cPanel)

---

## Pasos (en orden)

### 1. Instalar dependencias PHP

```bash
cd "c:\Users\santo\Documents\FACTOSYS\PROYECTOS GIT LAB\tailadmin-app_cepromed_peru"
composer install
```

### 2. Generar clave de aplicación

El archivo `.env` ya existe. Solo falta generar la clave:

```bash
php artisan key:generate
```

### 3. Migraciones (crear tablas en la BD)

Asegúrate de que la base de datos `factosys_bd_cpromed` exista en MySQL/cPanel y que el usuario tenga permisos. Luego:

```bash
php artisan migrate
```

Si la BD está en un servidor remoto (cPanel), en `.env` usa:

- `DB_HOST=localhost` si la app corre **en el mismo servidor** que MySQL.
- `DB_HOST=195.250.27.211` (o el host que te den en cPanel) si te conectas **desde tu PC** al MySQL remoto.

### 4. Crear usuario administrador (opcional)

```bash
php artisan db:seed --class=AdminUserSeeder
```

- **Correo:** admin@cepromed.peru  
- **Contraseña:** AdminCepromed2025!

### 5. Instalar dependencias del frontend

```bash
npm install
```

### 6. Ejecutar la aplicación

**En Windows** (recomendado, evita el error de Pail/`pcntl`):

```bash
composer run dev:win
```

**En Linux/Mac:**

```bash
composer run dev
```

Esto inicia el servidor Laravel y Vite. Abre en el navegador:

**http://localhost:8000**

---

**Opción B – Dos terminales**

Terminal 1 (servidor Laravel):

```bash
php artisan serve
```

Terminal 2 (assets y recarga en caliente):

```bash
npm run dev
```

Luego abre **http://localhost:8000**.

---

## Resumen rápido (si ya tienes todo instalado)

```bash
composer install
php artisan key:generate
php artisan migrate
npm install
composer run dev:win
```

(En Windows usa `dev:win`; en Linux/Mac puedes usar `composer run dev`.)

Abre **http://localhost:8000**. Si no hay sesión, te redirigirá a **Iniciar sesión**; puedes **Registrarse** y luego entrar al dashboard.

---

## Despliegue en cPanel (PDF y rutas)

### Document root

En cPanel, el **document root** del dominio o subdominio debe apuntar a la carpeta **`public`** del proyecto (no a la raíz del proyecto). Ejemplo: si la app está en `~/cpromedperu`, el document root debe ser `~/cpromedperu/public`.

### Si el PDF da 404

La descarga del PDF usa la URL **`/descargar-nota-pdf?id=2`** (un solo segmento + query string) para que funcione aunque Apache no reescriba bien rutas con varios segmentos. Los botones "PDF" de la app ya abren esta URL.

Si aun así sale 404:

1. **Document root:** En cPanel → Dominios / Subdominios, el directorio del sitio debe ser la carpeta **`public`** del proyecto (no la raíz del proyecto).
2. **mod_rewrite (Apache):** En cPanel busca **"Apache Modules"** o **"Select PHP Version"** → **"Extensions"** / **"Apache"** y asegúrate de que **mod_rewrite** esté activado. Sin esto, Laravel no puede enrutar y casi todo dará 404.
3. **.htaccess:** Dentro de **public** debe existir el **.htaccess** con las reglas que envían las peticiones a `index.php`. Si al subir ves error 500, quita la línea `Options -MultiViews` del .htaccess.
4. **Caché de rutas:** En el servidor (SSH o terminal de cPanel) ejecuta:
   ```bash
   php artisan route:clear
   php artisan config:clear
   php artisan cache:clear
   ```
   Luego prueba de nuevo **Descargar PDF** o el enlace que abre `/descargar-nota-pdf?id=2`.
