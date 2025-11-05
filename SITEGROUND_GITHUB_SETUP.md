# 🔐 Guía Completa: Conectar SiteGround con GitHub Actions

Esta guía te llevará paso a paso para configurar el deploy automático desde GitHub a tu hosting de SiteGround.

---

## 📋 Requisitos Previos

- ✅ Cuenta de SiteGround activa
- ✅ Repositorio en GitHub (ya lo tienes: PersonalWp)
- ✅ 15-20 minutos de tiempo

---

## PARTE 1: Obtener Credenciales FTP desde SiteGround

### Paso 1: Ingresar a SiteGround

1. Ve a [https://login.siteground.com/](https://login.siteground.com/)
2. Ingresa con tu email y contraseña
3. Haz clic en **"Login"**

### Paso 2: Acceder a Site Tools

1. En el dashboard principal, verás tu lista de sitios web
2. Busca el sitio que quieres conectar con GitHub
3. Haz clic en el botón **"Site Tools"** (herramientas del sitio)

   ```
   Mi Sitio Web
   └── [Botón Site Tools] ← Haz clic aquí
   ```

### Paso 3: Navegar a FTP Accounts Manager

1. En el panel lateral izquierdo, busca la sección **"DEVS"**
2. Dentro de DEVS, haz clic en **"FTP Accounts Manager"**

   ```
   Panel lateral:
   ├── Dashboard
   ├── Email
   ├── Security
   └── DEVS
       └── FTP Accounts Manager ← Haz clic aquí
   ```

### Paso 4: Ver o Crear Cuenta FTP

**Opción A: Si ya tienes una cuenta FTP**

1. Verás una tabla con cuentas FTP existentes
2. Busca la cuenta que dice **"master"** o el nombre de tu dominio
3. Haz clic en el ícono del **ojo** (👁️) para ver la contraseña
4. Anota estos datos:
   - **Usuario (Username)**: Ejemplo: `u123456789-john`
   - **Servidor (Server)**: Ejemplo: `ftp26.siteground.com`
   - **Puerto (Port)**: Normalmente es `21`
   - **Contraseña (Password)**: La que veas al hacer clic en el ojo

**Opción B: Si NO tienes cuenta FTP, crear una nueva**

1. Haz clic en **"Create FTP Account"**
2. Completa el formulario:
   - **Username**: Crea un nombre (ejemplo: `github-deploy`)
   - **Password**: Crea una contraseña segura (guárdala!)
   - **Directory**: Deja `/public_html` o selecciona donde quieres el deploy
3. Haz clic en **"Create"**
4. Anota los datos que aparecen:
   - Username
   - Server
   - Port
   - Password

### Paso 5: Anotar Información Importante

Copia esta plantilla y complétala con tus datos:

```
=================================
CREDENCIALES FTP DE SITEGROUND
=================================

Servidor (FTP_SERVER):
Ejemplo: ftp26.siteground.com
Mi valor: _____________________

Usuario (FTP_USERNAME):
Ejemplo: u123456789-github
Mi valor: _____________________

Contraseña (FTP_PASSWORD):
Mi valor: _____________________

Puerto (FTP_PORT):
Normalmente: 21
Mi valor: _____________________

Directorio destino:
Normalmente: /public_html/
Mi valor: _____________________
=================================
```

---

## PARTE 2: Crear el Workflow en GitHub

### Paso 6: Ir a tu Repositorio en GitHub

1. Abre tu navegador y ve a:
   ```
   https://github.com/Omarvaldezp/PersonalWp
   ```

2. Asegúrate de estar en el repositorio correcto

### Paso 7: Crear el Archivo de Workflow

1. Haz clic en el botón **"Add file"** (arriba a la derecha)
2. Selecciona **"Create new file"**

   ```
   [+ Add file ▼]
   └── Create new file ← Haz clic aquí
   ```

3. En el campo **"Name your file"**, escribe exactamente:
   ```
   .github/workflows/deploy.yml
   ```

   ⚠️ **IMPORTANTE**: GitHub creará automáticamente las carpetas `.github` y `workflows`

### Paso 8: Copiar el Código del Workflow

Copia y pega este código completo en el editor:

```yaml
name: Deploy to SiteGround

on:
  push:
    branches:
      - main
      - master
  workflow_dispatch:

jobs:
  deploy:
    name: Deploy to SiteGround via SFTP
    runs-on: ubuntu-latest

    steps:
      - name: Checkout code
        uses: actions/checkout@v4
        with:
          fetch-depth: 0

      - name: Setup Node.js
        uses: actions/setup-node@v4
        with:
          node-version: '20'

      - name: Install dependencies
        run: |
          if [ -f "package.json" ]; then
            npm ci
          fi

      - name: Build project
        run: |
          if [ -f "package.json" ] && grep -q '"build"' package.json; then
            npm run build
          fi

      - name: Deploy to SiteGround via SFTP
        uses: SamKirkland/FTP-Deploy-Action@v4.3.5
        with:
          server: ${{ secrets.FTP_SERVER }}
          username: ${{ secrets.FTP_USERNAME }}
          password: ${{ secrets.FTP_PASSWORD }}
          port: ${{ secrets.FTP_PORT || 21 }}
          protocol: ftps
          local-dir: ./dist/
          server-dir: /public_html/
          dangerous-clean-slate: false
          exclude: |
            **/.git*
            **/.git*/**
            **/node_modules/**
            **/.env
            **/composer.lock
            **/.DS_Store
```

### Paso 9: Guardar el Archivo

1. Baja hasta el final de la página
2. En **"Commit new file"**:
   - Mensaje: `Add GitHub Actions workflow for SiteGround deployment`
3. Selecciona **"Commit directly to the main branch"**
4. Haz clic en **"Commit new file"**

---

## PARTE 3: Configurar Secrets en GitHub

### Paso 10: Ir a Settings del Repositorio

1. Estando en tu repositorio `PersonalWp`, haz clic en **"Settings"** (arriba)

   ```
   < > Code    Issues    Pull requests    Settings ← Haz clic aquí
   ```

2. ⚠️ **Si no ves "Settings"**: Es porque no eres el dueño del repositorio. Necesitas permisos de administrador.

### Paso 11: Navegar a Secrets

1. En el menú lateral izquierdo, busca **"Secrets and variables"**
2. Haz clic para expandirlo
3. Selecciona **"Actions"**

   ```
   Settings → Menú lateral:
   ├── General
   ├── Collaborators
   ├── Secrets and variables
   │   └── Actions ← Haz clic aquí
   ```

### Paso 12: Agregar el Primer Secret (FTP_SERVER)

1. Haz clic en el botón verde **"New repository secret"**

2. Completa el formulario:
   - **Name**: `FTP_SERVER`
   - **Secret**: Pega tu servidor (ejemplo: `ftp26.siteground.com`)

3. Haz clic en **"Add secret"**

### Paso 13: Agregar el Segundo Secret (FTP_USERNAME)

1. Haz clic nuevamente en **"New repository secret"**

2. Completa:
   - **Name**: `FTP_USERNAME`
   - **Secret**: Pega tu usuario FTP (ejemplo: `u123456789-github`)

3. Haz clic en **"Add secret"**

### Paso 14: Agregar el Tercer Secret (FTP_PASSWORD)

1. Haz clic nuevamente en **"New repository secret"**

2. Completa:
   - **Name**: `FTP_PASSWORD`
   - **Secret**: Pega tu contraseña FTP

3. Haz clic en **"Add secret"**

### Paso 15: Agregar el Cuarto Secret (FTP_PORT) - Opcional

1. Haz clic nuevamente en **"New repository secret"**

2. Completa:
   - **Name**: `FTP_PORT`
   - **Secret**: `21`

3. Haz clic en **"Add secret"**

### Paso 16: Verificar Secrets

Deberías ver una lista con 4 secrets:

```
✅ FTP_SERVER
✅ FTP_USERNAME
✅ FTP_PASSWORD
✅ FTP_PORT
```

---

## PARTE 4: Probar el Deploy

### Paso 17: Hacer un Cambio de Prueba

1. Ve al código de tu repositorio
2. Abre el archivo `README.md`
3. Haz clic en el ícono del lápiz (✏️) para editar
4. Agrega una línea al final:
   ```
   Test deploy - [fecha actual]
   ```
5. Haz commit con el mensaje: `Test automatic deployment`

### Paso 18: Ver el Deploy en Acción

1. Ve a la pestaña **"Actions"** en tu repositorio

   ```
   < > Code    Issues    Pull requests    Actions ← Haz clic aquí
   ```

2. Verás un workflow ejecutándose con el nombre de tu commit

3. Haz clic en el workflow para ver los detalles

4. Observa cada paso ejecutándose:
   ```
   ✅ Checkout code
   ✅ Setup Node.js
   ✅ Install dependencies
   ✅ Build project
   🔄 Deploy to SiteGround via SFTP ← Este subirá los archivos
   ```

### Paso 19: Verificar en SiteGround

1. Espera a que el workflow termine (aparecerá ✅ verde)

2. Ve a tu sitio web en el navegador:
   ```
   https://tudominio.com
   ```

3. Presiona **Ctrl + Shift + R** (o Cmd + Shift + R en Mac) para refrescar sin cache

4. Deberías ver tu webapp moderna cargando

---

## 🎉 ¡LISTO! Deploy Automático Configurado

### ¿Cómo Funciona Ahora?

Cada vez que hagas `git push` a la rama `main`:

```bash
git add .
git commit -m "Nuevas características"
git push origin main
```

GitHub Actions automáticamente:
1. ✅ Descarga tu código
2. ✅ Instala dependencias (`npm install`)
3. ✅ Compila el proyecto (`npm run build`)
4. ✅ Sube todo a SiteGround vía FTP
5. ✅ Tu sitio se actualiza automáticamente

---

## 🔧 Configuración Adicional

### Cambiar el Directorio de Destino

Si tu sitio NO está en `/public_html/`, edita el workflow:

1. Ve a `.github/workflows/deploy.yml`
2. Busca la línea:
   ```yaml
   server-dir: /public_html/
   ```
3. Cámbiala a tu directorio:
   ```yaml
   server-dir: /public_html/mi-subdirectorio/
   ```

### Opciones Comunes de Directorios:

- `/public_html/` - Sitio principal
- `/public_html/app/` - Subdirectorio app
- `/public_html/beta/` - Versión beta
- `/www/` - Algunos hostings usan este

### Cambiar el Protocolo (si FTPs no funciona)

En el workflow, busca:
```yaml
protocol: ftps
```

Prueba cambiar a:
```yaml
protocol: ftp
```

O si SiteGround te da acceso SSH/SFTP:
```yaml
protocol: sftp
```

---

## 🆘 Solución de Problemas

### ❌ Error: "Authentication failed"

**Causa**: Usuario o contraseña incorrectos

**Solución**:
1. Ve a GitHub → Settings → Secrets
2. Verifica que `FTP_USERNAME` y `FTP_PASSWORD` sean correctos
3. Actualiza los secrets si es necesario
4. Vuelve a hacer push para probar

### ❌ Error: "Connection refused"

**Causa**: Servidor o puerto incorrectos

**Solución**:
1. Verifica que `FTP_SERVER` sea correcto (ejemplo: `ftp26.siteground.com`)
2. Verifica que `FTP_PORT` sea `21`
3. Intenta cambiar `protocol` de `ftps` a `ftp`

### ❌ Error: "Permission denied"

**Causa**: El usuario FTP no tiene permisos en el directorio

**Solución**:
1. Ve a SiteGround → FTP Accounts Manager
2. Verifica que tu cuenta FTP tenga permisos de escritura
3. Verifica que `server-dir` sea un directorio válido

### ❌ Deploy exitoso pero sitio no actualiza

**Causa**: Cache del navegador o CDN

**Solución**:
1. Limpia cache del navegador (Ctrl + Shift + R)
2. Si usas Cloudflare o CDN, purga el cache
3. Espera 2-3 minutos para propagación

### ❌ Archivos no aparecen en SiteGround

**Causa**: Directorio incorrecto o build no generado

**Solución**:
1. Verifica que `npm run build` funcione localmente
2. Confirma que la carpeta `dist/` se genera
3. Revisa los logs en GitHub Actions para ver qué se subió

---

## 📞 ¿Necesitas Ayuda?

Si tienes problemas:

1. **Revisa los logs**:
   - GitHub → Actions → Click en el workflow fallido
   - Lee el error en el paso "Deploy to SiteGround"

2. **Información para soporte**:
   - Screenshot del error en GitHub Actions
   - Tu configuración de SiteGround (sin contraseñas)
   - Mensaje de error completo

3. **Recursos**:
   - [SiteGround FTP Guide](https://www.siteground.com/tutorials/ftp/)
   - [GitHub Actions Documentation](https://docs.github.com/en/actions)

---

## ✅ Checklist Final

Marca cada item cuando lo completes:

- [ ] Obtuve credenciales FTP de SiteGround
- [ ] Creé el workflow en `.github/workflows/deploy.yml`
- [ ] Agregué el secret `FTP_SERVER`
- [ ] Agregué el secret `FTP_USERNAME`
- [ ] Agregué el secret `FTP_PASSWORD`
- [ ] Agregué el secret `FTP_PORT` (opcional)
- [ ] Hice un test deploy y funcionó
- [ ] Verifiqué que el sitio se actualizó en SiteGround
- [ ] Documenté mis credenciales en un lugar seguro

---

¡Felicidades! Tu webapp ahora se despliega automáticamente. 🚀

Cada `git push` actualizará tu sitio en segundos.
