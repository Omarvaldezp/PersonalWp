# 🆘 Troubleshooting: Archivos No Aparecen Después de Deploy

## Estado Actual
- ✅ Deploy exitoso (según GitHub Actions)
- ❌ No se ven archivos en File Manager
- ❌ Webapp no carga

## Información Necesaria para Diagnosticar

### 1. Logs del Deploy
```
[PEGAR AQUÍ EL OUTPUT COMPLETO DEL ÚLTIMO DEPLOY]
```

### 2. Contenido de /public_html/
```
[LISTAR QUÉ VES EN FILE MANAGER]
```

### 3. Configuración FTP
```
Usuario FTP: ___________________
Directorio del usuario: ___________________
```

---

## Tests de Diagnóstico

### Test 1: Buscar Archivos
- [ ] Busqué "index.html" en File Manager
- [ ] Resultado: ___________________

### Test 2: Verificar Directorios
- [ ] Revisé `/public_html/`
- [ ] Revisé `/www/`
- [ ] Revisé `/home/usuario/public_html/`
- [ ] Los archivos están en: ___________________

### Test 3: Verificar Permisos
- [ ] Usuario FTP tiene acceso a /public_html/
- [ ] Directorio home del usuario FTP: ___________________

---

## Soluciones Temporales

### Opción 1: Subir Manualmente via FTP

Mientras diagnosticamos, puedes subir los archivos manualmente:

1. Descarga FileZilla o usa File Manager
2. Sube estos archivos a `/public_html/`:
   - `src/index.html`
   - `src/main.js`
   - `src/styles/main.css`

### Opción 2: Cambiar Directorio de Deploy

Si el problema es el directorio, podemos cambiar `server-dir` en el workflow.

---

## Próximos Pasos

1. Proporciona los logs del deploy
2. Indica qué ves exactamente en File Manager
3. Confirma el directorio del usuario FTP
4. Con esa info, identificaré el problema exacto
