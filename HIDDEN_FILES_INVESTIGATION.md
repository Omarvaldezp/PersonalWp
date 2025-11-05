# 🔍 Investigación: Archivos Ocultos en SiteGround File Manager

## 🎯 Problema Identificado

Los logs de deploy muestran que los archivos están en el servidor, pero NO son visibles en el File Manager.

**Posibles causas:**
1. ✅ Archivos ocultos (empiezan con `.`) no se muestran
2. ✅ Configuración "Show Hidden Files" desactivada
3. ✅ Cache del File Manager
4. ✅ Directorio incorrecto

---

## 🔧 SOLUCIÓN 1: Activar "Mostrar Archivos Ocultos"

### En SiteGround File Manager:

1. **Ve a SiteGround → Site Tools → File Manager**

2. **Busca el botón "Settings" o "⚙️"** (generalmente arriba a la derecha)

3. **Busca la opción:**
   - **"Show Hidden Files"**
   - O **"Mostrar archivos ocultos"**
   - O un checkbox que diga **"Hidden Files"**

4. **Actívala** (marca el checkbox)

5. **Refresca el File Manager** (F5 o botón Refresh)

### ¿Qué archivos se mostrarán?

Una vez activado, deberías ver:
```
/public_html/
├── .htaccess                      ← Oculto (empieza con .)
├── .ftp-deploy-sync-state.json   ← Oculto (empieza con .)
├── index.html                     ← Normal
├── main.js                        ← Normal
├── styles/                        ← Normal
├── wp-admin/                      ← WordPress (si existe)
├── wp-content/                    ← WordPress (si existe)
└── index.php                      ← WordPress (si existe)
```

---

## 🔧 SOLUCIÓN 2: Verificar el Path Correcto

El deploy dice que sube a `/public_html/`, pero puede que estés viendo otro directorio.

### Verificar Path Actual:

1. En File Manager, mira la **barra de navegación arriba**
2. Debería decir algo como:
   ```
   /home/customer/www/omarvaldez.com/public_html/
   ```
   O simplemente:
   ```
   /public_html/
   ```

3. **Si dice algo diferente**, navega al path correcto:
   - Haz clic en "Home" 🏠
   - Busca la carpeta `public_html`
   - Entra en ella

### Paths Comunes en SiteGround:

- `/home/customer/www/omarvaldez.com/public_html/`
- `/home/customer/www/omarvaldez.com/public_html/www/`
- Simplemente `/public_html/`

---

## 🔧 SOLUCIÓN 3: Refrescar Cache del File Manager

El File Manager puede estar mostrando una vista antigua:

1. **Cierra el File Manager completamente**
2. **Vuelve a abrirlo:** SiteGround → Site Tools → File Manager
3. **O presiona Ctrl + Shift + R** en el File Manager

---

## 🔧 SOLUCIÓN 4: Usar la Búsqueda del File Manager

Si los archivos están ahí pero no los ves:

1. En File Manager, busca el icono de **búsqueda 🔍** (Search)
2. Busca: `index.html`
3. **Anota el path completo** donde lo encuentra
4. Navega a ese directorio

---

## 🔧 SOLUCIÓN 5: Verificar con FTP/SFTP

Si el File Manager no muestra los archivos, usa FTP:

### Con FileZilla (Cliente FTP):

1. **Descarga FileZilla** (gratis): https://filezilla-project.org/

2. **Conéctate con tus credenciales:**
   - Host: El valor de `FTP_SERVER` (ejemplo: `ftp26.siteground.com`)
   - Usuario: El valor de `FTP_USERNAME`
   - Contraseña: El valor de `FTP_PASSWORD`
   - Puerto: `21`

3. **Navega a `/public_html/`**

4. **¿Qué ves?**
   - Si ves `index.html`, `main.js`, etc. → Los archivos ESTÁN ahí
   - Si NO ves nada → Hay un problema con el directorio

5. **Desde FileZilla puedes:**
   - Ver todos los archivos (incluyendo ocultos)
   - Eliminar archivos de WordPress
   - Subir/descargar archivos manualmente

---

## 🔧 SOLUCIÓN 6: Verificar con SSH (Avanzado)

Si tienes acceso SSH en SiteGround:

### Activar SSH:

1. Ve a **SiteGround → Site Tools → SSH Keys Manager**
2. Activa acceso SSH
3. Conecta por SSH

### Comandos para Investigar:

```bash
# Listar todos los archivos (incluyendo ocultos)
ls -la /home/customer/www/omarvaldez.com/public_html/

# Buscar archivos de tu webapp
find /home/customer/www/omarvaldez.com/public_html/ -name "index.html"

# Ver el contenido del directorio
cd /home/customer/www/omarvaldez.com/public_html/
ls -lah

# Eliminar WordPress desde SSH (MÁS RÁPIDO)
cd /home/customer/www/omarvaldez.com/public_html/
rm -rf wp-admin wp-includes wp-content wp-*.php index.php
```

---

## 📊 Tabla Comparativa de Métodos

| Método | Dificultad | Velocidad | Muestra Ocultos | Recomendado |
|--------|------------|-----------|-----------------|-------------|
| File Manager (con "Show Hidden") | ⭐ Fácil | Rápido | ✅ Sí | ✅ Primera opción |
| FileZilla (FTP) | ⭐⭐ Media | Medio | ✅ Sí | ✅ Si File Manager falla |
| SSH | ⭐⭐⭐ Difícil | Muy rápido | ✅ Sí | Para usuarios avanzados |

---

## 🎯 Plan de Acción Recomendado

### PASO 1: Activar "Show Hidden Files"
- [ ] Abrí File Manager
- [ ] Busqué el botón Settings ⚙️
- [ ] Activé "Show Hidden Files"
- [ ] Refresqué (F5)
- [ ] Resultado: ___________________________

### PASO 2: Verificar Path
- [ ] El path actual es: ___________________________
- [ ] Navegué a `/public_html/`
- [ ] Ahora veo: ___________________________

### PASO 3: Usar Búsqueda
- [ ] Busqué "index.html"
- [ ] Lo encontró en: ___________________________
- [ ] Ese es el directorio correcto

### PASO 4: Probar con FileZilla (si los pasos anteriores fallan)
- [ ] Descargué e instalé FileZilla
- [ ] Me conecté con mis credenciales FTP
- [ ] Navegué a `/public_html/`
- [ ] Veo estos archivos: ___________________________

---

## 📋 Información para Reportar

Una vez hagas estos pasos, reporta:

**1. ¿Activaste "Show Hidden Files"?**
- [ ] Sí - Ahora veo más archivos
- [ ] Sí - Pero sigo sin ver nada
- [ ] No encontré esa opción

**2. ¿Cuál es el path completo que muestra File Manager?**
```
Path: _____________________________________
```

**3. ¿Qué archivos ves ahora en `/public_html/`?**
```
Lista de archivos:
- ___________
- ___________
- ___________
```

**4. ¿La búsqueda de "index.html" encontró algo?**
- [ ] Sí, en el path: _____________________
- [ ] No encontró nada

**5. Si usaste FileZilla, ¿qué ves en `/public_html/`?**
```
Lista de archivos desde FTP:
- ___________
- ___________
- ___________
```

---

## 🚨 Teoría Más Probable

**Hipótesis #1:** Los archivos están ahí pero ocultos
- Solución: Activar "Show Hidden Files"

**Hipótesis #2:** Estás viendo un subdirectorio diferente
- Solución: Verificar path y navegar a `/public_html/`

**Hipótesis #3:** Cache del File Manager
- Solución: Cerrar y reabrir File Manager

**Hipótesis #4:** WordPress ocupa todo el espacio y los archivos se subieron pero están "debajo"
- Solución: Eliminar WordPress primero, luego forzar re-deploy

---

## 📞 Próximo Paso

Prueba **SOLUCIÓN 1** primero (activar "Show Hidden Files") y reporta:
1. ¿Encontraste la opción?
2. ¿Qué archivos ves ahora?
3. Screenshot opcional del File Manager

Con esa información sabré exactamente qué está pasando. 🔍
