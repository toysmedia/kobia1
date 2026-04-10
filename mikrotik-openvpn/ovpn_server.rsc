## ============================================================
## MikroTik OpenVPN Server Setup Script
## Adapted from: ageapps/MikroTik_OpenVPN_Server_Setup
## Repository: toysmedia/oxnett
## ============================================================
##
## INTEGRATION NOTE:
## This script sets up the OpenVPN *server* on the billing server's MikroTik router.
## It is run once during initial provisioning.
##
## The Laravel app (app/Services/MikrotikScriptService.php) generates *client-side*
## provisioning scripts for each managed MikroTik router. Those scripts configure
## each router as an OpenVPN client connecting back to this server.
##
## The VPN subnet (VPNPOOL below) MUST match the Laravel config:
##   BILLING_SERVER_VPN_IP=10.8.0.1      (server's VPN IP — always .1)
##   BILLING_SERVER_SUBNET=10.8.0.0/24   (must match VPNPOOL.0/24)
##   OPENVPN_PORT=1194                   (must match OVPNPORT below)
## ============================================================

## PARAMETERS - CUSTOMIZE THESE FOR YOUR ENVIRONMENT
:global ORG "OxNett"
:global COUNTRY "US"
:global STATE "US"
:global LOCALITY "United States"
## Key sizes: 2048 (minimum recommended), 4096 (more secure)
:global KEYSIZE 4096
## VPN subnet prefix — MUST match BILLING_SERVER_SUBNET in .env (10.8.0.0/24)
## The server will use 10.8.0.1 as its VPN IP (BILLING_SERVER_VPN_IP)
:global VPNPOOL "10.8.0"
## OpenVPN port — MUST match OPENVPN_PORT in .env (default: 1194)
:global OVPNPORT "1194"

## ============================================================
## STEP 1: Create CA and Server Certificates
## ============================================================

# Create Certificate Authority template
/certificate add name=CA-tpl country="$COUNTRY" state="$STATE" locality="$LOCALITY" organization="$ORG" common-name="CA" key-size=$KEYSIZE key-usage=crl-sign,key-cert-sign days-valid=3650

# Create Server certificate template
/certificate add name=SERVER-tpl country="$COUNTRY" state="$STATE" locality="$LOCALITY" organization="$ORG" common-name="SERVER" key-size=$KEYSIZE key-usage=digital-signature,key-encipherment,tls-server days-valid=3650

# Create Client certificate template (used as base for client certs)
/certificate add name=CLIENT-tpl country="$COUNTRY" state="$STATE" locality="$LOCALITY" organization="$ORG" common-name="CLIENT" key-size=$KEYSIZE key-usage=tls-client days-valid=3650

# Sign the CA (self-signed)
/certificate sign CA-tpl ca-crl-host=127.0.0.1 name="CA"
:delay 5

# Sign the Server certificate with the CA
/certificate sign SERVER-tpl ca="CA" name="SERVER"
:delay 5

## ============================================================
## STEP 2: Create IP Pool for VPN Clients
## ============================================================
/ip pool add name=OVPN-POOL ranges="$VPNPOOL.2-$VPNPOOL.254"

## ============================================================
## STEP 3: Create VPN Profile
## ============================================================
/ppp profile add dns-server="$VPNPOOL.1" local-address="$VPNPOOL.1" name=OVPN-PROFILE remote-address=OVPN-POOL use-encryption=yes

## ============================================================
## STEP 4: Enable OpenVPN Server
## ============================================================
/interface ovpn-server server set auth=sha256 certificate="SERVER" cipher=aes128-cbc,aes192-cbc,aes256-cbc default-profile=OVPN-PROFILE enabled=yes require-client-certificate=yes port=$OVPNPORT

## ============================================================
## STEP 5: Add Firewall Rules
## ============================================================
# Allow OpenVPN traffic
/ip firewall filter add chain=input dst-port=$OVPNPORT protocol=tcp comment="Allow OpenVPN" place-before=0

# Allow traffic from VPN clients to the router (for DNS, etc.)
/ip firewall filter add chain=input src-address="$VPNPOOL.0/24" comment="Allow VPN clients to router" place-before=1

# Allow forwarding from VPN subnet
/ip firewall filter add chain=forward src-address="$VPNPOOL.0/24" comment="Allow VPN client forwarding" place-before=0

# NAT masquerade for VPN clients (so they can access the internet through the VPN)
/ip firewall nat add chain=srcnat src-address="$VPNPOOL.0/24" action=masquerade comment="NAT for VPN clients"

## ============================================================
## Setup Complete!
## Now import ovpn_clients.rsc to create user accounts.
##
## NEXT STEPS FOR LARAVEL INTEGRATION:
## 1. Download exported certificates from the router (Files panel in WinBox)
## 2. Place them in the Laravel app's storage so they can be served via
##    /api/router-certs/{router_id}/* endpoints
## 3. Set the following in the Laravel .env on the billing server:
##    BILLING_SERVER_PUBLIC_IP=89.117.52.63
##    BILLING_SERVER_VPN_IP=10.8.0.1
##    BILLING_SERVER_SUBNET=10.8.0.0/24
##    OPENVPN_PORT=1194
## 4. Each managed router will receive a dynamically generated provisioning
##    script from the Laravel app (Router panel → Generate Script).
## ============================================================
