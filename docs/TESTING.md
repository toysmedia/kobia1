# OxNet ISP SaaS Platform — Testing Guide

> Covers automated PHPUnit tests and manual testing checklists for all user flows.

---

## Table of Contents

1. [Automated Test Suite](#1-automated-test-suite)
2. [Test Data Setup](#2-test-data-setup)
3. [Manual Testing Checklists](#3-manual-testing-checklists)
4. [Expected URLs & Test Credentials](#4-expected-urls--test-credentials)
5. [Common Issues & Debug Tips](#5-common-issues--debug-tips)

---

## 1. Automated Test Suite

### Running All Tests

```bash
# Fastest: uses SQLite in-memory (no MySQL required)
php artisan test

# Or directly with PHPUnit
./vendor/bin/phpunit

# Run only a specific test file
php artisan test tests/Feature/Auth/SuperAdminAuthTest.php

# Run a specific test method
php artisan test --filter test_super_admin_can_login_with_valid_credentials

# Run with verbose output
php artisan test --verbose
```

### Test Structure

```
tests/
├── Feature/
│   ├── Auth/
│   │   ├── SuperAdminAuthTest.php   — Super admin login, logout, guard
│   │   ├── AdminAuthTest.php        — Tenant admin login, logout
│   │   ├── CustomerAuthTest.php     — PPPoE customer login, logout, portal check
│   │   ├── CommunityAuthTest.php    — Community login, registration, ban check
│   │   └── SellerAuthTest.php       — Seller/worker login, logout
│   ├── Dashboard/
│   │   ├── SuperAdminDashboardTest.php — Super admin pages, sub-sections
│   │   ├── AdminDashboardTest.php       — ISP dashboard, routers, subscribers
│   │   └── CustomerDashboardTest.php    — Customer pages, renewal access
│   └── ExampleTest.php
└── Unit/
    └── ExampleTest.php
```

### Database Setup for Tests

Tests use an **SQLite in-memory database** (configured in `phpunit.xml`). The `TestCase` base class automatically redirects the named `mysql` connection to the same SQLite database so that models with `$connection = 'mysql'` work correctly.

No MySQL server is required to run the test suite.

```xml
<!-- phpunit.xml -->
<env name="DB_CONNECTION" value="sqlite"/>
<env name="DB_DATABASE" value=":memory:"/>
```

### Model Factories

The test suite ships with factories for all user types:

| Factory | Model | Location |
|---------|-------|----------|
| `SuperAdminFactory` | `System\SuperAdmin` | `database/factories/System/` |
| `AdminFactory` | `Admin` | `database/factories/` |
| `SubscriberFactory` | `Subscriber` | `database/factories/` |
| `SellerFactory` | `Seller` | `database/factories/` |
| `CommunityUserFactory` | `Community\CommunityUser` | `database/factories/Community/` |

Usage in tests:

```php
use App\Models\System\SuperAdmin;
use Illuminate\Foundation\Testing\RefreshDatabase;

class MyTest extends TestCase
{
    use RefreshDatabase;

    public function test_something(): void
    {
        $admin = SuperAdmin::factory()->create(['is_active' => true]);
        $this->actingAs($admin, 'super_admin')->get('/super-admin/dashboard')->assertStatus(200);
    }
}
```

---

## 2. Test Data Setup

### Quick Setup (All Portals)

```bash
# Run migrations
php artisan migrate

# Seed default data + test accounts
php artisan db:seed
php artisan db:seed --class=TestDataSeeder
```

### What TestDataSeeder Creates

| Item | Details |
|------|---------|
| Super Admin | `superadmin@oxnet.co.ke` / `password` |
| Pricing Plan | Starter — KES 2,500/month |
| Demo Tenant | subdomain: `demo`, DB: `oxnet_tenant_demo` |
| Community User | `member@community.test` / `password` |
| CMS Defaults | Site name, tagline, contact info |

> The seeder will not overwrite existing records (`firstOrCreate`), so it is safe to run multiple times.

---

## 3. Manual Testing Checklists

Use these checklists when performing manual QA. Tick each item after verification.

### 3.1 Super Admin Auth Flow

- [ ] Visit `/super-admin/login` — login form renders
- [ ] Submit empty form — validation errors shown
- [ ] Submit wrong credentials — "Invalid credentials" error shown
- [ ] Login with `superadmin@oxnet.co.ke` / `password` — redirected to `/super-admin/dashboard`
- [ ] Dashboard loads with tenant stats cards
- [ ] Navigate to **Tenants** → table lists all tenants
- [ ] Navigate to **Subscriptions** → subscription list loads
- [ ] Navigate to **Pricing Plans** → plans list with create button
- [ ] Navigate to **CMS** → CMS editor loads
- [ ] Navigate to **Audit Logs** → log table loads
- [ ] Navigate to **Community Moderation** → moderation dashboard
- [ ] Navigate to **AI Training** → knowledge base list
- [ ] Click **Logout** — redirected to `/super-admin/login`
- [ ] Attempt to visit `/super-admin/dashboard` after logout — redirected to login

### 3.2 Admin (Tenant) Auth Flow

- [ ] Visit `{subdomain}/admin/login` — login form renders
- [ ] Submit wrong credentials — validation error shown
- [ ] Login with admin credentials — redirected to `/admin/isp/dashboard`
- [ ] Dashboard loads with subscriber count, router count, revenue cards
- [ ] Navigate to **Routers** — router list or empty state
- [ ] Navigate to **Subscribers** — subscriber list or empty state
- [ ] Navigate to **Packages** — ISP packages list
- [ ] Navigate to **Sessions** — RADIUS sessions table
- [ ] Navigate to **Payments** — M-Pesa payment history
- [ ] Navigate to **Workers** — worker/seller management
- [ ] Navigate to **Profile** — profile edit form
- [ ] Subscription countdown banner visible if expiry < 7 days
- [ ] Click **Logout** — redirected to `/admin/login`

### 3.3 PPPoE Customer Auth Flow

- [ ] Visit `{subdomain}/customer/login` — login form renders  
  (If "Customer portal is not available" — check `customer_portal_enabled` tenant setting)
- [ ] Submit wrong credentials — validation error shown
- [ ] Login as customer — redirected to `/customer/dashboard`
- [ ] Dashboard shows current package, expiry date, usage stats
- [ ] Navigate to **Package** — current package details + change request option
- [ ] Navigate to **Payments** — payment history
- [ ] Navigate to **Support** — support ticket form
- [ ] Navigate to **Profile** — profile edit form
- [ ] For an **expired** subscriber — should be redirected to `/customer/payments/renew`
- [ ] Renewal page shows STK push form
- [ ] Click **Logout** — redirected to `/customer/login`

### 3.4 Community Portal Auth Flow

- [ ] Visit `/community` — public feed renders without login
- [ ] Visit `/community/posts` — post list renders
- [ ] Visit `/community/categories` — category grid renders
- [ ] Visit `/community/login` — login form renders
- [ ] Visit `/community/register` — registration form renders
- [ ] Submit registration with valid data — account created, login redirect
- [ ] Login with valid credentials — redirected to `/community`
- [ ] Navigate to **New Post** — post creation form
- [ ] Submit a post — post appears in feed
- [ ] Like a post — like count updates
- [ ] Reply to a post — reply appears in thread
- [ ] Follow a user — follow relationship created
- [ ] Visit own **Profile** — shows posts, reputation, follow stats
- [ ] Click **Logout** — redirected to `/community`
- [ ] Banned user: `actingAs(banned_user)` → accessing create-post → redirected with ban message

### 3.5 Seller / Worker Auth Flow

- [ ] Visit `/seller/login` — login form renders
- [ ] Submit wrong credentials — validation error shown
- [ ] Login with valid seller credentials — redirected to `/seller/dashboard`
- [ ] Dashboard shows assigned customers and packages
- [ ] Navigate to **Users** — customer list
- [ ] Navigate to **Packages** — package list
- [ ] Navigate to **Payments** — payment history
- [ ] Click **Logout** — redirected to `/seller/login`
- [ ] Permission-restricted route blocked if seller lacks permission

---

## 4. Expected URLs & Test Credentials

### Local Development (localhost:8000 or .test domain)

| Portal | Login URL | Credential |
|--------|-----------|------------|
| Super Admin | `http://localhost:8000/super-admin/login` | `superadmin@oxnet.co.ke` / `password` |
| Admin | `http://demo.oxnett.test/admin/login` | (create via `TestDataSeeder` or register) |
| Customer | `http://demo.oxnett.test/customer/login` | `testcustomer` / `password` |
| Community | `http://localhost:8000/community/login` | `member@community.test` / `password` |
| Seller | `http://localhost:8000/seller/login` | (create via admin Workers management) |

### Production (oxnet.co.ke)

| Portal | Login URL |
|--------|-----------|
| Super Admin | `https://oxnet.co.ke/super-admin/login` |
| Admin | `https://{tenant}.oxnet.co.ke/admin/login` |
| Customer | `https://{tenant}.oxnet.co.ke/customer/login` |
| Community | `https://oxnet.co.ke/community/login` |
| Seller | `https://{tenant}.oxnet.co.ke/seller/login` |

---

## 5. Common Issues & Debug Tips

### "Route [super-admin.login] not defined"

**Cause:** Route cache is stale or there is a route name conflict.  
**Fix:**
```bash
php artisan route:clear
php artisan route:cache
php artisan route:list | grep super-admin
```

### Customer Portal Shows "403 Not Available"

**Cause:** `CustomerPortalEnabled` middleware — the `customer_portal_enabled` setting is not `1`.  
**Fix:** In Admin Portal → Settings → Customer Portal → toggle ON.

### Login Redirects Back to Login (Redirect Loop)

**Cause:** Session driver misconfigured, or `APP_KEY` not set.  
**Fix:**
```bash
php artisan key:generate
php artisan cache:clear
php artisan config:clear
```

### "SQLSTATE[HY000]: General error: 1 no such table"

**Cause:** Migrations have not been run.  
**Fix:**
```bash
php artisan migrate
```

### Subscription Check Fails / Tenant Not Resolved

**Cause:** The `ResolveTenant` middleware is not resolving the subdomain correctly.  
**Fix:** Verify `APP_URL` is set to your wildcard domain and that DNS / Valet is configured for subdomains.

### Tests Fail with "Connection refused" (mysql)

**Cause:** The test runner is trying to use MySQL instead of SQLite.  
**Fix:** Ensure `phpunit.xml` has the SQLite lines **uncommented**:
```xml
<env name="DB_CONNECTION" value="sqlite"/>
<env name="DB_DATABASE" value=":memory:"/>
```
