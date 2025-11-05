# 🚀 Deploy Limpio - Eliminar Todo y Empezar de Cero

## ⚠️ IMPORTANTE: Lee Esto Primero

Esta opción va a:
- ✅ **ELIMINAR TODO** en `/public_html/` (incluyendo WordPress)
- ✅ **SUBIR SOLO** tu webapp (`index.html`, `main.js`, `styles/`)
- ✅ Garantizar que tu webapp funcione inmediatamente

## 🎯 ¿Estás Seguro?

Marca cada item antes de continuar:

- [ ] **Ya NO necesito WordPress** - voy a eliminarlo permanentemente
- [ ] **Hice backup de WordPress** (o no me importa perderlo)
- [ ] **Quiero que mi webapp sea el sitio principal** en omarvaldez.com
- [ ] **Entiendo que esto borrará TODO en /public_html/**

---

## 🔧 PASO 1: Actualizar el Workflow en GitHub

### Instrucciones Exactas:

1. **Ve a tu repositorio en GitHub:**
   ```
   https://github.com/Omarvaldezp/PersonalWp
   ```

2. **Navega al archivo del workflow:**
   - Haz clic en la carpeta `.github`
   - Haz clic en la carpeta `workflows`
   - Haz clic en el archivo `deploy.yml`

3. **Edita el archivo:**
   - Haz clic en el ícono del **lápiz ✏️** (Edit this file)

4. **Busca la línea 103** que dice:
   ```yaml
   dangerous-clean-slate: false
   ```

5. **Cámbiala a:**
   ```yaml
   dangerous-clean-slate: true
   ```

6. **Guarda los cambios:**
   - Baja hasta el final
   - En "Commit message" escribe: `Enable clean slate deployment`
   - Haz clic en **"Commit changes"**

---

## 🚀 PASO 2: Ejecutar el Deploy Limpio

### Opción A: Deploy Automático (Recomendado)

1. **Haz un pequeño cambio en cualquier archivo:**
   - Edita `README.md`
   - Agrega una línea al final: `Clean deploy - [fecha]`
   - Haz commit

2. **El deploy se ejecutará automáticamente**

### Opción B: Deploy Manual

1. **Ve a GitHub → Actions**
2. **Haz clic en el workflow más reciente** (o en "Deploy to SiteGround")
3. **Haz clic en "Run workflow"**
4. **Selecciona la rama** `main` o `master`
5. **Haz clic en "Run workflow"**

---

## 📊 ¿Qué Va a Pasar?

### Durante el Deploy:

```
1. Conectando al servidor...
2. 🗑️ ELIMINANDO todos los archivos en /public_html/
   - Borrando wp-admin/
   - Borrando wp-content/
   - Borrando index.php
   - Borrando php_errorlog
   - Borrando TODO
3. 📤 Subiendo archivos de tu webapp:
   - Subiendo index.html
   - Subiendo main.js
   - Subiendo styles/main.css
4. ✅ Deploy completado
```

**Tiempo estimado:** 30-60 segundos

---

## ✅ PASO 3: Verificar que Funciona

1. **Ve a GitHub → Actions**
2. **Espera que el workflow termine** (círculo verde ✅)
3. **Abre tu sitio:**
   ```
   https://omarvaldez.com
   ```
4. **Limpia la cache del navegador:**
   - Windows: `Ctrl + Shift + R`
   - Mac: `Cmd + Shift + R`

5. **Deberías ver:**
   ```
   ╔════════════════════════════════════╗
   ║  Mi WebApp Personalizada           ║
   ║                                     ║
   ║  Bienvenido a tu nueva WebApp      ║
   ║  Esta es la base de tu aplicación  ║
   ║  web personalizada                 ║
   ║                                     ║
   ║  © 2025 - Construido con           ║
   ║  tecnologías modernas              ║
   ╚════════════════════════════════════╝
   ```

---

## 🔒 PASO 4: Revertir el Cambio (Importante)

Una vez que funcione, **debes revertir el cambio** para evitar borrar archivos accidentalmente en el futuro:

1. **Ve al archivo `deploy.yml` de nuevo en GitHub**
2. **Edita la línea 103:**
   ```yaml
   dangerous-clean-slate: false  # Volver a false
   ```
3. **Commit con mensaje:** `Disable clean slate after successful deploy`

**¿Por qué?** Si lo dejas en `true`, cada deploy borrará TODO y volverá a subir. Esto gasta tiempo y puede causar problemas.

Con `false`, solo sube archivos que cambiaron (más rápido y seguro).

---

## 📋 Checklist Completo

### Antes del Deploy:
- [ ] Leí y entiendo que esto borra TODO
- [ ] Tengo backup de WordPress (o no lo necesito)
- [ ] Estoy listo para eliminar WordPress permanentemente

### Durante el Deploy:
- [ ] Edité `deploy.yml` línea 103: `dangerous-clean-slate: true`
- [ ] Hice commit del cambio
- [ ] Ejecuté el workflow (automático o manual)
- [ ] El workflow terminó con ✅ verde

### Después del Deploy:
- [ ] Visité `https://omarvaldez.com`
- [ ] Limpié cache del navegador (Ctrl + Shift + R)
- [ ] ✅ VEO MI WEBAPP FUNCIONANDO
- [ ] Revertí el cambio: `dangerous-clean-slate: false`

---

## 🆘 Si Algo Sale Mal

### Problema: El workflow falla

**Solución:**
1. Ve a GitHub → Actions
2. Haz clic en el workflow fallido
3. Lee el error
4. Copia el error completo y repórtalo

### Problema: Sigo viendo WordPress

**Solución:**
1. Espera 2-3 minutos más
2. Limpia cache del navegador de nuevo (Ctrl + Shift + R)
3. Verifica que el workflow terminó exitosamente

### Problema: Veo página en blanco

**Solución:**
1. Abre la consola del navegador (F12)
2. Ve a la pestaña "Console"
3. ¿Hay errores? Repórtalos
4. Ve a la pestaña "Network"
5. Refresca la página
6. ¿Qué archivos se cargan? ¿Cuáles fallan?

### Problema: Error 500

**Solución:**
1. Ve a File Manager en SiteGround
2. Busca el archivo `php_errorlog`
3. Ábrelo y ve el último error
4. Repórtalo

---

## 🎉 ¡Éxito!

Una vez completado:

✅ WordPress eliminado permanentemente
✅ Tu webapp funcionando en `omarvaldez.com`
✅ Deploy automático activo
✅ Cada `git push` actualiza tu sitio

Ahora puedes:
- Personalizar tu webapp (`src/index.html`, `src/styles/main.css`)
- Agregar nuevas funcionalidades
- Cada cambio se despliega automáticamente

---

## 📝 Resumen de Cambios

**Archivo a editar:**
```
.github/workflows/deploy.yml
```

**Línea 103 - Cambiar de:**
```yaml
dangerous-clean-slate: false
```

**A:**
```yaml
dangerous-clean-slate: true
```

**Después de que funcione, revertir a:**
```yaml
dangerous-clean-slate: false
```

---

## 🔍 Logs Esperados

Cuando el deploy funcione, verás algo como:

```
🗑️ Removing all files from server (clean slate mode)
Deleted: wp-admin/
Deleted: wp-content/
Deleted: wp-includes/
Deleted: index.php
Deleted: php_errorlog
...
📤 Uploading new files:
✅ Uploaded: index.html
✅ Uploaded: main.js
✅ Uploaded: styles/main.css
🎉 Deploy complete!
```

---

¿Listo para empezar? Sigue **PASO 1** y repórtame cuando termines. 🚀
