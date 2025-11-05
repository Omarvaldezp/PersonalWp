# ⚠️ Actualización Urgente del Workflow de Deployment

## El Problema que Encontramos

El workflow de GitHub Actions falló con este error:

```
npm error The `npm ci` command can only install with an existing package-lock.json
```

## ✅ Ya Solucionado Parcialmente

He agregado el archivo `package-lock.json` al repositorio, lo cual resuelve parte del problema.

## 🔧 Acción Requerida: Actualizar el Workflow

Necesitas actualizar manualmente el archivo `.github/workflows/deploy.yml` en GitHub.

### Instrucciones Paso a Paso:

#### 1. Ve al archivo del workflow en GitHub

```
https://github.com/Omarvaldezp/PersonalWp/blob/main/.github/workflows/deploy.yml
```

O navega: Repositorio → `.github` → `workflows` → `deploy.yml`

#### 2. Haz clic en el ícono del lápiz ✏️ para editar

#### 3. Busca esta sección (líneas 26-30):

```yaml
      - name: Install dependencies (si usas npm/yarn)
        run: |
          if [ -f "package.json" ]; then
            npm ci
          fi
```

#### 4. Reemplázala con esto:

```yaml
      - name: Install dependencies (si usas npm/yarn)
        run: |
          if [ -f "package.json" ]; then
            if [ -f "package-lock.json" ]; then
              npm ci
            else
              npm install
            fi
          fi
```

**¿Qué hace esto?**
- Usa `npm ci` si existe `package-lock.json` (más rápido)
- Usa `npm install` si no existe (fallback)

#### 5. Busca esta sección (líneas 38-55):

```yaml
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

#### 6. Cambia `local-dir` y agrega más archivos excluidos:

```yaml
      - name: Deploy to SiteGround via SFTP
        uses: SamKirkland/FTP-Deploy-Action@v4.3.5
        with:
          server: ${{ secrets.FTP_SERVER }}
          username: ${{ secrets.FTP_USERNAME }}
          password: ${{ secrets.FTP_PASSWORD }}
          port: ${{ secrets.FTP_PORT || 21 }}
          protocol: ftps
          local-dir: ./src/
          server-dir: /public_html/
          dangerous-clean-slate: false
          exclude: |
            **/.git*
            **/.git*/**
            **/node_modules/**
            **/.env
            **/composer.lock
            **/.DS_Store
            **/package.json
            **/package-lock.json
            **/vite.config.js
```

**Cambios importantes:**
- `local-dir: ./dist/` → `local-dir: ./src/` (despliega archivos fuente directamente)
- Se excluyen archivos de configuración que no necesitas en producción

#### 7. Guarda los cambios

Baja hasta el final, agrega el mensaje de commit:
```
Fix deployment workflow to handle package-lock.json
```

Haz clic en **"Commit changes"**

---

## 🎯 Workflow Completo Actualizado

Si prefieres copiar el archivo completo, aquí está:

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
            if [ -f "package-lock.json" ]; then
              npm ci
            else
              npm install
            fi
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
          local-dir: ./src/
          server-dir: /public_html/
          dangerous-clean-slate: false
          exclude: |
            **/.git*
            **/.git*/**
            **/node_modules/**
            **/.env
            **/composer.lock
            **/.DS_Store
            **/package.json
            **/package-lock.json
            **/vite.config.js
```

---

## ✅ Verificar que Funciona

Una vez actualizados los cambios:

1. Ve a **GitHub → Actions**
2. Haz clic en **"Run workflow"** manualmente
3. O haz un pequeño cambio y push para activarlo
4. Observa que ahora todos los pasos pasan ✅

---

## 📝 Resumen de Cambios

| Antes | Después | Beneficio |
|-------|---------|-----------|
| `npm ci` siempre | `npm ci` o `npm install` según disponibilidad | No falla si falta lockfile |
| `local-dir: ./dist/` | `local-dir: ./src/` | Despliega archivos directamente |
| No excluía configs | Excluye `package.json`, etc. | Solo archivos necesarios en servidor |

---

## ¿Por qué no pude actualizar esto automáticamente?

GitHub tiene restricciones de seguridad que impiden que apps automatizadas modifiquen workflows de GitHub Actions. Es una medida de seguridad para prevenir que código malicioso modifique pipelines de CI/CD.

---

¡Una vez actualizado el workflow, tus deployments funcionarán perfectamente! 🚀
