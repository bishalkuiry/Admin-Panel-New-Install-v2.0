# Quixko - Installation Guide

## Quick Installation (cPanel / Shared Hosting)

### Step 1: Upload Files
1. Download and extract the application ZIP file
2. Upload ALL files directly to your domain folder (e.g., `public_html`)

### Step 2: Create Database
1. Login to cPanel → MySQL Databases
2. Create a new database (e.g., `username_quixko`)
3. Create a database user with a strong password
4. Add the user to the database with ALL PRIVILEGES

### Step 3: Set Permissions
Make sure these folders are writable (chmod 755 or 775):
```
storage/
bootstrap/cache/
```

### Step 4: Run Web Installer
1. Open your browser and go to: `https://yourdomain.com/`
2. Follow the installation wizard:
   - **Step 0**: Welcome & Requirements overview
   - **Step 1**: Server requirements check (PHP 8.2+, extensions)
   - **Step 2**: Database configuration
   - **Step 3**: Database migration/setup
   - **Step 4**: Admin account creation
   - **Step 5**: Installation complete!

### Step 5: Access Admin Panel
After installation, access your admin panel at:
`https://yourdomain.com/admin`

---

## Server Requirements

- PHP 8.2 or higher
- MySQL 5.7+ or MariaDB 10.3+
- Required PHP Extensions:
  - BCMath, Ctype, cURL, DOM
  - Fileinfo, JSON, Mbstring
  - OpenSSL, PDO, PDO_MySQL
  - Tokenizer, XML, ZIP, GD

---

## License Verification

This software requires a valid license.

License Server: `https://licence.blulands.com`

Each license is valid for:
- One domain only
- Lifetime updates
- Technical support

---

## Manual Installation (Advanced)

If the web installer doesn't work:

```bash
# 1. Copy environment file
cp .env.example .env

# 2. Edit .env with your database settings

# 3. Generate application key
php artisan key:generate

# 4. Run migrations
php artisan migrate --force

# 5. Create storage link
php artisan storage:link

# 6. Clear caches
php artisan config:clear
php artisan cache:clear

# 7. Create installed marker
echo '{"installed_at":"now","version":"1.0.0"}' > storage/installed

# 8. Switch to normal routes
cp installation/activate_normal_routes.txt bootstrap/app.php
```

---

## Troubleshooting

### "500 Internal Server Error"
- Check file permissions (storage, bootstrap/cache)
- Check PHP version (requires 8.2+)
- Check error logs in `storage/logs/laravel.log`

### "Database connection failed"
- Verify database credentials
- Make sure database exists
- Check if MySQL user has proper permissions

---

## Security Recommendations

After installation:
1. ✅ Change default admin password
2. ✅ Set `APP_DEBUG=false` in `.env`
3. ✅ Set `APP_ENV=production` in `.env`
4. ✅ Set up SSL certificate (HTTPS)
5. ✅ Regular backups

---

© 2025 Quixko. All rights reserved.
