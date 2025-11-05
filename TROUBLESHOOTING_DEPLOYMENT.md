# 🔍 Troubleshooting: Archivos No Aparecen en SiteGround

## Diagnóstico Paso a Paso

### PASO 1: Verificar los Logs del Deployment

1. Ve a tu repositorio en GitHub
2. Haz clic en la pestaña **"Actions"**
3. Busca el workflow más reciente que se ejecutó
4. Haz clic en el workflow
5. Haz clic en el job **"Deploy to SiteGround via SFTP"**
6. Expande el paso **"Deploy to SiteGround via SFTP"**

### ¿Qué deberías ver en los logs?

Busca estas líneas en los logs:

```
✔ Connected
✔ Uploading X files
✔ Upload complete
```

**🔴 Si ves errores de conexión:**
- Verifica los secrets en GitHub (FTP_SERVER, FTP_USERNAME, FTP_PASSWORD)
- Intenta cambiar el protocolo de `ftps` a `ftp`

**🟡 Si dice "0 files uploaded":**
- El directorio `./src/` está vacío o mal configurado
- Los archivos están siendo excluidos por error

### PASO 2: Verificar el Directorio en SiteGround

**¿Dónde estás buscando los archivos?**

Los archivos deberían estar en:
```
/public_html/
```

**Pero también podrían estar en:**
- `/public_html/www/`
- `/www/`
- `/home/usuario/public_html/`
- Un subdirectorio dentro de `/public_html/`

**Cómo verificar:**

1. Ve a **SiteGround → Site Tools**
2. Ve a **Site → File Manager**
3. Navega a `/public_html/`
4. Busca archivos como:
   - `index.html`
   - Carpeta `styles/`
   - `main.js`

### PASO 3: Verificar la Configuración del Workflow

**Ve a tu archivo `.github/workflows/deploy.yml` y verifica:**

```yaml
local-dir: ./src/           # ← Carpeta local a subir
server-dir: /public_html/   # ← Carpeta destino en SiteGround
```

**Posibles problemas:**

1. **`server-dir` incorrecto**: Necesitas el path correcto de tu hosting
2. **`local-dir` no existe**: Verifica que `./src/` tenga archivos

### PASO 4: Prueba de Conexión Manual

Para verificar que tu configuración FTP funciona:

1. Descarga **FileZilla** o cualquier cliente FTP
2. Conéctate con tus credenciales de SiteGround:
   - Host: El valor de `FTP_SERVER` (ejemplo: `ftp26.siteground.com`)
   - Usuario: El valor de `FTP_USERNAME`
   - Contraseña: El valor de `FTP_PASSWORD`
   - Puerto: 21
3. Una vez conectado, verás la estructura de directorios
4. Identifica el directorio correcto donde deberían ir los archivos

## 🛠️ Soluciones Comunes

### Solución 1: Cambiar el Protocolo

Si usas `ftps` y no funciona, prueba con `ftp`:

En `.github/workflows/deploy.yml`:
```yaml
protocol: ftp  # Cambiar de ftps a ftp
```

### Solución 2: Cambiar el Directorio de Destino

Si tu sitio NO está en `/public_html/`, cámbialo:

```yaml
server-dir: /www/                    # O
server-dir: /public_html/mi-sitio/   # O
server-dir: /home/usuario/www/       # Según tu hosting
```

### Solución 3: Verificar Archivos a Subir

Asegúrate de que `./src/` tiene contenido:

En tu repositorio, deberías tener:
```
src/
├── index.html
├── main.js
└── styles/
    └── main.css
```

### Solución 4: Activar Logs Detallados

Modifica el step de deploy para ver más información:

```yaml
- name: Deploy to SiteGround via SFTP
  uses: SamKirkland/FTP-Deploy-Action@v4.3.5
  with:
    server: ${{ secrets.FTP_SERVER }}
    username: ${{ secrets.FTP_USERNAME }}
    password: ${{ secrets.FTP_PASSWORD }}
    port: 21
    protocol: ftp
    local-dir: ./src/
    server-dir: /public_html/
    log-level: verbose  # ← Agregar esta línea
    dangerous-clean-slate: false
    exclude: |
      **/.git*
      **/.git*/**
      **/node_modules/**
```

## 📊 Checklist de Diagnóstico

Marca cada item que verificaste:

- [ ] Revisé los logs en GitHub Actions
- [ ] Los logs muestran "Upload complete"
- [ ] Verifiqué el directorio `/public_html/` en SiteGround
- [ ] Verifiqué otros posibles directorios (`/www/`, etc.)
- [ ] Los secrets están correctamente configurados
- [ ] El directorio `./src/` existe en el repositorio
- [ ] Intenté conectarme con FileZilla manualmente
- [ ] Probé cambiar el protocolo de `ftps` a `ftp`

## 🆘 Información para Soporte

Por favor proporciona:

1. **Logs del workflow**:
   - Ve a GitHub Actions
   - Copia el output del paso "Deploy to SiteGround via SFTP"
   - Pégalo aquí (oculta credenciales)

2. **Estructura de directorios en SiteGround**:
   - ¿Qué ves en File Manager?
   - ¿Cuál es el path completo de tu sitio?

3. **Configuración actual**:
   - ¿Qué protocolo usas? (ftp, ftps, sftp)
   - ¿Qué valor tienes en `server-dir`?

---

## 📝 Próximos Pasos

1. **Revisa los logs** del workflow en GitHub Actions
2. **Copia y pega** el output del paso de deploy aquí
3. **Verifica** qué directorios existen en tu File Manager de SiteGround
4. Con esa información, podré ayudarte a identificar el problema exacto

---

¿Qué ves en los logs del workflow? ¿Dice cuántos archivos subió?
