# 🗄️ Cómo Ejecutar el Schema SQL en SiteGround

SiteGround **NO tiene phpPgAdmin** en Site Tools. Aquí tienes 3 opciones para instalar tu base de datos:

---

## ✅ **OPCIÓN 1: Script PHP Automático** (RECOMENDADA - MÁS FÁCIL)

### Paso 1: Crear la base de datos en SiteGround

1. Ir a **Site Tools > PostgreSQL > Databases**
2. Click en **"Create Database"**
3. Configurar:
   ```
   Database Name: omarvaldez_db
   User: omarvaldez_admin
   Password: [genera contraseña segura]
   ```
4. **Anotar estas credenciales**

### Paso 2: Editar el script de instalación

En tu computadora local, edita el archivo `setup-database.php`:

```php
// Líneas 13-16 - Actualiza con TUS credenciales:
$db_host = 'localhost';
$db_port = '5432';
$db_name = 'omarvaldez_db';        // ← Tu nombre de DB
$db_user = 'omarvaldez_admin';     // ← Tu usuario
$db_password = 'tu_password_aqui'; // ← Tu password
```

### Paso 3: Subir el script vía GitHub

```bash
# En tu terminal local:
git add setup-database.php
git commit -m "Add database setup script"
git push
```

GitHub Actions lo subirá automáticamente a `/public_html/`

### Paso 4: Ejecutar el instalador

1. Abre tu navegador
2. Visita: `https://omarvaldez.com/setup-database.php?password=admin123`
3. Click en **"📊 Instalar Schema"**
4. Esperar confirmación de éxito
5. (Opcional) Click en **"🌱 Instalar Datos de Ejemplo"**

### Paso 5: ELIMINAR el script

⚠️ **MUY IMPORTANTE por seguridad:**

1. Ir a **Site Tools > File Manager**
2. Navegar a `/public_html/`
3. Borrar `setup-database.php`

**¡Listo!** Tu base de datos está instalada.

---

## 🖥️ **OPCIÓN 2: Acceso Remoto con pgAdmin** (Local)

### Paso 1: Habilitar acceso remoto en SiteGround

1. Ir a **Site Tools > PostgreSQL > Remote**
2. Click en **"Add New IP"**
3. Agregar tu IP pública (descúbrela en: https://whatismyipaddress.com/)
4. **Anotar el hostname** (aparece en Dashboard > Site Information > Site IP)

### Paso 2: Descargar pgAdmin

- **Windows/Mac/Linux**: https://www.pgadmin.org/download/

### Paso 3: Conectar desde pgAdmin

1. Abrir pgAdmin
2. Click derecho en **Servers** > **Register** > **Server**
3. Configurar:

   **General Tab:**
   ```
   Name: SiteGround - OmarValdez
   ```

   **Connection Tab:**
   ```
   Host: [tu site IP de SiteGround]
   Port: 5432
   Maintenance database: omarvaldez_db
   Username: omarvaldez_admin
   Password: [tu password]
   ```

4. Click **Save**

### Paso 4: Ejecutar el Schema

1. En pgAdmin, navegar a tu servidor conectado
2. Expandir: **Databases > omarvaldez_db**
3. Click derecho en **omarvaldez_db** > **Query Tool**
4. Abrir el archivo `database/schema.sql` desde tu computadora
5. Copiar TODO el contenido y pegarlo en Query Tool
6. Click en **Execute** (▶️)
7. Repetir con `database/seed_data.sql` si quieres datos de ejemplo

---

## 🔧 **OPCIÓN 3: SSH + psql** (Avanzada)

### Requisitos

- SSH habilitado en tu plan GoGeek ✅
- Conocimientos básicos de terminal

### Paso 1: Conectar por SSH

```bash
# En tu terminal local:
ssh tu_usuario@tu_sitio.com -p 18765

# Puerto SSH en SiteGround suele ser 18765
# Usuario SSH: encontrar en Site Tools > Dev > SSH Keys
```

### Paso 2: Verificar PostgreSQL

```bash
# Una vez conectado por SSH:
which psql

# Si aparece la ruta, PostgreSQL está disponible
```

### Paso 3: Subir los archivos SQL

**Opción A: Usando SCP desde tu computadora local**

```bash
# En terminal LOCAL (no SSH):
scp -P 18765 database/schema.sql tu_usuario@tu_sitio.com:~/schema.sql
scp -P 18765 database/seed_data.sql tu_usuario@tu_sitio.com:~/seed_data.sql
```

**Opción B: Clonar el repositorio en el servidor**

```bash
# En SSH:
cd ~
git clone https://github.com/Omarvaldezp/PersonalWp.git
cd PersonalWp
```

### Paso 4: Ejecutar el Schema

```bash
# Conectar a PostgreSQL y ejecutar:
psql -h localhost -U omarvaldez_admin -d omarvaldez_db -f ~/schema.sql

# Te pedirá el password de PostgreSQL
# Luego ingresa el password que creaste en SiteGround
```

### Paso 5: Ejecutar Seed Data (opcional)

```bash
psql -h localhost -U omarvaldez_admin -d omarvaldez_db -f ~/seed_data.sql
```

### Paso 6: Verificar instalación

```bash
psql -h localhost -U omarvaldez_admin -d omarvaldez_db -c "\dt"
```

Deberías ver la lista de todas las tablas creadas.

---

## 🔍 **Verificar que Todo Funcionó**

Después de cualquier método, verifica:

### 1. Ver tablas creadas

**Desde pgAdmin:**
- Expandir: Databases > omarvaldez_db > Schemas > public > Tables

**Desde SSH:**
```bash
psql -h localhost -U omarvaldez_admin -d omarvaldez_db -c "\dt"
```

Deberías ver:
- ✓ usuarios
- ✓ blog_posts
- ✓ cursos
- ✓ investigaciones
- ✓ contactos
- ✓ newsletter_suscriptores
- ✓ sesiones
- ✓ configuracion
- ✓ analytics

### 2. Verificar usuario admin

**Desde pgAdmin Query Tool:**
```sql
SELECT username, email FROM usuarios WHERE username = 'admin';
```

**Desde SSH:**
```bash
psql -h localhost -U omarvaldez_admin -d omarvaldez_db -c "SELECT username, email FROM usuarios WHERE username = 'admin';"
```

Debería retornar:
```
username | email
---------+---------------------
admin    | omar@omarvaldez.com
```

### 3. Contar registros de ejemplo (si ejecutaste seed_data.sql)

```sql
SELECT
  (SELECT COUNT(*) FROM blog_posts) as blog_posts,
  (SELECT COUNT(*) FROM cursos) as cursos,
  (SELECT COUNT(*) FROM investigaciones) as investigaciones;
```

Debería mostrar:
```
blog_posts | cursos | investigaciones
-----------+--------+----------------
    4      |   5    |       4
```

---

## ❓ Solución de Problemas

### Error: "Connection refused"

**Causa:** PostgreSQL no está escuchando o credenciales incorrectas

**Solución:**
1. Verificar en Site Tools > PostgreSQL > Databases que la DB esté activa
2. Verificar usuario y password
3. Si usas acceso remoto, verificar que tu IP esté autorizada

### Error: "FATAL: password authentication failed"

**Causa:** Password incorrecto

**Solución:**
1. Ir a Site Tools > PostgreSQL > Databases
2. Click en el ícono de "editar" (lápiz) junto al usuario
3. Cambiar password
4. Usar el nuevo password

### Error: "database does not exist"

**Causa:** No has creado la base de datos

**Solución:**
1. Ir a Site Tools > PostgreSQL > Databases
2. Click "Create Database"
3. Crear la base de datos primero

### Error: "permission denied for schema public"

**Causa:** El usuario no tiene permisos suficientes

**Solución:**
```sql
-- Ejecutar como usuario admin de PostgreSQL:
GRANT ALL PRIVILEGES ON DATABASE omarvaldez_db TO omarvaldez_admin;
GRANT ALL PRIVILEGES ON SCHEMA public TO omarvaldez_admin;
```

---

## 🎯 Resumen de Recomendaciones

| Método | Dificultad | Tiempo | Recomendado para |
|--------|-----------|--------|------------------|
| **Script PHP** | ⭐ Fácil | 5 min | Principiantes, instalación rápida |
| **pgAdmin Remoto** | ⭐⭐ Medio | 10 min | Quienes quieran GUI y gestión continua |
| **SSH + psql** | ⭐⭐⭐ Difícil | 15 min | Usuarios avanzados con experiencia CLI |

**Mi recomendación:** Usa la **Opción 1 (Script PHP)** para la instalación inicial, luego configura **Opción 2 (pgAdmin)** para gestión continua de la base de datos.

---

## 📚 Siguiente Paso

Una vez instalada la base de datos, continúa con **BACKEND_SETUP.md** en la sección:
- "Configuración de PHP" → Crear `config.php`
- "Panel de Administración" → Acceder al admin

---

## 🆘 ¿Problemas?

1. Verificar logs en Site Tools > Statistics > Error Log
2. Contactar soporte de SiteGround si PostgreSQL no está disponible
3. Verificar que tu plan GoGeek tenga PostgreSQL habilitado

---

✅ **Una vez completado, tendrás:**
- Base de datos PostgreSQL configurada
- Todas las tablas creadas
- Usuario admin listo para usar
- Datos de ejemplo (opcional)
