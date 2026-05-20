# 📤 PREPARAR REPOSITORIO GIT PARA DEPLOYMENT

## ✅ Antes de hacer Deployment

### 1. Verificar que estos archivos ESTÉN en el repositorio Git

```bash
# Archivos de configuración de deployment (YA CREADOS)
DEPLOYMENT.md
DEPLOYMENT_CHECKLIST.md
START_HERE.md
deploy.sh
nginx.conf
apache.conf
server-commands.sh
.env.production.example

# Archivos del proyecto Laravel
app/
bootstrap/
config/
database/
public/
resources/
routes/
artisan
composer.json
composer.lock
package.json
package-lock.json
```

### 2. Verificar que estos archivos NO ESTÉN en el repositorio Git

```bash
# Estos deben estar en .gitignore
.env                    # ⚠️ IMPORTANTE: Contiene credenciales
.env.backup
.env.production         # El servidor creará su propio .env
/vendor/                # Se instalará con composer
/node_modules/          # Se instalará con npm
/public/hot
/public/storage
/storage/*.key
/storage/logs/
/storage/framework/
.phpunit.result.cache
```

---

## 🔄 SUBIR ARCHIVOS DE DEPLOYMENT AL REPOSITORIO

### Opción 1: Desde VS Code (Recomendado)

1. **Abrir Control de Código Fuente** (icono de Git en la barra lateral)
2. **Ver los cambios** - deberías ver los archivos nuevos:
   - DEPLOYMENT.md
   - DEPLOYMENT_CHECKLIST.md
   - START_HERE.md
   - deploy.sh
   - nginx.conf
   - apache.conf
   - server-commands.sh
   - .env.production.example
   - README.md (actualizado)

3. **Hacer Stage de los archivos**
   - Click en el `+` al lado de cada archivo
   - O click en `+` junto a "Changes" para agregar todos

4. **Commit**
   - Escribir mensaje: `Add deployment configuration for Hostinger VPS`
   - Presionar `✓ Commit`

5. **Push**
   - Click en `...` (más opciones)
   - Click en `Push`

### Opción 2: Desde Terminal

```bash
# Navegar al directorio del proyecto
cd \\172.16.1.44\htdocs\appwebzarza

# Ver estado actual
git status

# Agregar archivos de deployment
git add DEPLOYMENT.md
git add DEPLOYMENT_CHECKLIST.md
git add START_HERE.md
git add deploy.sh
git add nginx.conf
git add apache.conf
git add server-commands.sh
git add .env.production.example
git add README.md

# O agregar todos los cambios
git add .

# Commit
git commit -m "Add deployment configuration for Hostinger VPS"

# Push al repositorio
git push origin master
```

---

## 🔍 VERIFICAR QUE SE SUBIERON CORRECTAMENTE

### En GitHub

1. Abrir: https://github.com/IngDatosZarza/appwebzarza
2. Verificar que estos archivos están presentes:
   - ✅ DEPLOYMENT.md
   - ✅ DEPLOYMENT_CHECKLIST.md
   - ✅ START_HERE.md
   - ✅ deploy.sh
   - ✅ nginx.conf
   - ✅ apache.conf
   - ✅ server-commands.sh
   - ✅ .env.production.example

### Desde Terminal

```bash
# Ver último commit
git log -1

# Ver archivos en el último commit
git show --name-only

# Verificar que el remote está actualizado
git fetch origin
git status
```

---

## ⚠️ IMPORTANTE: Verificar .env NO está en Git

```bash
# Este comando NO debe mostrar .env
git ls-files | grep .env

# Solo debe mostrar .env.example o .env.production.example
```

Si `.env` aparece en el repositorio:
```bash
# Eliminarlo del repositorio (pero mantenerlo local)
git rm --cached .env
git commit -m "Remove .env from repository"
git push origin master
```

---

## 📋 CHECKLIST PRE-DEPLOYMENT

Antes de ir al servidor, verifica:

- [ ] Todos los archivos de deployment están en el repositorio
- [ ] El archivo `.env` NO está en el repositorio (solo .env.example)
- [ ] Hiciste commit de todos los cambios
- [ ] Hiciste push al repositorio remoto
- [ ] Verificaste en GitHub que los archivos están presentes
- [ ] Tienes las credenciales SSH del VPS
- [ ] Tienes las credenciales de la base de datos remota
- [ ] Tienes las credenciales SMTP de Hostinger
- [ ] Tienes las API keys de Oppen

---

## 🚀 SIGUIENTE PASO

Una vez que verificaste que todo está en Git:

1. Abrir **[START_HERE.md](START_HERE.md)**
2. Seguir los 3 pasos para el deployment

---

## 🔄 FLUJO DE TRABAJO RECOMENDADO

### Para Desarrollo Futuro

```
1. Hacer cambios en código local
   ↓
2. Probar localmente
   ↓
3. Commit a Git
   git add .
   git commit -m "descripción cambios"
   ↓
4. Push a GitHub
   git push origin master
   ↓
5. Deployment en servidor
   ssh usuario@servidor
   bash ~/deploy-appwebzarza.sh
```

---

## 📝 COMANDOS GIT ÚTILES

```bash
# Ver estado
git status

# Ver cambios sin stage
git diff

# Ver cambios con stage
git diff --staged

# Ver historial de commits
git log --oneline -10

# Descartar cambios locales
git checkout -- archivo.php

# Ver archivos ignorados
git status --ignored

# Verificar rama actual
git branch

# Ver configuración de remote
git remote -v
```

---

## ✅ LISTO PARA DEPLOYMENT

Si todos los archivos están en GitHub, estás listo para:

👉 **Continuar con [START_HERE.md](START_HERE.md)**
