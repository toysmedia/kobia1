#!/bin/bash
# ============================================================
# Generate OpenVPN client configuration files (.ovpn)
# Run this on your LOCAL machine after downloading certificates
# ============================================================
# Usage: sh create_config.sh
# ============================================================

# >>>>>> CUSTOMIZE THESE VALUES <<<<<<
# PUBLIC_ADDRESS: public IP or domain of your billing server (BILLING_SERVER_PUBLIC_IP in .env)
# Change this if you are not deploying to the oxdes.com production server.
PUBLIC_ADDRESS="89.117.52.63"
PUBLIC_PORT="1194"              # OpenVPN server port (OPENVPN_PORT in .env)
PROTOCOL="tcp-client"                       # Protocol: tcp-client or udp
NUM_USERS=5                                 # Number of users to generate configs for
# >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>

CERT_DIR="certificates"
CONFIG_DIR="configs"
TEMPLATE="template.ovpn"

# Validate
if [ -z "$PUBLIC_ADDRESS" ]; then
    echo "ERROR: PUBLIC_ADDRESS is not set. Edit this script and set your server's public IP or domain!"
    exit 1
fi

if [ ! -f "$TEMPLATE" ]; then
    echo "ERROR: Template file '$TEMPLATE' not found!"
    exit 1
fi

if [ ! -d "$CERT_DIR" ]; then
    echo "ERROR: Certificates directory '$CERT_DIR' not found!"
    exit 1
fi

mkdir -p "$CONFIG_DIR"

for i in $(seq 1 $NUM_USERS); do
    USER_DIR="$CONFIG_DIR/user${i}"
    echo "Creating user $i configuration..."

    mkdir -p "$USER_DIR"

    echo "  Copying certificates and key..."
    cp "$CERT_DIR/cert_export_user${i}@CA.crt" "$USER_DIR/" 2>/dev/null
    cp "$CERT_DIR/cert_export_user${i}@CA.key" "$USER_DIR/" 2>/dev/null
    cp "$CERT_DIR/cert_export_CA.crt" "$USER_DIR/" 2>/dev/null

    echo "  Creating auth credentials file..."
    printf "user${i}\nuser000${i}\n" > "$USER_DIR/user.auth"

    echo "  Generating .ovpn config file..."
    sed "s/{CLIENT}/user${i}@CA/g; s/{PUBLIC_ADDRESS}/$PUBLIC_ADDRESS/g; s/{PUBLIC_PORT}/$PUBLIC_PORT/g; s/{PROTOCOL}/$PROTOCOL/g" \
        "$TEMPLATE" > "$USER_DIR/user${i}.ovpn"

    echo "  ✓ user${i} config ready at: $USER_DIR/"
    echo ""
done

echo "============================================"
echo "All $NUM_USERS user configurations created!"
echo "Each user folder in '$CONFIG_DIR/' contains:"
echo "  - .ovpn config file"
echo "  - CA certificate"
echo "  - Client certificate + key"
echo "  - Auth credentials"
echo "============================================"
