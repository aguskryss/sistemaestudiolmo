# Publicar en Hostinger (plan Premium)

## 1. Preparar en tu computadora

```bash
composer install --no-dev --optimize-autoloader
npm ci && npm run build          # genera public/build (Hostinger no corre Node)
```

## 2. Subir el proyecto

Por SSH (hPanel → Avanzado → Acceso SSH) o Git (hPanel → Avanzado → GIT):

- Subí el proyecto **fuera** de `public_html`, por ejemplo a `~/sistema`.
- Hacé que el dominio apunte a `~/sistema/public`. En Premium la forma más simple es reemplazar `public_html` por un enlace:
  ```bash
  rm -rf ~/domains/TU-DOMINIO/public_html
  ln -s ~/sistema/public ~/domains/TU-DOMINIO/public_html
  ```
  Así `.env`, `storage/` (donde quedan los planos) y el código **nunca** son accesibles desde la web.
- Subí también `public/build` (está en `.gitignore`, así que si usás Git hay que subirlo aparte o compilar y commitear en una rama de deploy).

## 3. `.env` de producción

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://tu-dominio.com

DB_HOST=localhost              # en el servidor la base es local
SESSION_SECURE_COOKIE=true
SESSION_ENCRYPT=true

MAIL_MAILER=smtp               # cuenta de correo creada en hPanel → Emails
MAIL_HOST=smtp.hostinger.com
MAIL_PORT=465
MAIL_SCHEME=smtps
MAIL_USERNAME=sistema@tu-dominio.com
MAIL_PASSWORD=...
MAIL_FROM_ADDRESS=sistema@tu-dominio.com
MAIL_FROM_NAME="Estudio"
```

Generá una `APP_KEY` nueva con `php artisan key:generate` (no reutilices la de desarrollo).

## 4. Comandos en el servidor

```bash
cd ~/sistema
php artisan migrate --force
php artisan config:cache && php artisan route:cache && php artisan view:cache
chmod -R 775 storage bootstrap/cache
```

En hPanel → Avanzado → Configuración PHP: versión **8.3**, `upload_max_filesize` y `post_max_size` en **128M** (el sistema acepta hasta 100 MB por archivo).

## 5. Cron (recordatorios por email)

hPanel → Avanzado → Cron Jobs → cada minuto:

```
/usr/bin/php /home/USUARIO/sistema/artisan schedule:run >> /dev/null 2>&1
```

- `recordatorios:enviar` corre cada 5 minutos.
- `recordatorios:vencimientos` corre todos los días a las 07:00 (seguros que vencen en 15 días, permisos en 30).

## 6. Después de publicar

- Activá SSL (hPanel → Seguridad → SSL).
- En hPanel → Bases de datos → MySQL remoto: **quitá el acceso remoto** (o limitalo a tu IP) y cambiá la contraseña de la base.
- Backups: hPanel hace copias diarias en Premium; además conviene descargar `storage/app/private` de vez en cuando.
