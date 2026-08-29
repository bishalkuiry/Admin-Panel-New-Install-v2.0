# InAllCart — Admin Panel

Laravel-based backend and admin panel for the InAllCart platform. Manages stores, products, orders, delivery partners, payments, and push notifications for the User, Store, and Driver apps.

## Prerequisites

- PHP ≥ 8.2
- Composer
- MySQL / MariaDB (or SQLite for local development)
- Node.js ≥ 18 + npm (for frontend assets)
- A web server: Apache / Nginx (or `php artisan serve` for local)
- A Firebase project (for push notifications and Realtime Database)

## 1. Copy Environment File

**Never edit `.env` directly from the repo.** Copy the example file and fill in your values:

```bash
cp .env.example .env
```

Key values to update in `.env`:

```env
APP_NAME=InAllCart
APP_ENV=production
APP_KEY=                        # generated in step 3
APP_DEBUG=false
APP_URL=https://yourdomain.com

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database_name
DB_USERNAME=your_db_user
DB_PASSWORD=your_db_password

# Mobile App Settings
APP_SCHEME=inallcart            # must match app deep link scheme
APP_PACKAGE=com.yourcompany.userapp
APP_STORE_ID=YOUR_APP_STORE_ID

# Mail (for order confirmations, password resets)
MAIL_MAILER=smtp
MAIL_HOST=your.smtp.host
MAIL_PORT=587
MAIL_USERNAME=your@email.com
MAIL_PASSWORD=your_mail_password
MAIL_FROM_ADDRESS=noreply@yourdomain.com
MAIL_FROM_NAME=InAllCart
```

## 2. Install PHP Dependencies

```bash
composer install --optimize-autoloader --no-dev
```

## 3. Generate Application Key

```bash
php artisan key:generate
```

## 4. Run Database Migrations & Seeders

```bash
php artisan migrate --force
php artisan db:seed
```

## 5. Install & Build Frontend Assets

```bash
npm install
npm run build
```

## 6. Link Storage

```bash
php artisan storage:link
```

## 7. Configure Firebase (Push Notifications & Realtime Database)

1. Go to the [Firebase Console](https://console.firebase.google.com/) and open your project.
2. Go to **Project Settings → Service Accounts** → **Generate new private key**.
3. Save the downloaded JSON file to `storage/app/firebase-credentials.json`.
4. Add the path to `.env`:
   ```env
   FIREBASE_CREDENTIALS=storage/app/firebase-credentials.json
   FIREBASE_DATABASE_URL=https://your-project-id-default-rtdb.firebaseio.com
   ```
5. Deploy the Realtime Database security rules:
   - Open **Firebase Console → Realtime Database → Rules**.
   - Paste the contents of `firebase-database-rules.json` from this folder and publish.

## 8. Configure Queue Worker (for notifications and emails)

For production, run the queue worker as a background service (e.g. Supervisor):

```bash
php artisan queue:work --sleep=3 --tries=3
```

Example Supervisor config (`/etc/supervisor/conf.d/inallcart-worker.conf`):
```ini
[program:inallcart-worker]
command=php /var/www/html/artisan queue:work --sleep=3 --tries=3
autostart=true
autorestart=true
user=www-data
```

## 9. Set File Permissions (Linux/macOS)

```bash
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

## 10. Run Locally (Development)

```bash
php artisan serve
npm run dev
```

Then open `http://localhost:8000` in your browser.

## 11. Default Admin Credentials

After seeding, log in with the default admin account created by the seeder. **Change the password immediately after first login** via the admin panel settings.

Check `database/seeders/` for the default credentials configured during seeding.

## 12. App Signing & Store Deployment

See the **App Signing & Deployment** section in the Offline Documentation for instructions on signing and publishing the Flutter apps that connect to this backend.
