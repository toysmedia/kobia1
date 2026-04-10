## ============================================================
## MikroTik OpenVPN Client Certificate Generator
## Adapted from: ageapps/MikroTik_OpenVPN_Server_Setup
## Repository: toysmedia/oxnett
## ============================================================
## This script creates 5 VPN users with certificates.
## Adjust NUMCLIENTS and the loop below for more/fewer users.
## ============================================================

:global CN "CA"
:global NUMCLIENTS 5

:for i from=1 to=$NUMCLIENTS do={
    :local USERNAME "user$i"
    :local PASSWORD "user000$i"

    :put "=== Creating VPN user: $USERNAME ==="

    ## Add PPP secret (user/pass for OpenVPN authentication)
    /ppp secret add name=$USERNAME password=$PASSWORD profile=OVPN-PROFILE service=ovpn

    ## Generate client certificate from template
    /certificate add name="client-tpl-$USERNAME" copy-from="CLIENT-tpl" common-name="$USERNAME@$CN"

    ## Sign with CA
    /certificate sign "client-tpl-$USERNAME" ca="$CN" name="$USERNAME@$CN"
    :delay 20

    ## Export client certificate and private key
    /certificate export-certificate "$USERNAME@$CN" export-passphrase="$PASSWORD"
    :delay 2

    :put "=== User $USERNAME created successfully ==="
}

## Export the CA certificate (needed by all clients)
/certificate export-certificate "$CN" export-passphrase=""

:put "============================================"
:put "All $NUMCLIENTS users created successfully!"
:put "Download the exported certificates from"
:put "Files menu in your MikroTik router."
:put "============================================"
