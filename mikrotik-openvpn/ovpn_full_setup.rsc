## ============================================================
## OxNett — MikroTik OpenVPN Full Automated Setup
## Paste this ONCE into MikroTik terminal — it does everything.
## Or import via: /import file=ovpn_full_setup.rsc
## ============================================================
##
## This script combines ovpn_server.rsc and ovpn_clients.rsc into
## a single fully-automated setup with smart polling instead of
## fixed :delay timers, so it works correctly even on slow hardware
## with 4096-bit keys.
##
## INTEGRATION NOTE:
## The VPN subnet (VPNPOOL below) MUST match the Laravel config:
##   BILLING_SERVER_VPN_IP=10.8.0.1      (server's VPN IP — always .1)
##   BILLING_SERVER_SUBNET=10.8.0.0/24   (must match VPNPOOL.0/24)
##   OPENVPN_PORT=1194                   (must match OVPNPORT below)
## ============================================================

## ============================================================
## PARAMETERS — customize before pasting/importing
## ============================================================
## SECURITY WARNING: Change the client passwords below (or in /ppp secret after
## setup) before deploying to production. The default passwords are weak placeholders.
:global ORG "OxNett"
:global COUNTRY "KE"
:global STATE "KE"
:global LOCALITY "Kenya"
## Key sizes: 2048 (minimum), 4096 (recommended — signing takes longer)
:global KEYSIZE 4096
## VPN subnet prefix — MUST match BILLING_SERVER_SUBNET in .env (10.8.0.0/24)
:global VPNPOOL "10.8.0"
## OpenVPN port — MUST match OPENVPN_PORT in .env
:global OVPNPORT "1194"
## Number of client users/certificates to create
:global NUMCLIENTS 5

:log info "OxNett OpenVPN: starting full automated setup"
:put "============================================================"
:put "OxNett OpenVPN Full Automated Setup"
:put "============================================================"

## ============================================================
## STEP 1: Create certificate templates
## Wrapped in :do on-error so re-runs skip already-existing certs
## ============================================================
:put "STEP 1: Creating certificate templates..."
:log info "OxNett OpenVPN: creating certificate templates"

:do { /certificate add name=CA-tpl country=$COUNTRY state=$STATE locality=$LOCALITY organization=$ORG common-name="CA" key-size=$KEYSIZE key-usage=crl-sign,key-cert-sign days-valid=3650 } on-error={}
:do { /certificate add name=SERVER-tpl country=$COUNTRY state=$STATE locality=$LOCALITY organization=$ORG common-name="SERVER" key-size=$KEYSIZE key-usage=digital-signature,key-encipherment,tls-server days-valid=3650 } on-error={}
:do { /certificate add name=CLIENT-tpl country=$COUNTRY state=$STATE locality=$LOCALITY organization=$ORG common-name="CLIENT" key-size=$KEYSIZE key-usage=tls-client days-valid=3650 } on-error={}

:put "Certificate templates created (or already exist)."

## ============================================================
## STEP 2: Sign CA certificate, then poll until ready
## Polling every 2s, timeout after 180s (3 min for 4096-bit keys)
## ============================================================
:put "STEP 2: Signing CA certificate (this may take 1-3 minutes)..."
:log info "OxNett OpenVPN: signing CA certificate"

## Note: The :while loops below are intentionally written as single lines.
## This supports both /import and direct terminal paste workflows.
## (Multi-line blocks do not work when pasted directly into the MikroTik terminal.)

:do { /certificate sign CA-tpl ca-crl-host=127.0.0.1 name="CA" } on-error={}

:local caReady false; :local caWait 0; :while ($caReady != true && $caWait < 180) do={ :do { :if ([:len [/certificate find where name="CA" private-key=yes]] > 0) do={ :set caReady true } } on-error={}; :if ($caReady != true) do={ :delay 2s; :set caWait ($caWait + 2) } }

:if ($caReady = true) do={ :put "CA certificate signed successfully." ; :log info "OxNett OpenVPN: CA certificate ready" } else={ :put "ERROR: CA signing timed out after 180s." ; :log error "OxNett OpenVPN: CA signing timed out" ; :error "CA signing failed — check System > Certificates" }

## ============================================================
## STEP 3: Sign SERVER certificate, then poll until ready
## ============================================================
:put "STEP 3: Signing SERVER certificate (this may take 1-3 minutes)..."
:log info "OxNett OpenVPN: signing SERVER certificate"

:do { /certificate sign SERVER-tpl ca="CA" name="SERVER" } on-error={}

:local srvReady false; :local srvWait 0; :while ($srvReady != true && $srvWait < 180) do={ :do { :if ([:len [/certificate find where name="SERVER" private-key=yes]] > 0) do={ :set srvReady true } } on-error={}; :if ($srvReady != true) do={ :delay 2s; :set srvWait ($srvWait + 2) } }

:if ($srvReady = true) do={ :put "SERVER certificate signed successfully." ; :log info "OxNett OpenVPN: SERVER certificate ready" } else={ :put "ERROR: SERVER signing timed out after 180s." ; :log error "OxNett OpenVPN: SERVER signing timed out" ; :error "SERVER signing failed — check System > Certificates" }

## ============================================================
## STEP 4: Create IP pool for VPN clients
## ============================================================
:put "STEP 4: Creating VPN IP pool..."
:log info "OxNett OpenVPN: creating IP pool"

:do { /ip pool add name=OVPN-POOL ranges=($VPNPOOL . ".2-" . $VPNPOOL . ".254") } on-error={}

:put "IP pool OVPN-POOL created (or already exists)."

## ============================================================
## STEP 5: Create PPP profile
## ============================================================
:put "STEP 5: Creating PPP profile..."
:log info "OxNett OpenVPN: creating PPP profile"

:do { /ppp profile add dns-server=($VPNPOOL . ".1") local-address=($VPNPOOL . ".1") name=OVPN-PROFILE remote-address=OVPN-POOL use-encryption=yes } on-error={}

:put "PPP profile OVPN-PROFILE created (or already exists)."

## ============================================================
## STEP 6: Enable OpenVPN server
## ============================================================
:put "STEP 6: Enabling OpenVPN server..."
:log info "OxNett OpenVPN: enabling OpenVPN server"

/interface ovpn-server server set auth=sha256 certificate="SERVER" cipher=aes128-cbc,aes192-cbc,aes256-cbc default-profile=OVPN-PROFILE enabled=yes require-client-certificate=yes port=$OVPNPORT

:put "OpenVPN server enabled on port $OVPNPORT."

## ============================================================
## STEP 7: Add firewall and NAT rules (idempotent — remove first)
## ============================================================
:put "STEP 7: Adding firewall and NAT rules..."
:log info "OxNett OpenVPN: configuring firewall rules"

:do { /ip firewall filter remove [find comment="Allow OpenVPN"] } on-error={}
:do { /ip firewall filter remove [find comment="Allow VPN clients to router"] } on-error={}
:do { /ip firewall filter remove [find comment="Allow VPN client forwarding"] } on-error={}
:do { /ip firewall nat remove [find comment="NAT for VPN clients"] } on-error={}

/ip firewall filter add chain=input dst-port=$OVPNPORT protocol=tcp comment="Allow OpenVPN" place-before=0
/ip firewall filter add chain=input src-address=($VPNPOOL . ".0/24") comment="Allow VPN clients to router" place-before=1
/ip firewall filter add chain=forward src-address=($VPNPOOL . ".0/24") comment="Allow VPN client forwarding" place-before=0
/ip firewall nat add chain=srcnat src-address=($VPNPOOL . ".0/24") action=masquerade comment="NAT for VPN clients"

:put "Firewall and NAT rules configured."

## ============================================================
## STEP 8: Create client users and certificates
## ============================================================
:put "STEP 8: Creating $NUMCLIENTS client users and certificates..."
:log info ("OxNett OpenVPN: creating " . $NUMCLIENTS . " client users")

:for i from=1 to=$NUMCLIENTS do={
    :local USERNAME ("user" . $i)
    :local PASSWORD ("user000" . $i)

    :put ("  Creating user: " . $USERNAME . " ...")
    :log info ("OxNett OpenVPN: processing user " . $USERNAME)

    ## Add PPP secret — skip if already exists
    :do { /ppp secret add name=$USERNAME password=$PASSWORD profile=OVPN-PROFILE service=ovpn } on-error={}

    ## Create client certificate from template — skip if already exists
    :do { /certificate add name=("client-tpl-" . $USERNAME) copy-from="CLIENT-tpl" common-name=($USERNAME . "@CA") } on-error={}

    ## Sign the client certificate
    :do { /certificate sign ("client-tpl-" . $USERNAME) ca="CA" name=($USERNAME . "@CA") } on-error={}

    ## Poll until client cert is signed (timeout 180s)
    :local clientReady false; :local clientWait 0; :while ($clientReady != true && $clientWait < 180) do={ :do { :if ([:len [/certificate find where name=($USERNAME . "@CA") private-key=yes]] > 0) do={ :set clientReady true } } on-error={}; :if ($clientReady != true) do={ :delay 2s; :set clientWait ($clientWait + 2) } }

    :if ($clientReady = true) do={
        :put ("  " . $USERNAME . " certificate signed.")
        :log info ("OxNett OpenVPN: " . $USERNAME . " certificate ready")
        /certificate export-certificate ($USERNAME . "@CA") export-passphrase=$PASSWORD
        :delay 2s
        :put ("  " . $USERNAME . " created and certificate exported.")
    } else={
        :put ("  WARNING: " . $USERNAME . " certificate signing timed out — skipping export.")
        :log warning ("OxNett OpenVPN: " . $USERNAME . " certificate signing timed out")
    }
}

## ============================================================
## STEP 9: Export CA certificate
## ============================================================
:put "STEP 9: Exporting CA certificate..."
:log info "OxNett OpenVPN: exporting CA certificate"

/certificate export-certificate "CA" export-passphrase=""

:put "CA certificate exported."

## ============================================================
## DONE
## ============================================================
:log info "OxNett OpenVPN: full automated setup complete"
:put "============================================================"
:put "OpenVPN server setup complete!"
:put "All certificates have been exported to Files."
:put ""
:put "Next steps:"
:put "  1. In WinBox: Files — download all cert_export_* files"
:put "  2. Run sign_certs.sh locally to strip key passphrases"
:put "  3. Run create_config.sh to build .ovpn client packages"
:put "  4. Set these values in the Laravel .env on the billing server:"
:put ("     BILLING_SERVER_VPN_IP=" . $VPNPOOL . ".1")
:put ("     BILLING_SERVER_SUBNET=" . $VPNPOOL . ".0/24")
:put ("     OPENVPN_PORT=" . $OVPNPORT)
:put "============================================================"
