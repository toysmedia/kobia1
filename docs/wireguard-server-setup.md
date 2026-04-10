# WireGuard Server Setup — Ubuntu Billing Server

This guide walks you through setting up WireGuard on the Ubuntu server that runs the iNettotik billing system. Each MikroTik router is added as a WireGuard peer, creating an encrypted tunnel used for RouterOS REST API calls.

---

## 1. Install WireGuard

```bash
sudo apt update
sudo apt install wireguard -y
```

---

## 2. Generate Server Keys

```bash
cd /etc/wireguard
umask 077
wg genkey | tee server_private.key | wg pubkey > server_public.key
cat server_public.key   # copy this value into your .env as WG_SERVER_PUBLIC_KEY
```

---

## 3. Create `/etc/wireguard/wg0.conf`

Replace `<SERVER_PRIVATE_KEY>` with the contents of `server_private.key`.

```ini
[Interface]
Address    = 10.255.255.1/24
ListenPort = 51820
PrivateKey = <SERVER_PRIVATE_KEY>

# Enable IP forwarding for the VPN subnet
PostUp   = iptables -A FORWARD -i wg0 -j ACCEPT; iptables -t nat -A POSTROUTING -o eth0 -j MASQUERADE
PostDown = iptables -D FORWARD -i wg0 -j ACCEPT; iptables -t nat -D POSTROUTING -o eth0 -j MASQUERADE

# --- Add each MikroTik router as a peer below ---
# [Peer]
# PublicKey  = <ROUTER_WG_PUBLIC_KEY>
# AllowedIPs = 10.255.255.X/32   # X = (router_id % 253) + 2
```

> **Subnet:** `10.255.255.0/24`  
> **Server IP in tunnel:** `10.255.255.1`  
> **Router IPs:** `10.255.255.2` through `10.255.255.254` (auto-assigned by billing system)

---

## 4. Set Environment Variables in `.env`

```dotenv
WG_SERVER_PUBLIC_KEY=<contents of server_public.key>
WG_PORT=51820
```

The billing system reads `WG_SERVER_PUBLIC_KEY` when generating MikroTik scripts so that each router peer can authenticate with this server.

---

## 5. Configure Firewall

```bash
sudo ufw allow 51820/udp
sudo ufw reload
```

---

## 6. Enable and Start WireGuard

```bash
sudo systemctl enable wg-quick@wg0
sudo systemctl start  wg-quick@wg0

# Verify it is running
sudo wg show
```

---

## 7. Adding a Router as a Peer

After provisioning a router with the MikroTik script, the router will have generated its own WireGuard key pair. Retrieve the router's public key from its RouterOS console:

```routeros
/interface wireguard print
```

Then add the peer to `/etc/wireguard/wg0.conf` on the billing server:

```ini
[Peer]
PublicKey  = <ROUTER_WG_PUBLIC_KEY>
AllowedIPs = 10.255.255.X/32
```

Where `X` is the octet assigned by the billing system: `(router_id % 253) + 2`.

Apply the change without restarting:

```bash
sudo wg addconf wg0 <(wg-quick strip wg0)
# or simply reload:
sudo systemctl reload wg-quick@wg0
```

---

## 8. Verify Connectivity

From the billing server, ping a router's VPN IP:

```bash
ping 10.255.255.2
```

From the MikroTik router, ping the billing server:

```routeros
/ping 10.255.255.1
```

---

## Notes

- The billing system prefers the router's `vpn_ip` (`10.255.255.X`) over its `wan_ip` for all RouterOS REST API calls. Never expose port 80 of the RouterOS API to the public internet.
- The REST API uses HTTP Basic Auth over the WireGuard tunnel, which is acceptable inside the encrypted VPN.
- If a router's WireGuard public key changes (e.g. after a reset), update the `[Peer]` block in `wg0.conf` and reload.
