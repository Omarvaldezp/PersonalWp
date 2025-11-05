# ✅ Checklist Rápido: Eliminar WordPress

Sigue estos pasos en orden:

## 1️⃣ HACER BACKUP (NO SALTAR ESTO)

- [ ] Ve a **SiteGround → Site Tools → Backups**
- [ ] Haz clic en **"Create Backup"** o **"Backup Now"**
- [ ] Espera que termine (1-5 minutos)
- [ ] Anota la fecha del backup: ________________

## 2️⃣ ABRIR FILE MANAGER

- [ ] Ve a **SiteGround → Site Tools → File Manager**
- [ ] Navega a **`/public_html/`**

## 3️⃣ SELECCIONAR ARCHIVOS DE WORDPRESS

Marca **SOLO** estos archivos/carpetas (no marques `index.html`, `main.js`, ni `styles/`):

- [ ] `wp-admin/` (carpeta)
- [ ] `wp-includes/` (carpeta)
- [ ] `wp-content/` (carpeta)
- [ ] `index.php`
- [ ] `wp-config.php`
- [ ] Todos los archivos `wp-*.php`
- [ ] `.htaccess`
- [ ] `license.txt`
- [ ] `readme.html`

## 4️⃣ ELIMINAR

- [ ] Haz clic en el botón **"Delete"** (icono de basura)
- [ ] Confirma la eliminación

## 5️⃣ VERIFICAR

- [ ] Refresca File Manager (F5)
- [ ] Deberías ver SOLO:
  - `index.html`
  - `main.js`
  - `styles/`
  - `.ftp-deploy-sync-state.json`

## 6️⃣ PROBAR TU WEBAPP

- [ ] Abre tu dominio: `https://tudominio.com`
- [ ] Limpia cache: **Ctrl + Shift + R** (Windows) o **Cmd + Shift + R** (Mac)
- [ ] ¿Ves tu nueva webapp? ✅

---

## 🆘 Si Algo Sale Mal

**¿Eliminaste archivos por error?**
→ Ve a GitHub → Actions → "Run workflow" (se volverán a subir)

**¿Ves página en blanco?**
→ Espera 2-3 minutos y refresca con Ctrl + Shift + R

**¿Quieres restaurar WordPress?**
→ SiteGround → Backups → Selecciona backup → Restore

---

## 📄 Guía Detallada

Para instrucciones completas, lee: `DELETE_WORDPRESS_GUIDE.md`

---

¡Listo! Tu webapp estará funcionando en tu dominio principal. 🚀
