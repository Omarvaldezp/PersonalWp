# 🚀 Configuración de Deploy Automático a SiteGround

## ⚠️ Acción Requerida

El archivo de GitHub Actions no puede ser subido automáticamente por restricciones de seguridad de GitHub.
**Necesitas agregarlo manualmente siguiendo estos pasos:**

## 📝 Paso 1: Crear el Workflow en GitHub

### Opción A: Desde la Interfaz Web de GitHub (Más Fácil)

1. Ve a tu repositorio: https://github.com/Omarvaldezp/PersonalWp

2. Haz clic en **Add file** → **Create new file**

3. En el campo del nombre del archivo escribe:
   ```
   .github/workflows/deploy.yml
   ```

4. Copia y pega este contenido:

```yaml
name: Deploy to SiteGround

on:
  push:
    branches:
      - main
      - master
  workflow_dispatch: # Permite ejecutar manualmente

jobs:
  deploy:
    name: Deploy to SiteGround via SFTP
    runs-on: ubuntu-latest

    steps:
      - name: Checkout code
        uses: actions/checkout@v4
        with:
          fetch-depth: 0

      - name: Setup Node.js (opcional para build)
        uses: actions/setup-node@v4
        with:
          node-version: '20'

      - name: Install dependencies (si usas npm/yarn)
        run: |
          if [ -f "package.json" ]; then
            npm ci
          fi

      - name: Build project (si necesitas compilar)
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

5. Haz clic en **Commit changes**

### Opción B: Desde tu Computadora Local

1. Clona o actualiza tu repositorio local:
   ```bash
   git clone https://github.com/Omarvaldezp/PersonalWp.git
   cd PersonalWp
   ```

2. El archivo ya existe en `.github/workflows/deploy.yml`

3. Agrega y commitea desde tu computadora personal (no desde Claude Code):
   ```bash
   git add .github/workflows/deploy.yml
   git commit -m "Add deployment workflow"
   git push origin main
   ```

## 🔑 Paso 2: Configurar Secrets en GitHub

1. Ve a tu repositorio en GitHub
2. Clic en **Settings** → **Secrets and variables** → **Actions**
3. Clic en **New repository secret**
4. Agrega cada uno de estos secrets:

### Secrets Requeridos:

| Nombre del Secret | Descripción | Ejemplo |
|-------------------|-------------|---------|
| `FTP_SERVER` | Servidor FTP de SiteGround | `ftp26.siteground.com` |
| `FTP_USERNAME` | Usuario FTP de SiteGround | `u123456789` |
| `FTP_PASSWORD` | Contraseña FTP | `tu_contraseña_segura` |
| `FTP_PORT` | Puerto FTP (opcional) | `21` |

### Cómo Obtener las Credenciales FTP de SiteGround:

1. Inicia sesión en **SiteGround**
2. Ve a **Site Tools**
3. Sección **Devs** → **FTP Accounts Manager**
4. Crea una cuenta FTP o usa la existente
5. Anota los datos:
   - **Servidor**: Ejemplo `ftp26.siteground.com`
   - **Usuario**: Tu usuario FTP
   - **Contraseña**: Tu contraseña FTP

## ⚙️ Paso 3: Configurar Directorio de Deploy (Opcional)

Si tu directorio en SiteGround NO es `/public_html/`, edita el workflow:

```yaml
server-dir: /tu-directorio-personalizado/
```

Opciones comunes:
- `/public_html/` - Directorio principal del sitio
- `/public_html/subdirectorio/` - Para subdirectorios
- `/www/` - Algunos hostings usan este

## ✅ Paso 4: Verificar que Funciona

1. Haz un cambio pequeño en tu código
2. Haz commit y push:
   ```bash
   git add .
   git commit -m "Test deploy"
   git push origin main
   ```
3. Ve a GitHub → **Actions** tab
4. Deberías ver el workflow ejecutándose

## 🎯 Flujo de Trabajo Final

Una vez configurado, el proceso será:

1. **Desarrollas localmente**: `npm run dev`
2. **Haces commit**: `git commit -m "nueva funcionalidad"`
3. **Push a main**: `git push origin main`
4. **Deploy automático**: GitHub Actions despliega a SiteGround
5. **Sitio actualizado**: Tu sitio en SiteGround se actualiza automáticamente

## 🆘 Troubleshooting

### Error: "No se puede conectar al servidor FTP"
- Verifica que los secrets estén correctos
- Confirma que el servidor FTP sea el correcto (`ftp26.siteground.com`, etc.)
- Intenta cambiar el protocolo de `ftps` a `ftp` en el workflow

### Error: "Permission denied"
- Verifica que el usuario FTP tenga permisos de escritura
- Confirma el directorio de destino (`server-dir`)

### Los cambios no se reflejan
- Limpia la cache del navegador (Ctrl + Shift + R)
- Verifica que el workflow terminó exitosamente en GitHub Actions
- Revisa los logs del deploy en GitHub Actions

## 📚 Archivos de Referencia

El workflow completo está guardado en:
```
.github/workflows/deploy.yml
```

Este archivo existe en tu proyecto local pero no puede ser subido automáticamente por Claude Code.

---

**Una vez completados estos pasos, tu sitio se actualizará automáticamente con cada push a la rama main!** 🎉
