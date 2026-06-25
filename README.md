# Pro-Vit

Application web multi-distributeurs pour la gestion des produits detergents Pro-Vit, construite sur le meme stack que `safesoft-g2d`:
- Laravel 11
- Blade
- Tailwind CSS via CDN
- Alpine.js via CDN
- Font Awesome
- MySQL

## Modules
- Panel Admin: dashboard, distributeurs, clients, categories, produits, commandes, parametres
- Panel Distributeur: dashboard, mes produits, stocks, mes clients, mes commandes, parametres
- Site public: catalogue par distributeur, panier, inscription, connexion, checkout, suivi commandes

## Comptes de demo
- Admin: `admin@provit-dz.com` / `password`
- Distributeur: `alger@provit-dz.com` / `password`
- Client: `sara@example.com` / `password`

## Installation locale
```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate:fresh --seed
php artisan storage:link
php artisan serve
```

Configurer ensuite votre acces MySQL dans `.env` si necessaire:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=provit
DB_USERNAME=root
DB_PASSWORD=
```

Creer la base MySQL avant la migration, par exemple:

```sql
CREATE DATABASE provit CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Puis lancer:

```bash
php artisan migrate:fresh --seed
php artisan storage:link
php artisan serve
```

Sous Windows PowerShell, utiliser:

```powershell
Copy-Item .env.example .env
```

## Build front
Le projet reutilise Tailwind CDN et Alpine CDN, donc aucun build front lourd n'est necessaire.

## API
- L'application Pro-Vit livre principalement une plateforme web Blade.
- Les endpoints mobiles/PME herites de `safesoft-g2d` ont ete retires du runtime pour eviter d'exposer des routes non alignees avec le modele Pro-Vit.
- Les seuls endpoints conserves sont:
  - `GET /api/v1/status`
  - `GET /api/v1/wilayas`
  - `GET /api/v1/communes/{wilaya}`
- Une collection Postman minimale est disponible dans `docs/postman_collection.json`.

## Push GitHub
```bash
git init
git remote add origin https://github.com/boulouzanacer/provit-dz.com.git
git add .
git commit -m "Initial Pro-Vit application"
git branch -M main
git push -u origin main
```

## Deploiement VPS
Serveur cible:
- IP: `165.227.130.135`
- Domaine: `provit-dz.com`

### 1. Preparer le serveur
- Installer `nginx`, `php8.2-fpm`, `php8.2-mysql`, `php8.2-mbstring`, `php8.2-xml`, `php8.2-curl`, `php8.2-zip`, `php8.2-gd`, `composer`, `git`
- Creer le dossier du site, par exemple `/var/www/provit-dz.com`
- Creer la base MySQL de production et un utilisateur dedie

### 2. Recuperer le projet
```bash
git clone https://github.com/boulouzanacer/provit-dz.com.git /var/www/provit-dz.com
cd /var/www/provit-dz.com
composer install --no-dev --optimize-autoloader
cp .env.example .env
php artisan key:generate
php artisan migrate --force --seed
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Configurer ensuite `.env` pour MySQL:
- `DB_CONNECTION=mysql`
- `DB_HOST`
- `DB_PORT`
- `DB_DATABASE`
- `DB_USERNAME`
- `DB_PASSWORD`

### 3. Configurer Nginx
Pointer le root sur:
- `/var/www/provit-dz.com/public`

Exemple de server block:
```nginx
server {
    listen 80;
    server_name provit-dz.com www.provit-dz.com;
    root /var/www/provit-dz.com/public;
    index index.php index.html;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php8.2-fpm.sock;
    }

    location ~ /\.ht {
        deny all;
    }
}
```

### 4. Checklist post-deploiement
- Configurer le bon `APP_URL`
- Passer `APP_ENV=production` et `APP_DEBUG=false`
- Activer HTTPS avec Certbot
- Verifier les permissions `storage` et `bootstrap/cache`
- Tester les connexions Admin / Distributeur / Client
- Verifier le formulaire commande et le decrement de stock
- Verifier que `public/storage` est accessible
- Verifier que le cron / supervision des services PHP-FPM et Nginx sont actifs
