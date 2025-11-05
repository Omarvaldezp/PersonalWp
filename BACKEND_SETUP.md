# 🚀 Guía de Configuración del Backend PHP + PostgreSQL

Sistema completo de backend para el sitio académico de Dr. Omar Valdez Palazuelos

## 📋 Tabla de Contenidos

1. [Requisitos Previos](#requisitos-previos)
2. [Configuración de PostgreSQL](#configuración-de-postgresql)
3. [Configuración de PHP](#configuración-de-php)
4. [Instalación del Sistema](#instalación-del-sistema)
5. [Estructura del Proyecto](#estructura-del-proyecto)
6. [API Endpoints](#api-endpoints)
7. [Panel de Administración](#panel-de-administración)
8. [Despliegue en SiteGround](#despliegue-en-siteground)
9. [Solución de Problemas](#solución-de-problemas)

---

## 📦 Requisitos Previos

### En SiteGround (Plan GoGeek):

✅ PHP 8.0 o superior
✅ PostgreSQL 12 o superior
✅ PDO PostgreSQL extension
✅ mod_rewrite habilitado

### Verificar en tu plan:

1. Ir a **Site Tools > Devs > PHP Manager**
2. Verificar versión PHP >= 8.0
3. Verificar extensiones: `pdo_pgsql`, `pgsql`, `json`

---

## 🗄️ Configuración de PostgreSQL

### Paso 1: Crear Base de Datos en SiteGround

1. Ir a **Site Tools > PostgreSQL > Databases**
2. Click en **"Create Database"**
3. Configurar:
   ```
   Database Name: omarvaldez_db  (o el que prefieras)
   User: omarvaldez_admin
   Password: [genera una contraseña segura]
   ```
4. **Anotar** estos datos, los necesitarás después

### Paso 2: Ejecutar el Schema SQL

#### Opción A: Desde phpPgAdmin (en SiteGround)

1. Ir a **Site Tools > PostgreSQL > phpPgAdmin**
2. Seleccionar tu base de datos
3. Click en **SQL** tab
4. Copiar TODO el contenido de `database/schema.sql`
5. Pegar y ejecutar

#### Opción B: Desde SSH (si tienes acceso)

```bash
psql -U omarvaldez_admin -d omarvaldez_db -f database/schema.sql
```

### Paso 3: Verificar Instalación

Ejecuta este query en phpPgAdmin para verificar:

```sql
SELECT table_name
FROM information_schema.tables
WHERE table_schema = 'public';
```

Deberías ver estas tablas:
- `usuarios`
- `blog_posts`
- `cursos`
- `investigaciones`
- `contactos`
- `newsletter_suscriptores`
- `sesiones`
- `configuracion`
- `analytics`

---

## ⚙️ Configuración de PHP

### Paso 1: Copiar Archivo de Configuración

```bash
cd src/api/config/
cp config.example.php config.php
```

### Paso 2: Editar Credenciales

Abre `src/api/config/config.php` y actualiza:

```php
<?php
// Credenciales de tu base de datos PostgreSQL
define('DB_HOST', 'localhost');  // O la IP que te dio SiteGround
define('DB_PORT', '5432');
define('DB_NAME', 'omarvaldez_db');  // Tu nombre de base de datos
define('DB_USER', 'omarvaldez_admin');  // Tu usuario
define('DB_PASSWORD', 'tu_password_aqui');  // Tu contraseña

// Entorno
define('APP_ENV', 'production');  // 'development' para testing
define('APP_DEBUG', false);  // true en desarrollo

// Seguridad - IMPORTANTE: Genera una clave única
define('JWT_SECRET_KEY', 'REEMPLAZA_CON_CLAVE_SEGURA');

// URL de tu sitio
define('APP_URL', 'https://omarvaldez.com');
```

### Paso 3: Generar Clave Secreta

Para `JWT_SECRET_KEY`, genera una clave única:

```bash
# En terminal local:
openssl rand -base64 32
```

O usa este generador online: https://generate-secret.vercel.app/32

### Paso 4: Verificar Permisos

```bash
# En SiteGround File Manager, asegurar que estos archivos NO sean legibles públicamente
chmod 640 src/api/config/config.php
```

---

## 🛠️ Instalación del Sistema

### Usuario Admin por Defecto

El schema SQL crea automáticamente un usuario admin:

```
Username: admin
Email: omar@omarvaldez.com
Password: admin123
```

⚠️ **IMPORTANTE**: Cambia esta contraseña inmediatamente después del primer login.

### Cambiar Password del Admin

Puedes hacerlo desde phpPgAdmin:

```sql
UPDATE usuarios
SET password_hash = crypt('tu_nuevo_password', gen_salt('bf'))
WHERE username = 'admin';
```

O crear un script temporal PHP:

```php
<?php
require_once 'src/api/config/Database.php';
$db = Database::getInstance();
$newPassword = 'tu_nuevo_password_seguro';
$hash = password_hash($newPassword, PASSWORD_BCRYPT);
$db->update(
    "UPDATE usuarios SET password_hash = :hash WHERE username = 'admin'",
    [':hash' => $hash]
);
echo "Password actualizado exitosamente";
```

---

## 📁 Estructura del Proyecto

```
/src/
├── api/                          # Backend API REST
│   ├── config/
│   │   ├── config.example.php    # Plantilla de configuración
│   │   ├── config.php            # TU CONFIGURACIÓN (gitignored)
│   │   ├── Database.php          # Clase de conexión PostgreSQL
│   │   └── cors.php              # Headers CORS
│   ├── models/
│   │   ├── Blog.php              # Modelo de blog posts
│   │   ├── Course.php            # Modelo de cursos
│   │   └── Research.php          # Modelo de investigaciones
│   ├── controllers/
│   │   ├── blog.php              # API endpoints blog
│   │   ├── courses.php           # API endpoints cursos
│   │   ├── research.php          # API endpoints investigaciones
│   │   ├── contact.php           # API contacto
│   │   └── newsletter.php        # API newsletter
│   ├── auth/
│   │   ├── Auth.php              # Clase de autenticación
│   │   └── login.php             # API login/logout
│   └── utils/
│       └── Response.php          # Utilidades para responses JSON
│
├── admin/                        # Panel de Administración
│   ├── index.php                 # Dashboard
│   ├── login.php                 # Página de login
│   └── assets/
│       └── admin.css             # Estilos del admin
│
├── index.html                    # Frontend (ya existente)
├── main.js                       # JavaScript frontend
└── styles/
    └── main.css                  # Estilos frontend

/database/
└── schema.sql                    # Schema PostgreSQL completo
```

---

## 🌐 API Endpoints

### Blog Posts

```
GET    /api/controllers/blog.php                    - Listar posts
GET    /api/controllers/blog.php?id=123             - Obtener por ID
GET    /api/controllers/blog.php?slug=titulo        - Obtener por slug
GET    /api/controllers/blog.php?categoria=ai       - Filtrar por categoría
GET    /api/controllers/blog.php?action=search&q=blockchain - Buscar
GET    /api/controllers/blog.php?action=categories  - Listar categorías
POST   /api/controllers/blog.php                    - Crear post (auth)
PUT    /api/controllers/blog.php?id=123             - Actualizar (auth)
DELETE /api/controllers/blog.php?id=123             - Eliminar (auth)
```

### Cursos

```
GET    /api/controllers/courses.php                 - Listar cursos
GET    /api/controllers/courses.php?id=123          - Por ID
GET    /api/controllers/courses.php?nivel=intermedio - Filtrar por nivel
GET    /api/controllers/courses.php?action=featured - Cursos destacados
POST   /api/controllers/courses.php                 - Crear (auth)
PUT    /api/controllers/courses.php?id=123          - Actualizar (auth)
DELETE /api/controllers/courses.php?id=123          - Eliminar (auth)
```

### Investigaciones

```
GET    /api/controllers/research.php                - Listar
GET    /api/controllers/research.php?id=123         - Por ID
GET    /api/controllers/research.php?tipo=articulo  - Por tipo
GET    /api/controllers/research.php?action=search&q=bitcoin - Buscar
POST   /api/controllers/research.php                - Crear (auth)
PUT    /api/controllers/research.php?id=123         - Actualizar (auth)
DELETE /api/controllers/research.php?id=123         - Eliminar (auth)
```

### Formularios

```
POST   /api/controllers/contact.php                 - Enviar mensaje
GET    /api/controllers/contact.php                 - Listar contactos (auth)
POST   /api/controllers/newsletter.php              - Suscribirse
GET    /api/controllers/newsletter.php              - Listar suscriptores (auth)
DELETE /api/controllers/newsletter.php?email=xxx    - Darse de baja
```

### Autenticación

```
POST   /api/auth/login.php                          - Login
       Body: {"username": "admin", "password": "admin123"}

POST   /api/auth/login.php?action=logout            - Logout

GET    /api/auth/login.php?action=me                - Usuario actual
```

---

## 🎛️ Panel de Administración

### Acceso

1. **URL**: `https://omarvaldez.com/admin/login.php`
2. **Credenciales por defecto**:
   - Usuario: `admin`
   - Password: `admin123`

### Funcionalidades

- ✅ Dashboard con estadísticas
- ✅ Gestión de Blog Posts
- ✅ Gestión de Cursos
- ✅ Gestión de Investigaciones
- ✅ Ver Contactos
- ✅ Ver Suscriptores Newsletter

### Nota Importante

El panel de administración actual es **básico y funcional**. Puedes expandirlo agregando:

- CRUD completo con formularios
- Paginación
- Búsqueda y filtros
- Upload de imágenes
- Editor WYSIWYG para contenido

---

## 🚀 Despliegue en SiteGround

### GitHub Actions ya está configurado

El workflow existente (`dangerous-clean-slate: true`) subirá automáticamente todos los archivos a `/public_html/`.

### ⚠️ Pasos CRÍTICOS después del deploy:

1. **Crear `config.php` manualmente en el servidor**

   El archivo `config.php` NO se sube a GitHub (está en .gitignore). Debes crearlo manualmente:

   ```
   - Ir a Site Tools > File Manager
   - Navegar a /public_html/src/api/config/
   - Click "New File" → config.php
   - Copiar el contenido de config.example.php
   - Actualizar con tus credenciales reales
   ```

2. **Verificar permisos de archivos**

   ```
   config.php: 640 (solo lectura para el servidor)
   *.php: 644
   directorios: 755
   ```

3. **Crear directorio uploads** (si planeas subir imágenes)

   ```bash
   mkdir src/uploads
   chmod 755 src/uploads
   ```

4. **Probar conexión a base de datos**

   Crea un archivo temporal `test-db.php`:

   ```php
   <?php
   require_once 'src/api/config/Database.php';
   try {
       $db = Database::getInstance();
       echo "✅ Conexión exitosa a PostgreSQL!<br>";
       echo "Versión: " . $db->getVersion();
   } catch (Exception $e) {
       echo "❌ Error: " . $e->getMessage();
   }
   ```

   Visita: `https://omarvaldez.com/test-db.php`

   Si funciona, **borra el archivo** por seguridad.

---

## 🐛 Solución de Problemas

### Error: "Cannot connect to database"

**Causa**: Credenciales incorrectas o PostgreSQL no iniciado

**Solución**:
1. Verificar credenciales en `config.php`
2. En Site Tools > PostgreSQL > Databases, verificar que la DB esté activa
3. Probar conexión con phpPgAdmin

### Error: "Call to undefined function pg_connect"

**Causa**: Extensión PostgreSQL no habilitada

**Solución**:
1. Site Tools > Devs > PHP Manager
2. Extensiones → Habilitar `pdo_pgsql` y `pgsql`
3. Guardar y reiniciar PHP

### Error 500 en APIs

**Causa**: Error de PHP no visible

**Solución**:
1. Cambiar en `config.php`:
   ```php
   define('APP_ENV', 'development');
   define('APP_DEBUG', true);
   ```
2. Ver el error completo
3. Volver a cambiar a `production` después de solucionar

### La API retorna HTML en lugar de JSON

**Causa**: Sesión ya iniciada o warning de PHP

**Solución**:
1. Verificar que `cors.php` esté incluido al inicio
2. Verificar que no haya echo/print antes de los headers
3. Activar `APP_DEBUG` para ver warnings

### Login admin no funciona

**Causa**: Password incorrecto o sesiones PHP mal configuradas

**Solución**:
1. Verificar que el hash del password esté correcto en la DB
2. Resetear password con el script SQL mencionado arriba
3. Verificar permisos de sesión: Site Tools > PHP Manager > Session Path

---

## 📚 Próximos Pasos

1. **Cambiar password del admin**
2. **Poblar la base de datos** con tu contenido real (usar el script de migración)
3. **Configurar email** para notificaciones de contacto
4. **Expandir el panel admin** con formularios CRUD completos
5. **Integrar el frontend** para que consuma las APIs en lugar del HTML estático

---

## 🆘 Soporte

Si encuentras problemas:

1. Revisa los logs de PHP: Site Tools > Statistics > Error Log
2. Revisa logs de PostgreSQL en phpPgAdmin
3. Activa modo debug temporalmente
4. Verifica permisos de archivos
5. Comprueba que todas las extensiones PHP estén habilitadas

---

## ✅ Checklist de Instalación

- [ ] Base de datos PostgreSQL creada
- [ ] Schema SQL ejecutado exitosamente
- [ ] Archivo `config.php` creado con credenciales correctas
- [ ] Clave `JWT_SECRET_KEY` generada
- [ ] Conexión a DB probada y funcionando
- [ ] Panel admin accesible en `/admin/login.php`
- [ ] Login exitoso con credenciales por defecto
- [ ] Password del admin cambiado
- [ ] GitHub workflow ejecutado sin errores
- [ ] APIs funcionando correctamente

---

**¡Sistema listo para usar! 🎉**
