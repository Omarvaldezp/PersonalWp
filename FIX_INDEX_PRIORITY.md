# 🎯 SOLUCIÓN: Forzar index.html como Página Principal

## ✅ Problema Identificado

**Síntoma:**
- ❌ `omarvaldez.com` → Error de WordPress
- ✅ `omarvaldez.com/index.html` → Webapp funciona

**Causa:**
Queda un archivo `index.php` de WordPress que Apache ejecuta ANTES que tu `index.html`.

**Apache prioriza archivos en este orden:**
1. `index.php` ← Se ejecuta primero
2. `index.html` ← Solo si NO hay index.php

---

## 🚀 SOLUCIÓN RÁPIDA: Subir .htaccess

He creado un archivo `.htaccess` que fuerza a Apache a usar `index.html` como prioridad.

### Opción A: Deploy Automático (Recomendado - 2 minutos)

1. **Haz merge a main:**

   **Desde GitHub (más fácil):**
   - Ve a: https://github.com/Omarvaldezp/PersonalWp
   - Verás un banner que dice "claude/github-repo-setup-011CUoWnqsN4SxYawMLZYP7m had recent pushes"
   - Haz clic en **"Compare & pull request"**
   - Haz clic en **"Create pull request"**
   - Haz clic en **"Merge pull request"**
   - Haz clic en **"Confirm merge"**

   **O desde línea de comandos:**
   ```bash
   git checkout main
   git merge claude/github-repo-setup-011CUoWnqsN4SxYawMLZYP7m
   git push origin main
   ```

2. **El deploy se ejecutará automáticamente**

3. **Espera 30-60 segundos**

4. **Visita `omarvaldez.com`** (sin `/index.html`)

5. **Limpia cache:** Ctrl + Shift + R

6. **¡Debería funcionar!** ✅

---

### Opción B: Manual desde SiteGround (3 minutos)

Si quieres que funcione YA sin esperar el deploy:

1. **Ve a SiteGround → Site Tools → File Manager**

2. **Navega a `/public_html/`**

3. **Haz clic en "New File"**

4. **Nombre:** `.htaccess`

5. **Edita el archivo y pega esto:**

```apache
# Configuración para webapp moderna
# Fuerza index.html como página principal

# Habilitar RewriteEngine
RewriteEngine On

# index.html como página principal (PRIORITARIO)
DirectoryIndex index.html index.php

# Forzar HTTPS (recomendado)
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]

# Cacheo para recursos estáticos
<IfModule mod_expires.c>
  ExpiresActive On
  ExpiresByType text/css "access plus 1 year"
  ExpiresByType application/javascript "access plus 1 year"
  ExpiresByType image/jpeg "access plus 1 year"
  ExpiresByType image/png "access plus 1 year"
</IfModule>

# Comprimir archivos
<IfModule mod_deflate.c>
  AddOutputFilterByType DEFLATE text/html text/plain text/css application/javascript
</IfModule>

# Prevenir listado de directorios
Options -Indexes
```

6. **Guarda el archivo**

7. **Visita `omarvaldez.com`** inmediatamente

8. **Limpia cache:** Ctrl + Shift + R

9. **¡Debería funcionar!** ✅

---

## 🔍 Opción C: Eliminar index.php Manualmente (Alternativa)

Si el .htaccess no funciona:

1. **Ve a SiteGround File Manager**

2. **Busca el archivo `index.php`** en `/public_html/`

3. **Elimínalo**

4. **Refresca tu sitio**

---

## ⚡ ¿Cuál Elegir?

| Opción | Velocidad | Dificultad | Permanente |
|--------|-----------|------------|------------|
| **A - Deploy automático** | 2 min | ⭐ Fácil | ✅ Sí |
| **B - Manual .htaccess** | Inmediato | ⭐⭐ Media | ✅ Sí |
| **C - Eliminar index.php** | Inmediato | ⭐ Fácil | ⚠️ Temporal* |

*Temporal porque si queda un index.php oculto, podría reaparecer el problema.

---

## 📋 Checklist

- [ ] Elegí una opción (A, B o C)
- [ ] Seguí los pasos
- [ ] Visité `omarvaldez.com` (sin /index.html)
- [ ] Limpié cache (Ctrl + Shift + R)
- [ ] ✅ ¡MI WEBAPP FUNCIONA EN LA RAÍZ!

---

## 🎉 Resultado Esperado

Antes:
```
omarvaldez.com → ❌ Error WordPress
omarvaldez.com/index.html → ✅ Webapp
```

Después:
```
omarvaldez.com → ✅ Webapp
omarvaldez.com/index.html → ✅ Webapp (también funciona)
```

---

## 🆘 Si Sigue Sin Funcionar

### Problema: Sigo viendo error de WordPress

**Posibles causas:**

1. **Cache del navegador:**
   - Prueba en modo incógnito
   - O usa otro navegador

2. **Cache del servidor/CDN:**
   - Si usas Cloudflare, purga el cache
   - Espera 5 minutos para propagación

3. **El .htaccess no se subió:**
   - Verifica en File Manager que exista `/public_html/.htaccess`
   - Si no existe, usa Opción B (manual)

4. **Error de sintaxis en .htaccess:**
   - Elimina el .htaccess temporalmente
   - Si el sitio funciona sin él, hay un error de sintaxis
   - Usa una versión más simple

### Problema: Error 500 después de agregar .htaccess

**Solución:**
1. Elimina el .htaccess de File Manager
2. Usa esta versión más simple:

```apache
DirectoryIndex index.html index.php
```

3. Guarda y prueba

---

## 🔍 Verificación Final

Una vez que funcione:

1. **Visita `omarvaldez.com`** → Debería mostrar tu webapp
2. **Inspecciona el código (Ctrl + U)** → Deberías ver el HTML de tu webapp
3. **Verifica en File Manager:**
   - ✅ `/public_html/index.html` existe
   - ✅ `/public_html/.htaccess` existe
   - ✅ `/public_html/main.js` existe
   - ✅ `/public_html/styles/main.css` existe

---

## 🎯 Mi Recomendación

**Usa Opción B (Manual)** para que funcione en 30 segundos, y luego haz **Opción A (Deploy)** para que quede permanente en tu repositorio.

---

¿Lista para probar? Elige una opción y repórtame el resultado. 🚀
