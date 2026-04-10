# MikroTik OpenVPN Server Setup

A production-ready OpenVPN server configuration for MikroTik RouterOS, featuring certificate-based mutual authentication combined with username/password login. Adapted from [ageapps/MikroTik_OpenVPN_Server_Setup](https://github.com/ageapps/MikroTik_OpenVPN_Server_Setup) with security improvements and additional tooling.

---

## Integration with the Laravel Billing App

This `mikrotik-openvpn/` directory contains **server-side setup scripts** that are run once when provisioning the OpenVPN server on the billing server's MikroTik router.

The **Laravel application** (`app/Services/MikrotikScriptService.php`) handles a separate but complementary concern: it dynamically generates **per-router client provisioning scripts** that configure each managed MikroTik router to connect back to the OpenVPN server as a client.

### How the two parts work together

| Component | Purpose | When to use |
|---|---|---|
| `ovpn_full_setup.rsc` (this folder) | **All-in-one** — sets up CA, server certs, IP pool, OpenVPN server, firewall, and all client users on the **billing server** | Once, for the fastest automated setup |
| `ovpn_server.rsc` (this folder) | Sets up the CA, server certificates, IP pool, and OpenVPN server daemon on the **billing server** | Once, if you prefer the two-step approach |
| `ovpn_clients.rsc` (this folder) | Creates PPP user accounts and exports client certificates on the **billing server** | Once per batch of routers (two-step approach) |
| Laravel **Router panel → Generate Script** | Generates a MikroTik provisioning script for each **managed router** to connect back as an OpenVPN client | Each time a new router is added |

### Required `.env` variables

The following values must be set in the Laravel app's `.env` file on the billing server. They must be consistent with the `VPNPOOL` and `OVPNPORT` values in `ovpn_server.rsc`:

```dotenv
# Billing server public address — routers connect to this
BILLING_SERVER_PUBLIC_IP=89.117.52.63

# OpenVPN server VPN IP — always the .1 address in the VPN pool
# Must match VPNPOOL.1 in ovpn_server.rsc
BILLING_SERVER_VPN_IP=10.8.0.1

# VPN subnet in CIDR notation — must match VPNPOOL in ovpn_server.rsc
BILLING_SERVER_SUBNET=10.8.0.0/24

# OpenVPN port — must match OVPNPORT in ovpn_server.rsc
# Use 1194 (standard) or 443 to bypass ISP blocks
OPENVPN_PORT=1194

# Application URL and domain
APP_URL=https://oxdes.com
APP_DOMAIN=oxdes.com
```

### Production values for oxdes.com

| Variable | Value |
|---|---|
| `BILLING_SERVER_PUBLIC_IP` | `89.117.52.63` |
| `BILLING_SERVER_VPN_IP` | `10.8.0.1` |
| `BILLING_SERVER_SUBNET` | `10.8.0.0/24` |
| `OPENVPN_PORT` | `1194` |
| `APP_URL` | `https://oxdes.com` |
| `APP_DOMAIN` | `oxdes.com` |

---

## Quick Start — One-Paste Automated Setup

For the fastest setup, use the combined script that does everything in one go:

1. Upload `ovpn_full_setup.rsc` to the router (WinBox **Files → Upload**)
2. Open a terminal and run:
   ```routeros
   /import file=ovpn_full_setup.rsc
   ```
3. Wait for the script to complete (~2–3 minutes per certificate with 4096-bit keys; ~15–20 minutes total for CA + server + 5 client certs on typical MikroTik hardware)
4. Download the exported certificates from **Files**

This script automatically:
- Creates the CA and server certificates
- Waits for each certificate to be fully signed (smart polling, no fixed delays)
- Creates the IP pool, VPN profile, and enables the OpenVPN server
- Adds firewall and NAT rules
- Creates all client users with certificates
- Exports everything ready for download

> **Prefer manual control?** Use the two-step approach with `ovpn_server.rsc` and `ovpn_clients.rsc` instead.

---

## Overview

This setup configures a full OpenVPN server on a MikroTik router:

- **Certificate Authority (CA)** generated on the router itself
- **Server certificate** signed by the CA
- **Per-user client certificates** signed by the CA
- **PPP user accounts** with username/password authentication
- **IP address pool** for connected clients (`10.8.0.2–10.8.0.254` by default)
- **Firewall rules** to allow VPN traffic and NAT masquerade for internet access through the tunnel

Shell scripts on your local machine handle stripping key passphrases and assembling ready-to-use `.ovpn` client configuration packages.

---

## Prerequisites

| Requirement | Details |
|---|---|
| MikroTik RouterOS | 6.x or newer (tested on 6.49+) |
| WinBox or SSH | Access to the router terminal |
| OpenSSL | Installed on your local machine (`openssl` in PATH) |
| OpenVPN client | For end-users connecting to the VPN |

---

## File Overview

| File | Purpose | Where it runs |
|---|---|---|
| `ovpn_full_setup.rsc` | **All-in-one:** creates certs, pool, profile, server, firewall, and all client users in one run | MikroTik router |
| `ovpn_server.rsc` | Creates CA, server cert, IP pool, PPP profile, OpenVPN server, and firewall rules | MikroTik router |
| `ovpn_clients.rsc` | Creates user accounts and exports client certificates | MikroTik router |
| `sign_certs.sh` | Strips passphrases from exported private keys | Local machine |
| `create_config.sh` | Assembles per-user `.ovpn` config packages | Local machine |
| `template.ovpn` | OpenVPN client config template used by `create_config.sh` | Local machine |

---

## Server Setup

### Step 1 — Customize `ovpn_server.rsc`

Open `ovpn_server.rsc` in a text editor and adjust the parameters at the top of the file:

```routeros
:global ORG "OxNett"          # Your organization name
:global COUNTRY "US"           # Two-letter country code
:global STATE "US"             # State or province
:global LOCALITY "United States"
:global KEYSIZE 4096           # 2048 or 4096 (4096 recommended)
:global VPNPOOL "10.8.0"       # VPN subnet prefix — must match BILLING_SERVER_SUBNET in .env
:global OVPNPORT "1194"        # OpenVPN TCP port — must match OPENVPN_PORT in .env
```

> **Subnet consistency:** `VPNPOOL` here must match `BILLING_SERVER_SUBNET` and `BILLING_SERVER_VPN_IP` in the Laravel `.env`. The server will always use `.1` as its own VPN IP (e.g. `10.8.0.1` for pool `10.8.0`).

### Step 2 — Upload scripts to the router

Using WinBox: **Files → Upload** — upload both `ovpn_server.rsc` and `ovpn_clients.rsc`.

Using SSH/SCP:
```bash
scp ovpn_server.rsc ovpn_clients.rsc admin@<router-ip>:/
```

### Step 3 — Run the server setup script

Connect to the router via WinBox Terminal or SSH:

```routeros
/import file=ovpn_server.rsc
```

This script will:
1. Create CA, SERVER, and CLIENT certificate templates
2. Sign the CA (self-signed) and the SERVER certificate
3. Create an IP address pool for VPN clients
4. Create a PPP profile for the VPN
5. Enable the OpenVPN server on the configured port
6. Add firewall filter and NAT rules

> **Note:** Certificate signing is asynchronous. The script includes `:delay` pauses. On slow hardware with 4096-bit keys, signing may take a minute or two. You can monitor progress in **System → Certificates**.

### Step 4 — Run the client certificate script

```routeros
/import file=ovpn_clients.rsc
```

This creates 5 user accounts (`user1`–`user5`) and exports their certificates to the router's file system. To create more or fewer users, edit `NUMCLIENTS` in `ovpn_clients.rsc` before uploading.

### Step 5 — Download the exported certificates

In WinBox: **Files** — download all files matching `cert_export_*.crt` and `cert_export_*.key` to a `certificates/` folder on your local machine.

You should have:
- `cert_export_CA.crt`
- `cert_export_user1@CA.crt`, `cert_export_user1@CA.key`
- `cert_export_user2@CA.crt`, `cert_export_user2@CA.key`
- … and so on for each user

---

## Client Setup

### Step 1 — Remove key passphrases

MikroTik exports private keys with a passphrase. Run `sign_certs.sh` on your local machine to strip the passphrases (OpenVPN clients need unencrypted keys):

```bash
# Default: process 5 users
sh sign_certs.sh

# Or specify a different number:
sh sign_certs.sh 10
```

When prompted by OpenSSL, enter the passphrase shown in the hint (e.g., `user0001` for user 1).

### Step 2 — Edit `create_config.sh`

Open `create_config.sh` and set your router's public address:

```bash
PUBLIC_ADDRESS="your.domain.com"   # or your public IP address
PUBLIC_PORT="1194"                  # must match OVPNPORT in ovpn_server.rsc
PROTOCOL="tcp-client"               # tcp-client (recommended) or udp
NUM_USERS=5                         # must match NUMCLIENTS in ovpn_clients.rsc
```

### Step 3 — Generate client config packages

```bash
sh create_config.sh
```

This creates a `configs/` directory with one subfolder per user, each containing:

```
configs/
  user1/
    user1.ovpn          ← Import this into the OpenVPN client
    cert_export_CA.crt
    cert_export_user1@CA.crt
    cert_export_user1@CA.key
    user.auth           ← Username and password
  user2/
    ...
```

### Step 4 — Distribute to end users

Give each user their folder. They import the `.ovpn` file into their OpenVPN client along with all files in the same directory.

---

## Connecting

### Windows / macOS
1. Install [OpenVPN Connect](https://openvpn.net/client/) or the OpenVPN GUI
2. Import the `.ovpn` file — the client will pick up the certificates and key automatically
3. Connect and enter credentials if prompted (credentials are in `user.auth`)

### Android / iOS
1. Install **OpenVPN Connect** from the App Store / Play Store
2. Transfer the entire user folder to the device
3. Open the `.ovpn` file with OpenVPN Connect

---

## Troubleshooting

### Connection times out
- Verify the OpenVPN port is open: `telnet <router-ip> 1194`
- Check that the firewall rule was added: `/ip firewall filter print` — look for the `Allow OpenVPN` entry
- Make sure port forwarding is configured if the router is behind another NAT device

### Certificate errors (`TLS handshake failed`)
- Ensure all three files (CA cert, client cert, client key) are in the same directory as the `.ovpn` file
- Verify the client certificate was signed by the same CA as the server certificate
- Check that the server certificate has the `tls-server` key usage flag: `/certificate print detail where name=SERVER`

### Authentication failure
- Confirm PPP secrets were created: `/ppp secret print`
- Verify the username and password in `user.auth` match the PPP secret

### VPN connects but no internet access
- Check that the NAT masquerade rule is present: `/ip firewall nat print` — look for `NAT for VPN clients`
- Make sure IP forwarding is enabled: `/ip settings print` — `ip-forward` should be `yes`
- Confirm the forward filter rule exists: `/ip firewall filter print` — look for `Allow VPN client forwarding`

### Certificates not appearing after signing
- Certificate signing with large key sizes (4096-bit) can take 1–3 minutes on RouterOS hardware
- Check status in WinBox: **System → Certificates** — wait until the `K` (key) and `T` (trusted) flags appear

---

## Security Notes

- **Change default passwords:** ⚠️ **CRITICAL — do this before deploying to production.** The default passwords (`user0001`, `user0002`, etc.) are weak sequential placeholders and must be replaced with strong, unique passwords for every user. Change them in `ovpn_clients.rsc` before running the script, or update them afterwards in `/ppp secret` via WinBox or `/ppp secret set [find name=user1] password=<new-password>`.
- **Limit the IP pool:** If you only have a small number of users, reduce the pool size in `ovpn_server.rsc` to limit potential exposure.
- **Restrict firewall rules:** The firewall rules use broad subnet matches. Consider tightening them once your topology is finalized.
- **Keep RouterOS updated:** Apply MikroTik security patches promptly. Subscribe to the [MikroTik security announcements](https://forum.mikrotik.com/viewforum.php?f=21).
- **Protect certificate files:** The `certificates/` and `configs/` directories are excluded by `.gitignore`. Never commit private keys to version control.
- **Consider a dedicated VPN port:** Using a non-standard port (e.g., 443) can reduce noise from automated scanners, but obscurity alone is not a security measure.

---

## Credits

Based on [ageapps/MikroTik_OpenVPN_Server_Setup](https://github.com/ageapps/MikroTik_OpenVPN_Server_Setup), with the following improvements:

- Upgraded key size from 2048 to 4096 bits
- Added `days-valid=3650` to all certificate templates
- Added proper `:delay` pauses after async certificate signing operations
- Added NAT masquerade and forwarding firewall rules
- Expanded IP pool from 14 to 253 addresses
- Replaced repetitive copy-paste blocks with a loop in `ovpn_clients.rsc`
- Added input validation and error checking to shell scripts
- Added `.gitignore` to prevent accidental commits of sensitive files
- Used `printf` instead of `echo` for portable newline handling in `create_config.sh`
