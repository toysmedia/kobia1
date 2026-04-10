# OxNet — Multi-Tenant ISP Management SaaS

**OxNet** is a production-ready, multi-tenant ISP management SaaS platform for Kenyan WISPs built on Laravel 10. It provides a complete white-label solution: each ISP gets their own isolated subdomain, database, branding, and customer portal.

> Previously known as iNettotik. Now evolved into a full multi-tenant SaaS platform.

---

## 🏗️ Architecture

| Layer | Technology |
|-------|-----------|
| Backend | Laravel 10, PHP 8.1+ |
| Frontend | Bootstrap 5, Vite, Alpine.js |
| Database | MySQL 8 (system DB + per-tenant DB) |
| RADIUS | FreeRADIUS 3.x (shared tables) |
| Payments | M-Pesa Daraja (STK Push + C2B) |
| SMS | Africa's Talking |
| AI | OpenAI GPT-4o-mini (Knowledge Base + fallback) |
| Queue | Redis + Laravel Horizon |

---

## ✨ Feature Overview

### Super Admin (Platform Owner)
- Multi-tenant management: create, suspend, activate ISP tenants
- Subscription billing: per-tenant plans with M-Pesa payment
- Pricing plan management (Starter, Growth, Enterprise)
- CMS for public landing page content
- Global SMS & email gateway configuration
- Community portal moderation
- AI knowledge base training & analytics
- Tenant map (geo-located ISPs across Kenya)
- Audit logs

### Admin Tenant Portal (ISP Owner)
- Router management with one-click MikroTik script generation
- Subscriber (PPPoE/Hotspot) management — 10,000+ users
- FreeRADIUS integration (radcheck, radreply, radacct, nas)
- M-Pesa payment auto-provisioning
- Worker/Seller management with permission system
- Live session monitoring & admin disconnect
- Expense tracking & reporting
- Support ticket management
- Bulk SMS campaigns
- Customer portal toggle

### PPPoE Customer Portal (End Users)
- Self-service package view & renewal via M-Pesa
- Payment history with e-receipts
- Support ticket creation
- Profile management

### Community Portal (ISP Professionals)
- Forum with threaded replies, likes, follows
- Categories: General, Technical Help, MikroTik Tips, Billing, etc.
- Tags: pppoe, mikrotik, mpesa, fiber, etc.
- Reputation system, moderation, content reporting
- Full-text search

### Workers / Sellers
- Commission-based reseller management
- Customer creation & payment collection
- Package assignment

---

## 🚀 Quick Start

```bash
# Clone and install
git clone https://github.com/toysmedia/oxnett.git
cd oxnett

composer install
npm install && npm run build

cp .env.example .env
php artisan key:generate

# Create system DB, configure .env, then:
php artisan migrate
php artisan db:seed

# Seed test credentials (dev only)
php artisan db:seed --class=TestDataSeeder

php artisan serve
```

Visit `http://localhost:8000/super-admin/login` — use `superadmin@oxnet.co.ke` / `password`.

---

## 🔑 Key URLs

| Portal | URL | Notes |
|--------|-----|-------|
| **Super Admin** | `/super-admin/login` | Platform owner |
| **Admin Dashboard** | `/admin/isp/dashboard` | Tenant ISP admin |
| **Admin Login** | `/admin/login` | — |
| **Customer Portal** | `/customer/dashboard` | PPPoE self-service |
| **Community** | `/community` | Open forum |
| **Seller Portal** | `/seller/login` | Workers/Sellers |
| M-Pesa STK Callback | `/api/mpesa/stk-callback` | Safaricom webhook |
| M-Pesa C2B Confirm | `/api/mpesa/c2b-confirmation` | Safaricom webhook |

---

## 📖 Documentation

| Doc | Description |
|-----|-------------|
| [Installation Guide](docs/INSTALLATION.md) | Local dev + production server setup |
| [Testing Guide](docs/TESTING.md) | Automated tests + manual testing checklists |
| [Architecture Guide](docs/ARCHITECTURE.md) | System design, DB schema, auth matrix |
| [CyberPanel Deployment](docs/cyberpanel-deployment.md) | CyberPanel-specific setup |
| [FreeRADIUS Config](docs/freeradius-sql-config.md) | FreeRADIUS ↔ Laravel DB setup |

---

## 🧪 Running Tests

```bash
# Run the full test suite (uses SQLite in-memory — no MySQL required)
php artisan test

# Run only auth flow tests
php artisan test tests/Feature/Auth/

# Run only dashboard tests
php artisan test tests/Feature/Dashboard/
```

---

## 🗺️ M-Pesa Flow

1. Customer visits `/customer/payments/renew` → selects package → enters phone
2. STK Push sent → customer enters M-Pesa PIN on their phone
3. Safaricom calls `/api/mpesa/stk-callback` (IP-verified)
4. System auto-provisions FreeRADIUS + MikroTik PPPoE secret
5. SMS confirmation sent via Africa's Talking

---

## 📡 MikroTik Setup

1. Add router in **Admin → Routers**
2. Click **"Generate Script"** → download the `.rsc` file
3. Paste into MikroTik Terminal (Winbox or SSH)
4. For hotspot: click **"Download Hotspot Files"** → upload ZIP to `/hotspot` on MikroTik

---

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch: `git checkout -b feat/my-feature`
3. Write tests for your changes
4. Run `php artisan test` — all tests must pass
5. Submit a pull request

---

## 📄 License

Proprietary — © ToyMedia. All rights reserved.
