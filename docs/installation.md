# iNettotik ISP Billing System - Installation Guide

## System Requirements
- Ubuntu 22.04 LTS (recommended)
- PHP 8.1+ (8.2 or 8.3 recommended for new installations)
- MySQL 8.0+
- Nginx or Apache
- Composer 2.x
- Node.js 18+ (for frontend assets)
- FreeRADIUS 3.x

## 1. Server Setup

```bash
# Update system
apt update && apt upgrade -y

# Install PHP 8.2 (or replace 8.2 with 8.1/8.3 as needed)
add-apt-repository ppa:ondrej/php -y
apt install php8.2 php8.2-fpm php8.2-mysql php8.2-mbstring php8.2-xml \
    php8.2-curl php8.2-zip php8.2-gd php8.2-bcmath -y

# Install MySQL
apt install mysql-server -y
mysql_secure_installation

# Install Nginx
apt install nginx -y

# Install Node.js
curl -fsSL https://deb.nodesource.com/setup_18.x | bash -
apt install nodejs -y

# Install Composer
curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer
```

## 2. Database Setup

```bash
mysql -u root -p
```

```sql
CREATE DATABASE inettotik CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'inettotik'@'localhost' IDENTIFIED BY 'strong_password_here';
GRANT ALL PRIVILEGES ON inettotik.* TO 'inettotik'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

## 3. Laravel Installation

```bash
cd /var/www
git clone https://github.com/toysmedia/iNettotik.git
cd iNettotik

# Install PHP dependencies
composer install --no-dev --optimize-autoloader

# Install Node dependencies and build assets
npm install && npm run build

# Environment setup
cp .env.example .env
php artisan key:generate

# Edit .env
nano .env
```

Configure `.env`:
```
APP_URL=https://billing.yourisp.co.ke
DB_DATABASE=inettotik
DB_USERNAME=inettotik
DB_PASSWORD=strong_password_here

RADIUS_SERVER_IP=127.0.0.1
MPESA_ENV=production
MPESA_CONSUMER_KEY=your_key
MPESA_CONSUMER_SECRET=your_secret
MPESA_SHORTCODE=your_shortcode
MPESA_PASSKEY=your_passkey
MPESA_CALLBACK_URL=https://billing.yourisp.co.ke/api/mpesa/stk-callback
```

```bash
# Run migrations
php artisan migrate --force

# Storage link
php artisan storage:link

# Cache config
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Set permissions
chown -R www-data:www-data /var/www/iNettotik
chmod -R 755 /var/www/iNettotik/storage
```

## 4. Nginx Configuration

```nginx
server {
    listen 80;
    server_name billing.yourisp.co.ke;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl;
    server_name billing.yourisp.co.ke;
    root /var/www/iNettotik/public;
    index index.php;

    ssl_certificate /etc/letsencrypt/live/billing.yourisp.co.ke/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/billing.yourisp.co.ke/privkey.pem;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;  # adjust version as needed
    }
}
```

## 5. FreeRADIUS Installation

```bash
apt install freeradius freeradius-mysql -y

# See docs/freeradius-sql-config.md for full configuration
```

## 6. Cron Setup

```bash
crontab -e -u www-data
```

Add:
```
* * * * * cd /var/www/iNettotik && php artisan schedule:run >> /dev/null 2>&1
```

## 7. SSL Certificate

```bash
apt install certbot python3-certbot-nginx -y
certbot --nginx -d billing.yourisp.co.ke
```

## 8. M-Pesa Daraja Setup

1. Go to https://developer.safaricom.co.ke
2. Create an app, get Consumer Key and Secret
3. For production: submit for Go-Live approval
4. Register C2B URLs: `GET /admin/isp/settings` → run from admin panel

## 9. Admin Access

Default admin credentials are set during installation. Access at:
- URL: https://billing.yourisp.co.ke/admin
- Create admin via: `php artisan tinker` → `App\Models\Admin::create([...])`

## 10. MikroTik Integration

1. Add router in Admin → ISP → Routers
2. Click "Generate Script"
3. Copy and paste the script into MikroTik Terminal
4. Download Hotspot Files and upload to MikroTik's `/hotspot` folder
