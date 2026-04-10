# OxNet ISP SaaS Platform — Architecture Guide

> Comprehensive technical reference for the OxNet multi-tenant ISP management platform.

---

## Table of Contents

1. [System Architecture Overview](#1-system-architecture-overview)
2. [Multi-Tenancy Model](#2-multi-tenancy-model)
3. [Database Schema Overview](#3-database-schema-overview)
4. [Authentication Guard Matrix](#4-authentication-guard-matrix)
5. [Middleware Stack by Route Group](#5-middleware-stack-by-route-group)
6. [Payment Flows](#6-payment-flows)
7. [Module Dependency Map](#7-module-dependency-map)
8. [Key Configuration Files](#8-key-configuration-files)

---

## 1. System Architecture Overview

```
┌─────────────────────────────────────────────────────────────────────────┐
│                          Internet (Public)                               │
└──────────────────────────────┬──────────────────────────────────────────┘
                               │  *.oxnet.co.ke (wildcard DNS)
                    ┌──────────▼────────────┐
                    │    Nginx / Apache      │
                    │  (Wildcard vhost)      │
                    └──────────┬────────────┘
                               │
              ┌────────────────▼────────────────────┐
              │          Laravel Application          │
              │                                       │
              │  ┌─────────────────────────────────┐ │
              │  │     RouteServiceProvider          │ │
              │  │  Loads route files by portal:     │ │
              │  │  • routes/web.php      (guest)    │ │
              │  │  • routes/super-admin.php         │ │
              │  │  • routes/admin.php               │ │
              │  │  • routes/seller.php              │ │
              │  │  • routes/customer.php            │ │
              │  │  • routes/community.php           │ │
              │  │  • routes/api.php                 │ │
              │  └─────────────────────────────────┘ │
              │                                       │
              │  ┌─────────────────────────────────┐ │
              │  │  ResolveTenant Middleware         │ │
              │  │  Reads subdomain → loads tenant   │ │
              │  │  DB credentials → switches        │ │
              │  │  'tenant' DB connection           │ │
              │  └─────────────────────────────────┘ │
              └────────────────┬────────────────────-┘
                               │
              ┌────────────────▼──────────────────────┐
              │            Database Layer               │
              │                                         │
              │  ┌──────────────────────────────────┐  │
              │  │  System DB (oxnet_system / MySQL) │  │
              │  │  • super_admin_users              │  │
              │  │  • tenants                        │  │
              │  │  • pricing_plans                  │  │
              │  │  • cms_content                    │  │
              │  │  • system_audit_logs              │  │
              │  │  • community_users                │  │
              │  │  • community_posts / replies      │  │
              │  │  • ai_knowledge_base              │  │
              │  │  • ai_conversations               │  │
              │  └──────────────────────────────────┘  │
              │                                         │
              │  ┌─────────────────────────────┐        │
              │  │  Tenant DB (oxnet_tenant_X)  │        │
              │  │  (one per tenant / ISP)       │        │
              │  │  • users (admins)             │        │
              │  │  • subscribers                │        │
              │  │  • sellers                    │        │
              │  │  • routers                    │        │
              │  │  • isp_packages               │        │
              │  │  • mpesa_payments             │        │
              │  │  • radcheck / radreply        │        │  (RADIUS tables)
              │  │  • tenant_settings            │        │
              │  │  • support_tickets            │        │
              │  └─────────────────────────────┘        │
              └───────────────────────────────────────-─┘
```

---

## 2. Multi-Tenancy Model

### Tenant Resolution Flow

```
1.  Incoming Request  →  nginx passes to Laravel
2.  Kernel runs global middleware (ForceHttps, SecurityHeaders)
3.  ResolveTenant middleware reads the subdomain from the Host header:
    - demo.oxnet.co.ke  → subdomain = "demo"
    - oxnet.co.ke       → subdomain = null  (system-level routes)
4.  ResolveTenant looks up the `tenants` table in the system DB:
    SELECT * FROM tenants WHERE subdomain = 'demo' LIMIT 1
5.  If found → decrypt credentials → configure 'tenant' DB connection
6.  Bind the Tenant model to the IoC container as `app('current_tenant')`
7.  EnsureSubscriptionActive middleware checks tenant.status:
    - expired / suspended → redirect to subscription payment page
8.  Request proceeds to the controller
```

### Tenant Isolation

| Layer | Isolation Method |
|-------|-----------------|
| Database | Separate MySQL database per tenant (`oxnet_tenant_{subdomain}`) |
| Auth | Separate `admin`, `seller`, `customer` guards — session-scoped |
| Files | `storage/tenants/{id}/` prefix for tenant uploads (future) |
| Cache | Cache keys prefixed with `tenant:{id}:` |

---

## 3. Database Schema Overview

### System Database (`oxnet_system`)

```
super_admin_users
  id, name, email, password, phone, avatar, is_active,
  email_verified_at, remember_token, timestamps, deleted_at

tenants
  id, name, email, phone, subdomain (unique), domain,
  database_name, database_host, database_port,
  database_username, database_password (encrypted),
  plan_id (FK→pricing_plans), status, trial_ends_at,
  subscription_expires_at, lat, lng, is_maintenance_mode,
  timestamps, deleted_at

pricing_plans
  id, name, slug (unique), description, price, billing_cycle,
  trial_days, max_customers, max_routers, feature_flags (json),
  is_active, sort_order, timestamps, deleted_at

subscription_payments
  id, tenant_id, plan_id, amount, currency, mpesa_code,
  mpesa_phone, status, extends_days, paid_at, timestamps

cms_content
  id, section, key, value (longtext), type, sort_order,
  is_active, timestamps

system_audit_logs
  id, action, user_type, user_id, old_values, new_values,
  ip_address, user_agent, timestamps

community_users
  id, name, email, password, avatar, bio, location, website,
  is_verified, is_banned, ban_reason, email_verified_at,
  last_login_at, reputation, remember_token, timestamps, deleted_at

community_posts
  id, community_user_id, community_category_id, title, slug,
  body, status (draft/published/pending/rejected),
  is_pinned, is_featured, is_locked, views, timestamps, deleted_at

community_replies
  id, community_post_id, community_user_id, parent_id (nullable),
  body, is_solution, timestamps, deleted_at

community_categories
  id, name, slug, description, icon, color, sort_order,
  is_active, timestamps

community_tags
  id, name, slug, timestamps

community_post_tag  (pivot)
  community_post_id, community_tag_id

community_likes  (polymorphic)
  id, community_user_id, likeable_id, likeable_type, timestamps

community_follows  (polymorphic)
  id, community_user_id, followable_id, followable_type, timestamps

community_reports
  id, community_user_id, reportable_id, reportable_type,
  reason, details, status, resolved_at, timestamps

ai_knowledge_base
  id, question, answer, portal_context, category, tags (json),
  is_active, hit_count, timestamps, deleted_at

ai_conversations
  id, session_id, portal_context, messages (json), timestamps

ai_unanswered_questions
  id, question, portal_context, session_id, status,
  resolved_at, timestamps
```

### Tenant Database (`oxnet_tenant_{subdomain}`)

```
users  (admin accounts)
  id, name, email, mobile, password, email_verified_at,
  tour_completed, remember_token, timestamps, deleted_at

subscribers
  id, name, email, phone, username (unique), password_hash,
  radius_password, isp_package_id (FK), router_id (FK),
  connection_type, status, expires_at, created_by,
  lat, lng, timestamps, deleted_at

sellers / workers
  id, name, email, mobile, password, tariff_id,
  balance, commission, is_active, remember_token,
  email_verified_at, timestamps

routers
  id, name, host, username, password, api_port, location,
  provision_phase, lat, lng, timestamps

isp_packages
  id, name, price, speed_download, speed_upload, validity_days,
  connection_type, radius_pool, is_active, timestamps

mpesa_payments
  id, subscriber_id, amount, phone, mpesa_code (unique),
  status, type (stk|c2b), used_at, timestamps

radcheck, radreply, radusergroup, radgroupcheck,
radgroupreply, radacct, radpostauth, nas   (FreeRADIUS tables)

expenses, expense_categories
roles, permissions  (role_user pivot with user_type)
message_logs, sms
tenant_settings
support_tickets, support_messages, support_ticket_replies
notifications, tenant_notifications
ai_tenant_settings
```

---

## 4. Authentication Guard Matrix

| Guard | Provider | Model | Table | Login URL | Dashboard URL |
|-------|----------|-------|-------|-----------|---------------|
| `super_admin` | `super_admins` | `System\SuperAdmin` | `super_admin_users` (system DB) | `/super-admin/login` | `/super-admin/dashboard` |
| `admin` | `admins` | `Admin` | `users` (tenant DB) | `/admin/login` | `/admin/isp/dashboard` |
| `customer` | `customers` | `Subscriber` | `subscribers` (tenant DB) | `/customer/login` | `/customer/dashboard` |
| `seller` | `sellers` | `Seller` | `sellers` (tenant DB) | `/seller/login` | `/seller/dashboard` |
| `community` | `community_users` | `Community\CommunityUser` | `community_users` (system DB) | `/community/login` | `/community` |
| `web` | `users` | `User` | `users` | (internal) | — |

> **Config file:** `config/auth.php`

---

## 5. Middleware Stack by Route Group

### Super Admin Routes (`/super-admin/*`)

```
web group:
  EncryptCookies
  AddQueuedCookiesToResponse
  StartSession
  ShareErrorsFromSession
  VerifyCsrfToken
  SubstituteBindings

Route middleware:
  auth:super_admin    →  checks super_admin guard
```

### Admin (Tenant) Routes (`/admin/*`)

```
web group (same as above)

Route middleware:
  is_installed        →  IsInstalled middleware
  auth:admin          →  checks admin guard
  subscription        →  EnsureSubscriptionActive (checks tenant.status)
  audit.action        →  AuditAction (logs sensitive actions)
```

### Seller / Worker Routes (`/seller/*`)

```
web group

Route middleware:
  is_installed
  auth:seller
  permission          →  CheckPermission (per-route permission check)
```

### Customer Portal Routes (`/customer/*`)

```
web group

Route middleware:
  customer.portal.enabled  →  CustomerPortalEnabled (checks tenant setting)
  guest:customer           →  for login/register pages
  auth:customer            →  for authenticated pages
  customer.active          →  CustomerSubscriptionActive (for most pages)
                              (renewal page bypasses this check)
```

### Community Portal Routes (`/community/*`)

```
web group

Public routes (no auth):
  /community, /community/posts, /community/categories, etc.

Authenticated routes:
  community.auth      →  CommunityAuth (checks community guard)
  community.not-banned →  CommunityNotBanned
  community.verified  →  CommunityVerified (for post creation, etc.)
```

### API Routes (`/api/*`)

```
api group:
  ThrottleRequests:api
  SubstituteBindings

Webhook routes (no CSRF):
  verify_mpesa_ip     →  VerifyMpesaIp
  verify_router_secret →  VerifyRouterSecret
```

---

## 6. Payment Flows

### 6.1 Tenant Subscription Payment (Super Admin / Tenant Admin)

```
Tenant Admin visits /subscription/payment
    │
    ▼
Selects pricing plan → enters M-Pesa phone number
    │
    ▼
POST /api/mpesa/stk-push
    │   (SuperAdminSubscriptionController@stkPush)
    ▼
Safaricom Daraja STK Push API
    │
    ▼ (Customer's phone shows M-Pesa PIN prompt)
    │
    ▼ Safaricom calls back:
POST /api/mpesa/stk-callback
    │   (MpesaCallbackController@stkCallback)
    ▼
SubscriptionPayment record created
Tenant.subscription_expires_at extended
EnsureSubscriptionActive now allows access
```

### 6.2 PPPoE Customer Package Renewal

```
Customer visits /customer/payments/renew
    │
    ▼
Selects package → enters M-Pesa phone number
    │
    ▼
POST /customer/payments/renew  or STK push
    │
    ▼
Safaricom Daraja → STK Push to customer phone
    │
    ▼ Safaricom calls back:
POST /api/mpesa/stk-callback
    │   (IspPaymentController@mpesaCallback)
    ▼
MpesaPayment record created (idempotent, mpesa_code unique)
Subscriber.expires_at extended
FreeRADIUS radcheck entry updated (password re-provisioned)
MikroTik PPPoE secret updated via API
SMS confirmation sent to customer (Africa's Talking)
```

---

## 7. Module Dependency Map

```
┌────────────────────────────────────────────────────────────────┐
│                    OxNet Module Map                             │
│                                                                  │
│  ┌─────────────────┐    ┌─────────────────┐                    │
│  │  Super Admin     │    │  Community       │                    │
│  │  Module          │    │  Portal          │                    │
│  │  ─────────────   │    │  ─────────────   │                    │
│  │  Tenants CRUD    │    │  Posts / Replies │                    │
│  │  Subscriptions   │    │  Categories/Tags │                    │
│  │  Pricing Plans   │    │  Likes / Follows │                    │
│  │  CMS             │    │  Moderation      │                    │
│  │  SMS/Email GW    │    │  Search          │                    │
│  │  Audit Logs      │    │  Profiles        │                    │
│  │  Community Mod   │    │                  │                    │
│  │  AI Training     │    └─────────────────┘                    │
│  └────────┬─────────┘                                           │
│           │ creates / manages                                    │
│           ▼                                                      │
│  ┌─────────────────┐    ┌─────────────────┐                    │
│  │  Admin Tenant    │    │  AI Assistant   │                    │
│  │  Portal          │    │  Module          │                    │
│  │  ─────────────   │    │  ─────────────   │                    │
│  │  Routers         │    │  Knowledge Base  │                    │
│  │  Subscribers     │    │  OpenAI Fallback │                    │
│  │  ISP Packages    │    │  Chat Widget     │                    │
│  │  RADIUS Mgmt     │    │  Per-Portal Ctx  │                    │
│  │  MikroTik API    │    │  Unanswered Q's  │                    │
│  │  M-Pesa Billing  │    │  Analytics       │                    │
│  │  Workers/Sellers │    │                  │                    │
│  │  Reports         │    └─────────────────┘                    │
│  │  Map View        │                                            │
│  │  Support Tickets │                                            │
│  └────────┬─────────┘                                           │
│           │ serves                                               │
│           ▼                                                      │
│  ┌─────────────────┐    ┌─────────────────┐                    │
│  │  Customer Portal │    │  Seller Portal  │                    │
│  │  (PPPoE Self-Svc)│    │  (Workers)      │                    │
│  │  ─────────────   │    │  ─────────────   │                    │
│  │  Dashboard       │    │  Customer Mgmt  │                    │
│  │  Package Info    │    │  Payments       │                    │
│  │  M-Pesa Renewal  │    │  Packages       │                    │
│  │  Support Tickets │    │  Commissions    │                    │
│  │  Profile         │    │                  │                    │
│  └─────────────────┘    └─────────────────┘                    │
└────────────────────────────────────────────────────────────────┘
```

### Key Service Classes

| Service | Location | Responsibility |
|---------|----------|---------------|
| `MikrotikProvisioningService` | `app/Services/` | 3-phase PPPoE provisioning, VLAN isolation |
| `MpesaService` | `app/Services/` | STK Push, C2B, idempotency, IP verification |
| `AiAssistantService` | `app/Services/` | KB-first lookup, OpenAI fallback, rate limiting |
| `TenantService` | `app/Services/` | Tenant creation, DB provisioning, credential encryption |
| `SmsService` | `app/Services/` | Africa's Talking SMS + fallback |

---

## 8. Key Configuration Files

| File | Purpose |
|------|---------|
| `config/auth.php` | Guard definitions and provider mappings |
| `config/database.php` | System DB + tenant DB + SQLite (for tests) connections |
| `config/ai.php` | OpenAI model, rate limits, portal context settings |
| `config/community.php` | Community portal feature flags |
| `config/mpesa.php` | M-Pesa Daraja credentials and webhook IPs |
| `config/sms.php` | Africa's Talking credentials |
| `app/Http/Kernel.php` | Global middleware + route middleware aliases |
| `app/Providers/RouteServiceProvider.php` | Route file loading with prefix/middleware |
| `app/Providers/TenantServiceProvider.php` | Tenant context binding and DB switching |
