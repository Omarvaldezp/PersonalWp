# Personal WebApp

Webapp moderna y personalizada, evolucionada desde WordPress a una aplicación web completamente personalizada.

## 🚀 Características

- **Deploy automático** con GitHub Actions
- **Tecnologías modernas**: Vite, JavaScript moderno
- **Estructura escalable** y profesional
- **Deploy a SiteGround** con cada push

## 📦 Estructura del Proyecto

```
PersonalWp/
├── .github/
│   └── workflows/
│       └── deploy.yml          # GitHub Actions para deploy automático
├── src/
│   ├── components/             # Componentes reutilizables
│   ├── assets/                 # Imágenes, fuentes, etc.
│   ├── styles/
│   │   └── main.css           # Estilos principales
│   ├── utils/                  # Funciones utilitarias
│   ├── index.html             # HTML principal
│   └── main.js                # Entry point JavaScript
├── public/                     # Archivos estáticos
├── dist/                       # Build output (generado automáticamente)
├── package.json
├── vite.config.js
└── README.md
```

## ⚙️ Configuración Inicial

### 1. Instalar Dependencias

```bash
npm install
```

### 2. Desarrollo Local

```bash
npm run dev
```

Esto abrirá tu webapp en `http://localhost:3000`

### 3. Build para Producción

```bash
npm run build
```

Los archivos compilados estarán en `/dist`

## 🔧 Configurar Deploy Automático a SiteGround

### Paso 1: Obtener Credenciales FTP de SiteGround

1. Entra a **SiteGround → Site Tools**
2. Ve a **Devs → FTP Accounts Manager**
3. Crea una cuenta FTP o usa la existente
4. Anota:
   - **Servidor FTP**: (ejemplo: `ftpXX.siteground.com`)
   - **Usuario**: tu usuario FTP
   - **Contraseña**: tu contraseña FTP
   - **Puerto**: 21 (normalmente)

### Paso 2: Configurar Secrets en GitHub

1. Ve a tu repositorio en GitHub
2. Clic en **Settings** → **Secrets and variables** → **Actions**
3. Clic en **New repository secret**
4. Agrega estos secrets:

| Nombre | Valor | Ejemplo |
|--------|-------|---------|
| `FTP_SERVER` | Tu servidor FTP | `ftp26.siteground.com` |
| `FTP_USERNAME` | Tu usuario FTP | `u123456789` |
| `FTP_PASSWORD` | Tu contraseña FTP | `tu_contraseña_segura` |
| `FTP_PORT` | Puerto (opcional) | `21` |

### Paso 3: Ajustar Configuración de Deploy

Edita `.github/workflows/deploy.yml` si necesitas:

- **Cambiar directorio de deploy**: Modifica `server-dir: /public_html/`
- **Cambiar directorio local**: Modifica `local-dir: ./dist/`
- **Protocolo**: Usa `ftps`, `ftp`, o `sftp` según tu SiteGround

### Paso 4: Realizar tu Primer Deploy

```bash
git add .
git commit -m "Initial webapp setup with auto-deploy"
git push origin main
```

Ve a **GitHub → Actions** y verás el deploy en proceso.

## 🔄 Workflow de Desarrollo

1. **Desarrolla localmente**: `npm run dev`
2. **Haz commit** de tus cambios: `git commit -m "descripción"`
3. **Push a GitHub**: `git push origin main`
4. **Deploy automático** se ejecuta automáticamente
5. **Verifica** tu sitio en SiteGround

## 🛠️ Próximos Pasos

- [ ] Agregar framework (React, Vue, Svelte)
- [ ] Configurar TypeScript
- [ ] Agregar testing (Jest, Vitest)
- [ ] Implementar CI/CD avanzado
- [ ] Agregar backend API (Node.js, PHP)
- [ ] Configurar base de datos
- [ ] Implementar autenticación de usuarios

## 📝 Notas Importantes

- El deploy automático solo se ejecuta en push a `main` o `master`
- Puedes ejecutar deploy manualmente desde GitHub Actions
- Los archivos en `.gitignore` NO se suben a SiteGround
- El directorio `/dist` se genera automáticamente con `npm run build`

## 🆘 Troubleshooting

### El deploy falla

- Verifica que los secrets estén correctos en GitHub
- Confirma que el usuario FTP tenga permisos de escritura
- Revisa los logs en GitHub Actions

### Cambios no se reflejan

- Limpia cache del navegador (Ctrl + Shift + R)
- Verifica que el deploy terminó exitosamente en GitHub Actions
- Confirma que el directorio de destino sea correcto

## 📚 Recursos

- [Vite Documentation](https://vitejs.dev/)
- [GitHub Actions](https://docs.github.com/en/actions)
- [SiteGround FTP Guide](https://www.siteground.com/tutorials/ftp/)

---

**¿Preguntas?** Contacta al equipo de desarrollo
 ```

   Test deploy - [fecha actual]

   ```
