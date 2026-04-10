# iNettotik ISP Billing System — CyberPanel Deployment Guide

Complete production-ready guide for deploying iNettotik on a **CyberPanel** (OpenLiteSpeed) server, including Hotspot and PPPoE testing.

---

## Table of Contents

1. [Prerequisites & Server Requirements](#1-prerequisites--server-requirements)
2. [CyberPanel Website Setup](#2-cyberpanel-website-setup)
3. [Database Setup via CyberPanel](#3-database-setup-via-cyberpanel)
4. [Deploy the Code](#4-deploy-the-code)
5. [OpenLiteSpeed Configuration](#5-openlitespeed-configuration)
6. [FreeRADIUS Installation & Configuration](#6-freeradius-installation--configuration)
7. [Cron Job Setup](#7-cron-job-setup)
8. [M-Pesa Daraja Configuration](#8-m-pesa-daraja-configuration)
9. [First Admin Account Creation](#9-first-admin-account-creation)
10. [Testing — Hotspot Flow](#10-testing--hotspot-flow-end-to-end)
11. [Testing — PPPoE Flow](#11-testing--pppoe-flow-end-to-end)
12. [Testing — POS / Reseller Flow](#12-testing--pos--reseller-flow)
13. [Troubleshooting](#13-troubleshooting)
14. [Security Checklist](#14-security-checklist)

---

## 1. Prerequisites & Server Requirements

| Requirement | Minimum | Recommended |
|-------------|---------|-------------|
| OS | Ubuntu 22.04 LTS | Ubuntu 22.04 LTS |
| RAM | 2 GB | 4 GB |
| vCPUs | 2 | 4 |
| Disk | 20 GB SSD | 40 GB SSD |
| PHP | 8.1 | 8.2 |
| MySQL / MariaDB | 8.0 / 10.6 | MySQL 8.0 / MariaDB 10.6+ |
| Composer | 2.x | 2.x |
| Node.js | 18+ | 18+ |

**Before you start:**

- CyberPanel is already installed (with OpenLiteSpeed).
- A domain name (e.g., `billing.yourisp.co.ke`) is pointed to the server's public IP.
- You have SSH root access to the server.
- FreeRADIUS will be installed on the same server (or a dedicated RADIUS server reachable by port 1812/1813 UDP).

---

## 2. CyberPanel Website Setup

### 2.1 Create the Website

1. Log in to CyberPanel at `https://YOUR_SERVER_IP:8090`.
2. Navigate to **Websites → Create Website**.
3. Fill in the form:
   - **Select Package**: Default or a custom package with sufficient resources.
   - **Owner**: `admin` (or your CyberPanel user).
   - **Domain Name**: `billing.yourisp.co.ke` (no `www` prefix — add an alias later if needed).
   - **Email**: your admin email.
   - **PHP**: `8.2` (select from the dropdown; install via **PHP → Install PHP** if not listed).
   - **SSL**: Leave unchecked for now — you will issue SSL in the next step.
4. Click **Create Website**.

### 2.2 Issue SSL via Let's Encrypt

1. Navigate to **SSL → Manage SSL**.
2. Select your domain from the dropdown.
3. Click **Issue SSL**.
4. CyberPanel will obtain a Let's Encrypt certificate and configure HTTPS automatically.

### 2.3 Set PHP Version to 8.2

1. Navigate to **Websites → List Websites**.
2. Click **Manage** next to your domain.
3. Under **PHP**, change the version to **8.2** and click **Save Changes**.

### 2.4 Set the Document Root to `public/`

CyberPanel's default document root for a website is `/home/yourdomain.com/public_html`.  
The Laravel `public/` folder must be the web root. You will configure this in **Part 5** after deploying the code, using a rewrite rule so that CyberPanel's vhost still serves correctly.

> **Note:** CyberPanel does not expose a native GUI field to change the document root for OLS vhosts. The recommended approach is to deploy the full Laravel project into `public_html/` and configure a rewrite rule to redirect all requests through `public/`, which the existing root `.htaccess` already handles (see [Part 5](#5-openlitespeed-configuration)).

---

## 3. Database Setup via CyberPanel

### 3.1 Create Database and User

1. Navigate to **Databases → Create Database**.
2. Fill in:
   - **Website**: select `billing.yourisp.co.ke`.
   - **Database Name**: `inettotik` (CyberPanel will prefix it with the website user, e.g., `billingY_inettotik` — note the full name shown after creation).
   - **Username**: `inettotik` (CyberPanel will prefix similarly, e.g., `billingY_inettotik`).
   - **Password**: choose a strong password (save it — you will need it in `.env`).
3. Click **Create Database**.

### 3.2 Note the Full Credentials

After creation, CyberPanel shows the full database name and username. Record:

```
DB_DATABASE=billingY_inettotik   # exact name shown by CyberPanel
DB_USERNAME=billingY_inettotik   # exact username shown by CyberPanel
DB_PASSWORD=your_strong_password
DB_HOST=127.0.0.1
DB_PORT=3306
```

You can also verify from the command line:

```bash
mysql -u root -p -e "SHOW DATABASES LIKE '%inettotik%'; SELECT user, host FROM mysql.user WHERE user LIKE '%inettotik%';"
```

### 3.3 Grant Privileges (if needed)

CyberPanel grants `ALL PRIVILEGES` automatically. If you need to verify or fix:

```bash
mysql -u root -p
GRANT ALL PRIVILEGES ON billingY_inettotik.* TO 'billingY_inettotik'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

---

## 4. Deploy the Code

### 4.1 SSH into the Server

```bash
ssh root@YOUR_SERVER_IP
```

### 4.2 Install Required System Packages

```bash
# Node.js 18 (if not already installed)
curl -fsSL https://deb.nodesource.com/setup_18.x | bash -
apt-get install -y nodejs

# Composer 2 (if not already installed)
curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Verify versions
php -v          # should be 8.2.x
composer -V     # should be 2.x
node -v         # should be v18.x or higher
npm -v
```

### 4.3 Clone the Repository

```bash
cd /home/billing.yourisp.co.ke/public_html

# Remove the default index file if present
rm -f index.html

# Clone iNettotik into the current directory
git clone https://github.com/toysmedia/oxnett.git .
```

> Replace `billing.yourisp.co.ke` with your actual domain.

### 4.4 Install PHP Dependencies

```bash
composer install --no-dev --optimize-autoloader
```

### 4.5 Install Node.js Dependencies and Build Frontend Assets

```bash
npm install
npm run build
```

### 4.6 Configure Environment

```bash
cp .env.example .env
php artisan key:generate
```

Now edit `.env` with your actual values:

```bash
nano .env
```

#### Full `.env` Reference

```dotenv
# ─── Application ───────────────────────────────────────────────────────────────
APP_NAME=iNetto
APP_ENV=production
APP_KEY=                          # auto-filled by key:generate
APP_DEBUG=false
APP_URL=https://billing.yourisp.co.ke
LOG_CHANNEL=stack
LOG_LEVEL=error

# ─── Database ──────────────────────────────────────────────────────────────────
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=billingY_inettotik    # exact name from CyberPanel
DB_USERNAME=billingY_inettotik    # exact username from CyberPanel
DB_PASSWORD=your_strong_password

# ─── Cache / Queue / Session ────────────────────────────────────────────────────
CACHE_DRIVER=file
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120

# ─── ISP / RADIUS ──────────────────────────────────────────────────────────────
RADIUS_SERVER_IP=127.0.0.1        # IP of the FreeRADIUS server
RADIUS_SECRET=your_radius_secret  # must match clients.conf in FreeRADIUS
ROUTER_CALLBACK_SECRET=           # random secret for MikroTik auto-registration
BILLING_DOMAIN=billing.yourisp.co.ke
IS_DEMO=0
VERSION=1.0
MANAGEMENT_IPS=192.168.88.1,10.0.0.1   # comma-separated allowed IPs for management

# ─── M-Pesa Daraja ──────────────────────────────────────────────────────────────
MPESA_ENV=sandbox                 # change to 'production' when going live
MPESA_CONSUMER_KEY=your_consumer_key
MPESA_CONSUMER_SECRET=your_consumer_secret
MPESA_SHORTCODE=174379            # your Lipa na M-Pesa paybill / till
MPESA_PASSKEY=your_lnmo_passkey
MPESA_CALLBACK_URL=https://billing.yourisp.co.ke/api/mpesa/stk-callback
MPESA_C2B_SHORTCODE=174379        # usually same as MPESA_SHORTCODE
MPESA_C2B_CONFIRMATION_URL=https://billing.yourisp.co.ke/api/mpesa/c2b-confirmation
MPESA_C2B_VALIDATION_URL=https://billing.yourisp.co.ke/api/mpesa/c2b-validation
MPESA_VERIFY_IP=true              # set to false only during sandbox testing if needed

# ─── SMS Providers ──────────────────────────────────────────────────────────────
SMS_DRIVER=africastalking         # options: africastalking | blessedafrica | advanta

# Africa's Talking
AT_USERNAME=sandbox               # change to your AT username in production
AT_API_KEY=your_at_api_key
AT_SENDER_ID=                     # optional — leave blank to use shortcode

# Blessed Africa (alternative)
BLESSED_AFRICA_API_KEY=
BLESSED_AFRICA_SENDER_ID=

# Advanta SMS (alternative)
ADVANTA_API_KEY=
ADVANTA_SENDER_ID=

# ─── Mail ───────────────────────────────────────────────────────────────────────
MAIL_MAILER=smtp
MAIL_HOST=smtp.yourmail.com
MAIL_PORT=587
MAIL_USERNAME=your_mail_username
MAIL_PASSWORD=your_mail_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@yourisp.co.ke
MAIL_FROM_NAME="${APP_NAME}"
```

> Save and exit (`Ctrl+O`, `Enter`, `Ctrl+X` in nano).

### 4.7 Run Database Migrations

```bash
php artisan migrate --force
```

### 4.8 Create the Storage Symlink

```bash
php artisan storage:link
```

### 4.9 Cache Configuration for Production

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## 5. OpenLiteSpeed Configuration

### 5.1 How the Root `.htaccess` Works

The repository includes a root `.htaccess` at `/home/billing.yourisp.co.ke/public_html/.htaccess`:

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteCond %{REQUEST_URI} !^/public/
    RewriteRule ^(.*)$ public/$1 [L,QSA]
</IfModule>

# PHP Execution Settings for LiteSpeed
<IfModule Litespeed>
    SetEnv PHP_ENABLE 1
    SetEnv LS_PHP_ENABLE 1
</IfModule>
```

This rule redirects all requests through the `public/` folder without exposing it in the URL. The `SetEnv` directives enable PHP execution under LiteSpeed/OpenLiteSpeed.

The `public/.htaccess` handles Laravel's front-controller routing:

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteCond %{HTTP:Authorization} .
    RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_URI} (.+)/$
    RewriteRule ^ %1 [L,R=301]
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
</IfModule>
```

### 5.2 Enable `.htaccess` Rewrite in CyberPanel vHost

CyberPanel / OLS may require explicitly enabling `.htaccess` overrides:

1. Navigate to **Websites → List Websites → Manage** (your domain).
2. Click **vHost Conf** (or open the raw vhost config file at `/usr/local/lsws/conf/vhosts/billing.yourisp.co.ke/vhconf.conf`).
3. Ensure the document root points to `public_html`:

```apache
docRoot                   /home/billing.yourisp.co.ke/public_html
```

4. Add or verify the following context to allow `.htaccess` rewrite:

```apache
context / {
  location                /home/billing.yourisp.co.ke/public_html
  allowBrowse             0
  rewrite  {
    enable                1
    inherit               1
  }
}
```

5. After editing, click **Save** and then **Graceful Restart** from **CyberPanel → Server → Restart Services → OpenLiteSpeed**.

### 5.3 File Permissions

OpenLiteSpeed runs as the `nobody` user by default. Set correct ownership and permissions:

```bash
cd /home/billing.yourisp.co.ke/public_html

# Ownership for OLS
chown -R nobody:nobody storage/ bootstrap/cache/

# Directory and file permissions
chmod -R 755 storage/ bootstrap/cache/

# If PHP-FPM runs as a different user (check with: ps aux | grep php)
# chown -R www-data:www-data storage/ bootstrap/cache/
```

Verify the web user:

```bash
ps aux | grep lsphp | head -3
```

If the process owner differs from `nobody`, use that user in the `chown` command above.

### 5.4 Restart OLS

```bash
/usr/local/lsws/bin/lswsctrl restart
# or from CyberPanel dashboard: Server → Restart Services → OpenLiteSpeed
```

### 5.5 Verify the Site Loads

Open `https://billing.yourisp.co.ke` in a browser. You should see the iNettotik home / login page. If you get a 500 error, check logs:

```bash
tail -f /home/billing.yourisp.co.ke/public_html/storage/logs/laravel.log
tail -f /usr/local/lsws/logs/error.log
```

---

## 6. FreeRADIUS Installation & Configuration

> For detailed SQL module configuration, refer to [`docs/freeradius-sql-config.md`](freeradius-sql-config.md).

### 6.1 Install FreeRADIUS

```bash
apt update
apt install -y freeradius freeradius-mysql
```

### 6.2 Enable the SQL Module

```bash
ln -s /etc/freeradius/3.0/mods-available/sql /etc/freeradius/3.0/mods-enabled/sql
```

### 6.3 Configure the SQL Module

Edit `/etc/freeradius/3.0/mods-available/sql`:

```bash
nano /etc/freeradius/3.0/mods-available/sql
```

Key settings to update:

```text
driver = "rlm_sql_mysql"

dialect = "mysql"

server = "127.0.0.1"
port = 3306
login = "billingY_inettotik"
password = "your_strong_password"
radius_db = "billingY_inettotik"

# Table names (these match the Laravel migrations)
acct_table1 = "radacct"
acct_table2 = "radacct"
postauth_table = "radpostauth"
authcheck_table = "radcheck"
groupcheck_table = "radgroupcheck"
authreply_table = "radreply"
groupreply_table = "radgroupreply"
usergroup_table = "radusergroup"

# Read NAS clients from the database
read_clients = yes
nas_table = "nas"
```

### 6.4 Configure the Default Site

Edit `/etc/freeradius/3.0/sites-available/default`:

```bash
nano /etc/freeradius/3.0/sites-available/default
```

Add `sql` to the `authorize`, `accounting`, and `post-auth` sections:

```text
authorize {
    ...
    sql
    ...
}

accounting {
    ...
    sql
    ...
}

post-auth {
    ...
    sql
    Post-Auth-Type REJECT {
        sql
    }
}
```

### 6.5 Configure Inner-Tunnel

Edit `/etc/freeradius/3.0/sites-available/inner-tunnel` and add `sql` to the `authorize` and `post-auth` sections in the same way.

### 6.6 Configure the RADIUS Client (NAS)

iNettotik automatically populates the `nas` table when you add a router. To add a test/default client manually:

Edit `/etc/freeradius/3.0/clients.conf`:

```bash
nano /etc/freeradius/3.0/clients.conf
```

Add:

```text
client localhost {
    ipaddr = 127.0.0.1
    secret = your_radius_secret       # must match RADIUS_SECRET in .env
    shortname = localhost
}
```

### 6.7 Test Configuration and Start Service

```bash
# Check configuration syntax
freeradius -XC

# Start in debug mode to verify (press Ctrl+C to stop)
freeradius -X

# Once verified, enable and start as a systemd service
systemctl enable freeradius
systemctl start freeradius
systemctl status freeradius
```

### 6.8 Test Authentication

```bash
radtest testuser testpass 127.0.0.1 0 your_radius_secret
```

You should see `Access-Accept` once a user with those credentials exists in `radcheck`. A failed response of `Access-Reject` with no errors means FreeRADIUS is running correctly; the user just does not exist yet.

### 6.9 Open Firewall Ports for RADIUS

```bash
ufw allow 1812/udp comment "FreeRADIUS Authentication"
ufw allow 1813/udp comment "FreeRADIUS Accounting"
```

---

## 7. Cron Job Setup

The Laravel scheduler must run every minute to handle subscriber expiry, invoice generation, and other timed tasks.

### Option A — CyberPanel Cron Jobs UI

1. Navigate to **Cron Jobs → Create Cron Job**.
2. Set **Select User**: the website Linux user (e.g., `billing.yourisp.co.ke` or `nobody`).
3. Set the schedule to `* * * * *` (every minute).
4. Set the command:

```bash
cd /home/billing.yourisp.co.ke/public_html && php artisan schedule:run > /dev/null 2>&1
```

5. Click **Add Cron Job**.

### Option B — Manual crontab

```bash
crontab -e
```

Add the line:

```cron
* * * * * cd /home/billing.yourisp.co.ke/public_html && php artisan schedule:run > /dev/null 2>&1
```

---

## 8. M-Pesa Daraja Configuration

### 8.1 Create a Daraja App

1. Go to [https://developer.safaricom.co.ke](https://developer.safaricom.co.ke) and sign up.
2. Create a new app (select **Lipa na M-Pesa Online** and **M-Pesa Express** products).
3. Note your **Consumer Key** and **Consumer Secret**.

### 8.2 Sandbox Setup

For sandbox testing, use the pre-configured Safaricom sandbox shortcode `174379` and the published sandbox passkey.

Update `.env`:

```dotenv
MPESA_ENV=sandbox
MPESA_CONSUMER_KEY=your_sandbox_consumer_key
MPESA_CONSUMER_SECRET=your_sandbox_consumer_secret
MPESA_SHORTCODE=174379
MPESA_PASSKEY=bfb279f9aa9bdbcf158e97dd71a467cd2e0c893059b10f78e6b72ada1ed2c919
MPESA_CALLBACK_URL=https://billing.yourisp.co.ke/api/mpesa/stk-callback
```

> **Important:** The callback URL must be publicly accessible (HTTPS). Safaricom cannot reach a local/private IP.

### 8.3 Register C2B URLs

Once the application is running, register the C2B confirmation and validation URLs via the admin panel:

1. Log in to the admin panel at `https://billing.yourisp.co.ke/admin`.
2. Navigate to **Settings → M-Pesa Settings** (or run the registration endpoint directly):

```bash
curl -X GET https://billing.yourisp.co.ke/api/mpesa/register-c2b \
     -H "Authorization: Bearer YOUR_SANCTUM_TOKEN"
```

This registers:
- `https://billing.yourisp.co.ke/api/mpesa/c2b-confirmation`
- `https://billing.yourisp.co.ke/api/mpesa/c2b-validation`

### 8.4 Verify Webhook Accessibility

Ensure all three M-Pesa webhook endpoints return a `200` or `405` response (method check) when accessed:

```bash
curl -I https://billing.yourisp.co.ke/api/mpesa/stk-callback
curl -I https://billing.yourisp.co.ke/api/mpesa/c2b-confirmation
curl -I https://billing.yourisp.co.ke/api/mpesa/c2b-validation
```

### 8.5 Switch to Production

When you are ready to go live:

```dotenv
MPESA_ENV=production
MPESA_CONSUMER_KEY=your_production_consumer_key
MPESA_CONSUMER_SECRET=your_production_consumer_secret
MPESA_SHORTCODE=your_live_paybill
MPESA_PASSKEY=your_live_passkey
```

Then re-cache the config:

```bash
php artisan config:cache
```

---

## 9. First Admin Account Creation

### 9.1 Using the Installation Wizard

If the database is freshly migrated, visit `https://billing.yourisp.co.ke/install` in your browser to complete the setup wizard, which will create the initial admin user.

### 9.2 Using Artisan Tinker

If you prefer the command line:

```bash
cd /home/billing.yourisp.co.ke/public_html
php artisan tinker
```

Inside the tinker shell:

```php
use App\Models\User;
use Illuminate\Support\Facades\Hash;

User::create([
    'name'     => 'Admin',
    'email'    => 'admin@yourisp.co.ke',
    'password' => Hash::make('your_secure_password'),
    'role'     => 'admin',   // adjust based on your User model's role field
]);
exit
```

### 9.3 Access the Admin Panel

Open your browser and navigate to:

```
https://billing.yourisp.co.ke/admin
```

Log in with the credentials you just created.

---

## 10. Testing — Hotspot Flow (End-to-End)

### Step 1 — Add a Router

1. In the admin panel, go to **Admin → ISP → Routers**.
2. Click **Add Router** and fill in:
   - **Name**: e.g., `Router-01`
   - **IP Address**: the MikroTik's LAN/WAN IP reachable from the server
   - **API Username / Password**: MikroTik API credentials
   - **RADIUS Secret**: same value as `RADIUS_SECRET` in `.env`
   - **Type**: `hotspot` (or `both` for Hotspot + PPPoE)
3. Click **Save**.

### Step 2 — Create ISP Packages

1. Go to **Admin → ISP → Packages → Add Package**.
2. Create Hotspot packages, for example:
   - **Name**: `1 Hour WiFi`, **Price**: KES 20, **Duration**: 1 hour, **Type**: Hotspot
   - **Name**: `Daily WiFi`, **Price**: KES 50, **Duration**: 24 hours, **Type**: Hotspot
3. Save each package.

### Step 3 — Generate MikroTik Script

1. Go to **Admin → ISP → Routers** and click **Generate Script** next to your router.
2. Review the generated `.rsc` script — it contains RADIUS client configuration, hotspot profile settings, and DNS/login page URLs.
3. Click **Download Script** to save the `.rsc` file.

### Step 4 — Download Hotspot Files

1. Click **Download Hotspot Files** next to the router.
2. Save the ZIP file — it contains:
   - `login.html` — the dark-themed login page shown to WiFi users
   - `alogin.html` — post-authentication redirect page
   - `status.html` — session status page

### Step 5 — Configure MikroTik

1. Open **WinBox** or the MikroTik web GUI.
2. Go to **New Terminal** and paste the contents of the downloaded `.rsc` script:

```
/import file=your-script.rsc
```

3. Upload the hotspot HTML files to the router's `/hotspot` directory via **Files → Upload** in WinBox.
4. Verify the hotspot RADIUS client points to your server's IP on port 1812/1813.

### Step 6 — Connect a Test Device

1. Connect a phone or laptop to the MikroTik hotspot WiFi.
2. Open a browser — you should be redirected to `https://billing.yourisp.co.ke/hotspot/login` (the dark-themed login page).

### Step 7 — Test M-Pesa STK Push

1. In your browser (on any device), visit `https://billing.yourisp.co.ke/buy`.
2. Select a Hotspot package (e.g., "1 Hour WiFi — KES 20").
3. Enter a Safaricom phone number.
4. Click **Pay**.
5. An M-Pesa STK Push prompt will appear on the phone — enter the M-Pesa PIN to confirm.

### Step 8 — Verify Payment Processing

1. After payment, check **Admin → ISP → Payments** — the payment should appear with status `completed`.
2. Check `storage/logs/laravel.log` for the callback log entry:

```bash
tail -100 /home/billing.yourisp.co.ke/public_html/storage/logs/laravel.log | grep -i mpesa
```

### Step 9 — Use the Receipt as a Voucher

1. Note the M-Pesa receipt number (e.g., `RGH4L9XYYY`).
2. On the hotspot login page, enter the receipt number in the **Voucher/Receipt** field.
3. Click **Login**.

### Step 10 — Verify RADIUS Authentication

Check that the voucher was provisioned in FreeRADIUS:

```bash
mysql -u billingY_inettotik -p billingY_inettotik -e "
  SELECT * FROM radcheck WHERE username = 'RGH4L9XYYY';
  SELECT * FROM radacct WHERE username = 'RGH4L9XYYY' ORDER BY acctstarttime DESC LIMIT 5;
"
```

You should see:
- A `radcheck` row with `Cleartext-Password := RGH4L9XYYY`
- A `radacct` row showing the active session after login

### Step 11 — Verify SMS Delivery

Check that an SMS was sent to the customer with the voucher code. Review SMS logs:

```bash
tail -50 /home/billing.yourisp.co.ke/public_html/storage/logs/laravel.log | grep -i sms
```

---

## 11. Testing — PPPoE Flow (End-to-End)

### Step 1 — Ensure Router is Configured

Verify the router is added in **Admin → ISP → Routers** and the generated script has been applied on the MikroTik (including the PPPoE server and RADIUS settings).

### Step 2 — Create a PPPoE Subscriber

1. Go to **Admin → ISP → Subscribers → Add Subscriber**.
2. Fill in:
   - **Username**: `testpppoe`
   - **Password**: `testpass123`
   - **Package**: select a PPPoE package
   - **Router**: select your router
3. Click **Save**.

### Step 3 — Verify RADIUS Provisioning

After saving, the system automatically provisions the subscriber in FreeRADIUS:

```bash
mysql -u billingY_inettotik -p billingY_inettotik -e "
  SELECT * FROM radcheck WHERE username = 'testpppoe';
  SELECT * FROM radreply WHERE username = 'testpppoe';
"
```

Expected rows in `radcheck`:
- `Cleartext-Password := testpass123`

Expected rows in `radreply`:
- `Framed-Pool` or IP assignment attributes
- `WISPr-Bandwidth-Max-Up` / `WISPr-Bandwidth-Max-Down` matching the package speed

### Step 4 — Attempt PPPoE Connection

On a MikroTik client or a Linux machine, configure a PPPoE client:

**MikroTik (via WinBox):**

1. Go to **PPP → Interface → Add → PPPoE Client**.
2. Set **Interface** to the WAN or test interface.
3. Set **Service Name** (if configured), **Username**: `testpppoe`, **Password**: `testpass123`.
4. Click **Apply** — the status should change to `connected`.

**Linux PPPoE client:**

```bash
apt install -y pppoeconf
pppoeconf
# Follow prompts — enter testpppoe / testpass123
```

### Step 5 — Verify the Session in Admin

Go to **Admin → ISP → Sessions** — the active PPPoE session for `testpppoe` should appear with:
- Start time
- IP address assigned
- Upload / download bytes

### Step 6 — Test Bandwidth Limits

Run a speed test from the PPPoE-connected device and verify the speed matches the package's `speed_upload` / `speed_download` values.

### Step 7 — Test Subscriber Expiry

1. In **Admin → ISP → Subscribers**, find `testpppoe` and set the expiry date to a past time.
2. Wait for the scheduler to run (or trigger manually: `php artisan schedule:run`).
3. Verify the PPPoE session is disconnected and the subscriber cannot reconnect.

### Step 8 — Test Renewal via M-Pesa

1. As the subscriber, visit `https://billing.yourisp.co.ke/customer/dashboard` and log in.
2. Click **Renew Package** and complete the M-Pesa STK Push payment.
3. Verify the expiry date is extended in **Admin → ISP → Subscribers**.
4. Verify the subscriber can reconnect via PPPoE.

---

## 12. Testing — POS / Reseller Flow

### Step 1 — Create a Reseller Account

1. Go to **Admin → ISP → Resellers → Add Reseller**.
2. Fill in the reseller's name, email, phone, and initial wallet balance.
3. Click **Save**.

### Step 2 — Reseller Login

1. Go to `https://billing.yourisp.co.ke/seller/login`.
2. Log in with the reseller's credentials.
3. Verify the reseller dashboard loads showing wallet balance and available packages.

### Step 3 — Generate and Sell Vouchers

1. From the reseller dashboard, navigate to **Payments → Pay Bill** or **Bulk Payment**.
2. Select a Hotspot package, enter a customer's phone number, and process the payment from the wallet.
3. Verify a voucher is generated and an SMS is sent to the customer.

### Step 4 — Verify Commission Tracking

1. Return to **Admin → ISP → Resellers** and check the reseller's transaction history.
2. Verify that wallet deductions and commissions are correctly calculated.

---

## 13. Troubleshooting

### 404 Errors on All Pages

- Verify `.htaccess` rewrite rules are enabled in the OLS vhost config (see [Part 5.2](#52-enable-htaccess-rewrite-in-cyberpanel-vhost)).
- Ensure the `AllowOverride All` equivalent is set for the document root.
- Restart OLS: `/usr/local/lsws/bin/lswsctrl restart`.

### 500 Internal Server Error

```bash
# Check Laravel logs
tail -100 /home/billing.yourisp.co.ke/public_html/storage/logs/laravel.log

# Check OLS error log
tail -100 /usr/local/lsws/logs/error.log

# Verify permissions
ls -la /home/billing.yourisp.co.ke/public_html/storage/
ls -la /home/billing.yourisp.co.ke/public_html/bootstrap/cache/
```

Common causes:
- **Storage not writable**: Run `chmod -R 775 storage/ bootstrap/cache/` and `chown -R nobody:nobody storage/ bootstrap/cache/`.
- **`.env` missing or `APP_KEY` not set**: Run `php artisan key:generate`.
- **Config cache stale**: Run `php artisan config:clear && php artisan config:cache`.

### M-Pesa Callback Not Received

```bash
# Tail logs and trigger a test payment
tail -f /home/billing.yourisp.co.ke/public_html/storage/logs/laravel.log
```

- Verify `APP_URL` in `.env` is the correct HTTPS URL.
- Verify the callback URL is publicly reachable: `curl -X POST https://billing.yourisp.co.ke/api/mpesa/stk-callback`.
- If `MPESA_VERIFY_IP=true`, Safaricom's IPs must not be blocked by your firewall.

### FreeRADIUS Not Authenticating

```bash
# Stop the service and run in debug mode
systemctl stop freeradius
freeradius -X
# Then test from another terminal:
radtest testuser testpass 127.0.0.1 0 your_radius_secret
```

Look for:
- `rlm_sql: Failed to connect to database` — check DB credentials in the SQL module config.
- `Access-Reject` with no SQL errors — the user does not exist in `radcheck`.

### Checking RADIUS Tables Directly

```bash
mysql -u billingY_inettotik -p billingY_inettotik -e "
  SELECT * FROM radcheck WHERE username = 'your_username';
  SELECT * FROM radreply WHERE username = 'your_username';
  SELECT * FROM radacct WHERE username = 'your_username' ORDER BY acctstarttime DESC LIMIT 5;
  SELECT * FROM nas;
"
```

### Restart Commands

```bash
# Restart OpenLiteSpeed
/usr/local/lsws/bin/lswsctrl restart

# Restart FreeRADIUS
systemctl restart freeradius

# Clear all Laravel caches
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear
```

---

## 14. Security Checklist

- [x] `APP_DEBUG=false` in production `.env`
- [x] `APP_ENV=production` in `.env`
- [x] `MPESA_VERIFY_IP=true` in `.env` — only allow Safaricom callback IPs
- [x] SSL certificate issued and auto-renewing via CyberPanel / Let's Encrypt
- [x] **UFW firewall rules**:

```bash
ufw default deny incoming
ufw default allow outgoing
ufw allow 22/tcp    comment "SSH"
ufw allow 80/tcp    comment "HTTP"
ufw allow 443/tcp   comment "HTTPS"
ufw allow 8090/tcp  comment "CyberPanel Dashboard"
ufw allow 7080/tcp  comment "OLS Web Admin (restrict to your IP in production)"
ufw allow 1812/udp  comment "FreeRADIUS Authentication"
ufw allow 1813/udp  comment "FreeRADIUS Accounting"
ufw enable
```

- [x] **Restrict CyberPanel and OLS admin ports** (8090, 7080) to your management IPs in production:

```bash
ufw delete allow 7080/tcp
ufw allow from YOUR_OFFICE_IP to any port 7080 proto tcp
ufw delete allow 8090/tcp
ufw allow from YOUR_OFFICE_IP to any port 8090 proto tcp
```

- [x] **MySQL bind address** — ensure MySQL only listens on `127.0.0.1`:

```bash
grep bind-address /etc/mysql/mysql.conf.d/mysqld.cnf
# Should show: bind-address = 127.0.0.1
```

- [x] **Storage directory** not web-accessible (the `public/` rewrite handles this, but verify no direct symlinks expose `storage/app/public` unexpectedly).
- [x] **Rotate `APP_KEY`** immediately if it is ever committed to version control or exposed.
- [x] **Keep dependencies updated**:

```bash
composer update --no-dev
npm audit fix
```

- [x] **SSL certificate auto-renewal** is managed by CyberPanel automatically. Verify with:

```bash
crontab -l -u root | grep certbot
# or
/usr/local/CyberCP/bin/python /usr/local/CyberCP/plogical/autoSSL.py
```
