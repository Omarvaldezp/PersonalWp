# 🗑️ Guía: Eliminar WordPress y Activar Tu Webapp

## ⚠️ IMPORTANTE: Hacer Backup Primero

Antes de eliminar WordPress, **SIEMPRE** haz un backup por si acaso.

---

## PASO 1: Crear Backup en SiteGround (5 minutos)

### Opción A: Backup Automático de SiteGround (Más Fácil)

1. Ve a **SiteGround → Site Tools**
2. En el menú lateral, busca **"Backups"**
3. Haz clic en **"Backup Manager"**
4. Haz clic en **"Create Backup"** o **"Backup Now"**
5. Espera que se complete (1-5 minutos)
6. **Anota la fecha del backup** por si necesitas restaurar

### Opción B: Descargar Archivos Manualmente

1. Ve a **SiteGround → Site Tools → File Manager**
2. Navega a `/public_html/`
3. Selecciona todo (checkbox arriba)
4. Haz clic en **"Compress"** → Crea `backup-wordpress.zip`
5. **Descarga** el archivo .zip a tu computadora
6. Guárdalo en un lugar seguro

### Opción C: Backup de Base de Datos (Si Necesitas el Contenido)

1. Ve a **SiteGround → Site Tools → MySQL**
2. Haz clic en **"phpMyAdmin"**
3. Selecciona tu base de datos de WordPress
4. Haz clic en **"Export"**
5. Deja las opciones por defecto
6. Haz clic en **"Go"**
7. Guarda el archivo `.sql` en tu computadora

---

## PASO 2: Identificar Archivos de WordPress

Archivos y carpetas que debes **ELIMINAR**:

```
📁 /public_html/
├── 🗑️ wp-admin/              ← ELIMINAR
├── 🗑️ wp-includes/           ← ELIMINAR
├── 🗑️ wp-content/            ← ELIMINAR (contiene temas, plugins, uploads)
├── 🗑️ index.php              ← ELIMINAR
├── 🗑️ wp-config.php          ← ELIMINAR (contiene credenciales DB)
├── 🗑️ wp-activate.php        ← ELIMINAR
├── 🗑️ wp-blog-header.php     ← ELIMINAR
├── 🗑️ wp-comments-post.php   ← ELIMINAR
├── 🗑️ wp-config-sample.php   ← ELIMINAR
├── 🗑️ wp-cron.php            ← ELIMINAR
├── 🗑️ wp-links-opml.php      ← ELIMINAR
├── 🗑️ wp-load.php            ← ELIMINAR
├── 🗑️ wp-login.php           ← ELIMINAR
├── 🗑️ wp-mail.php            ← ELIMINAR
├── 🗑️ wp-settings.php        ← ELIMINAR
├── 🗑️ wp-signup.php          ← ELIMINAR
├── 🗑️ wp-trackback.php       ← ELIMINAR
├── 🗑️ xmlrpc.php             ← ELIMINAR
├── 🗑️ license.txt            ← ELIMINAR
├── 🗑️ readme.html            ← ELIMINAR
├── 🗑️ .htaccess              ← ELIMINAR (WordPress lo usa)
└── 🗑️ Cualquier archivo wp-*.php ← ELIMINAR
```

Archivos que debes **MANTENER** (tu webapp):

```
📁 /public_html/
├── ✅ index.html             ← MANTENER (tu webapp)
├── ✅ main.js                ← MANTENER (tu webapp)
├── ✅ styles/                ← MANTENER (tu webapp)
│   └── ✅ main.css
└── ✅ .ftp-deploy-sync-state.json ← MANTENER (GitHub Actions)
```

---

## PASO 3: Eliminar WordPress (10 minutos)

### Método A: File Manager de SiteGround (Recomendado)

1. **Ve a SiteGround → Site Tools → File Manager**

2. **Navega a `/public_html/`**

3. **Selecciona SOLO archivos de WordPress:**
   - Marca checkbox de `wp-admin/`
   - Marca checkbox de `wp-includes/`
   - Marca checkbox de `wp-content/`
   - Marca checkbox de `index.php`
   - Marca checkbox de `wp-config.php`
   - Marca todos los archivos `wp-*.php`
   - Marca `.htaccess`
   - Marca `license.txt`, `readme.html`

4. **NO SELECCIONES:**
   - ❌ `index.html` (tu webapp)
   - ❌ `main.js` (tu webapp)
   - ❌ `styles/` (tu webapp)
   - ❌ `.ftp-deploy-sync-state.json`

5. **Haz clic en el botón "Delete"** (icono de basura)

6. **Confirma la eliminación**

### Método B: Vía FTP (FileZilla, etc.)

1. Conéctate vía FTP con tus credenciales
2. Navega a `/public_html/`
3. Selecciona carpetas y archivos de WordPress
4. Clic derecho → Delete
5. Confirma

### Método C: Opción Rápida (Solo si estás seguro)

1. En File Manager, selecciona TODO en `/public_html/`
2. Elimina todo
3. Espera el próximo deploy de GitHub Actions
4. Se subirán automáticamente solo los archivos de tu webapp

**⚠️ CUIDADO:** Esta opción eliminará TODO, incluyendo archivos que no sean de WordPress.

---

## PASO 4: Verificar la Eliminación

1. **Refresca File Manager** (F5 o botón Refresh)

2. **Deberías ver SOLO estos archivos:**
   ```
   /public_html/
   ├── index.html
   ├── main.js
   ├── styles/
   │   └── main.css
   └── .ftp-deploy-sync-state.json
   ```

3. **Si eliminaste todo accidentalmente:**
   - Ve a GitHub → Actions
   - Haz clic en "Run workflow" manualmente
   - O haz un pequeño cambio y push
   - Los archivos se subirán automáticamente

---

## PASO 5: Crear .htaccess para Tu Webapp (Opcional pero Recomendado)

WordPress usaba `.htaccess`. Crea uno nuevo para tu webapp:

1. En File Manager, haz clic en **"New File"**
2. Nombre: `.htaccess`
3. Ubícalo en `/public_html/.htaccess`
4. Edita el archivo y pega:

```apache
# Configuración para webapp moderna

# Habilitar RewriteEngine
RewriteEngine On

# Forzar HTTPS (opcional pero recomendado)
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]

# Servir index.html como página principal
DirectoryIndex index.html

# Cacheo para recursos estáticos
<IfModule mod_expires.c>
  ExpiresActive On
  ExpiresByType text/css "access plus 1 year"
  ExpiresByType application/javascript "access plus 1 year"
  ExpiresByType image/jpeg "access plus 1 year"
  ExpiresByType image/png "access plus 1 year"
</IfModule>

# Comprimir archivos para carga más rápida
<IfModule mod_deflate.c>
  AddOutputFilterByType DEFLATE text/html text/plain text/css application/javascript
</IfModule>

# Proteger archivos sensibles
<Files .env>
  Order Allow,Deny
  Deny from all
</Files>
```

5. Guarda el archivo

---

## PASO 6: Verificar que Tu Webapp Funciona

1. **Abre tu navegador**

2. **Visita tu dominio:**
   ```
   https://tudominio.com
   ```

3. **Limpia la cache del navegador:**
   - **Chrome/Edge**: Ctrl + Shift + R (Windows) o Cmd + Shift + R (Mac)
   - **Firefox**: Ctrl + F5

4. **Deberías ver tu webapp:**
   - Título: "Personal WebApp"
   - Mensaje: "Bienvenido a tu nueva WebApp"
   - Estilo moderno con colores azul/gris

5. **Si ves error 404 o página en blanco:**
   - Espera 2-3 minutos para propagación
   - Limpia cache del navegador de nuevo
   - Verifica que `index.html` exista en `/public_html/`

---

## PASO 7: Limpiar Base de Datos de WordPress (Opcional)

Si quieres liberar espacio:

1. Ve a **SiteGround → Site Tools → MySQL**
2. Haz clic en **"phpMyAdmin"**
3. Selecciona la base de datos de WordPress
4. Haz clic en **"Drop"** (eliminar)
5. Confirma

**⚠️ SOLO haz esto si:**
- Ya hiciste backup
- No necesitas el contenido de WordPress
- Estás 100% seguro

---

## ✅ Checklist Final

Marca cada item:

- [ ] Hice backup de WordPress (descargué o usé SiteGround Backup)
- [ ] Identifiqué todos los archivos de WordPress
- [ ] Eliminé todos los archivos de WordPress
- [ ] Dejé intactos los archivos de mi webapp
- [ ] Creé el archivo `.htaccess` (opcional)
- [ ] Visité mi dominio y veo mi webapp
- [ ] Limpieza de base de datos (opcional)

---

## 🆘 Si Algo Sale Mal

### Problema: Eliminé archivos por error

**Solución:**
1. Ve a GitHub → Actions
2. Haz clic en "Run workflow" manualmente
3. Los archivos de tu webapp se subirán de nuevo

### Problema: Veo página en blanco

**Solución:**
1. Verifica que `index.html` exista en `/public_html/`
2. Limpia cache del navegador (Ctrl + Shift + R)
3. Espera 2-3 minutos
4. Revisa la consola del navegador (F12) para ver errores

### Problema: Quiero restaurar WordPress

**Solución:**
1. Ve a SiteGround → Backups
2. Selecciona el backup que hiciste
3. Haz clic en "Restore"
4. Espera que se complete

### Problema: Error 500

**Solución:**
1. Revisa el archivo `.htaccess`
2. Bórralo temporalmente para ver si ese es el problema
3. Si funciona sin `.htaccess`, hay un error de sintaxis en el archivo

---

## 🎉 ¡Felicidades!

Una vez completado:

✅ WordPress eliminado
✅ Tu webapp moderna funcionando
✅ Deploy automático activo
✅ Dominio principal apuntando a tu nueva app

Ahora cada vez que hagas `git push`, tu sitio se actualizará automáticamente. 🚀

---

## 📝 Próximos Pasos Recomendados

1. **Personalizar tu webapp:**
   - Editar colores en `src/styles/main.css`
   - Agregar más contenido en `src/index.html`
   - Agregar funcionalidades en `src/main.js`

2. **Agregar features:**
   - Sistema de routing
   - Formulario de contacto
   - Galería de imágenes
   - Blog o portafolio

3. **Optimizar:**
   - Agregar Google Analytics
   - Configurar SEO (meta tags)
   - Agregar favicon
   - Optimizar imágenes

¿Necesitas ayuda con alguno de estos pasos? 🚀
