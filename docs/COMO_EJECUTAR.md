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

La ruta del PDF usa la URL **`/notas-venta/pdf/{id}`** (por ejemplo `https://tudominio.com/notas-venta/pdf/2`) para mejorar compatibilidad con Apache/cPanel. Los enlaces de la app ya generan esta URL.

Si sigue saliendo 404:

1. Comprueba que en cPanel el document root sea la carpeta **public**.
2. Asegúrate de que en **public** exista el archivo **.htaccess** con las reglas de reescritura (RewriteRule hacia `index.php`).
3. Si el servidor no permite `Options -MultiViews` en .htaccess y da error 500, elimina esa línea del **public/.htaccess**.
