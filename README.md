# 🎓 Sitio Web Académico - Dr. Omar Valdez Palazuelos

Sitio web profesional para profesor universitario, investigador y consultor especializado en **Blockchain**, **Bitcoin**, **Inteligencia Artificial** y **Fintech**.

---

## 🔥 Stack Tecnológico

- **Frontend:** HTML5, CSS3, JavaScript (ES6+)
- **Backend:** Firebase (Firestore Database)
- **Hosting:** Firebase Hosting
- **Autenticación:** Firebase Auth (futuro)
- **Storage:** Firebase Storage (futuro)

---

## ✨ Características

### Para Visitantes

- 📝 **Blog** - Artículos sobre tecnologías emergentes
- 🎓 **Cursos** - Capacitaciones en Blockchain, IA y Fintech
- 🔬 **Investigaciones** - Publicaciones académicas
- 📧 **Contacto** - Formulario de contacto directo
- 📰 **Newsletter** - Suscripción a boletín

### Para Administradores

- ✅ Panel de administración visual (Firebase Console)
- ✅ Agregar/editar contenido sin programar
- ✅ Base de datos en tiempo real
- ✅ Sin mantenimiento de servidores
- ✅ Analytics incluido
- ✅ Hosting GRATIS con SSL

---

## 🚀 Configuración Rápida

### Prerrequisitos

- Node.js 18+ instalado
- Cuenta de Google
- Git instalado

### Instalación

```bash
# 1. Clonar repositorio
git clone https://github.com/Omarvaldezp/PersonalWp.git
cd PersonalWp

# 2. Instalar dependencias
npm install

# 3. Configurar Firebase
# Lee la guía completa en: FIREBASE_SETUP_GUIDE.md

# 4. Actualizar configuración
# Edita src/firebase/config.js con tu configuración de Firebase

# 5. Probar localmente
npx firebase serve

# 6. Deploy a producción
npx firebase deploy
```

---

## 📖 Documentación

### Guías Disponibles

- **[FIREBASE_SETUP_GUIDE.md](FIREBASE_SETUP_GUIDE.md)** - Guía completa paso a paso (⭐ EMPIEZA AQUÍ)
- **[BACKEND_SETUP.md](BACKEND_SETUP.md)** - Setup anterior con PHP/PostgreSQL (legacy)
- **[POSTGRESQL_SETUP_OPTIONS.md](POSTGRESQL_SETUP_OPTIONS.md)** - Opciones de PostgreSQL (legacy)

### Estructura del Proyecto

```
PersonalWp/
├── src/
│   ├── index.html              # Página principal (versión Firebase)
│   ├── styles/
│   │   └── main.css           # Estilos
│   ├── firebase/
│   │   ├── config.js          # Configuración Firebase
│   │   ├── firebase.js        # Inicialización
│   │   └── services/          # Servicios CRUD
│   │       ├── blogService.js
│   │       ├── courseService.js
│   │       ├── researchService.js
│   │       ├── contactService.js
│   │       └── newsletterService.js
│   └── admin/ (legacy PHP)
├── firestore.rules             # Reglas de seguridad Firestore
├── firestore.indexes.json      # Índices de Firestore
├── firebase.json               # Configuración Firebase Hosting
└── package.json                # Dependencias del proyecto
```

---

## 🎛️ Panel de Administración

### Acceso

1. Ir a: **https://console.firebase.google.com/**
2. Seleccionar proyecto: `omarvaldez-web`
3. Navegar a **Firestore Database**

### Gestión de Contenido

#### Agregar Nuevo Blog Post

```
1. Firestore Database > blog_posts
2. Click "Agregar documento"
3. Llenar campos:
   - titulo: "Tu título"
   - slug: "tu-titulo"
   - extracto: "Resumen corto"
   - contenido: "Contenido completo"
   - categorias: ["Blockchain", "Bitcoin"]
   - estado: "publicado"
   - fecha_publicacion: (timestamp actual)
4. Guardar
```

---

## 🌐 URLs del Proyecto

### Desarrollo

- **Local:** http://localhost:5000 (con `firebase serve`)

### Producción

- **Firebase Hosting:** https://omarvaldez-web.web.app
- **Dominio Custom:** https://omarvaldez.com (configurar en Firebase Console)

---

## 🔒 Seguridad

### Reglas de Firestore

- ✅ Lectura pública de contenido publicado
- ✅ Solo admin puede escribir (desde Firebase Console)
- ✅ Formularios pueden crear documentos
- ✅ Protección contra acceso no autorizado

---

## 💰 Costos

### Firebase Free Tier (Spark Plan)

**Completamente GRATIS:**

- ✅ 50,000 lecturas/día
- ✅ 20,000 escrituras/día
- ✅ 1GB almacenamiento
- ✅ 10GB bandwidth/mes
- ✅ Hosting con SSL
- ✅ Analytics ilimitado

**Tu sitio académico cabe PERFECTO en el tier gratuito.**

---

## 🛠️ Desarrollo

### Scripts Disponibles

```bash
# Servir con Firebase Hosting local
npm run firebase:serve

# Deploy a producción
npm run firebase:deploy
```

---

## 📄 Licencia

MIT License - Omar Valdez Palazuelos

---

**Hecho con ❤️ para la educación en tecnologías emergentes**
