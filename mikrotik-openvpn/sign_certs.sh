#!/bin/bash
# ============================================================
# Remove passphrases from MikroTik-exported client key files
# Run this on your LOCAL machine (not on the router)
# ============================================================
# Usage: sh sign_certs.sh [number_of_users]
# Default: 5 users
# ============================================================

NUM_USERS=${1:-5}
CERT_DIR="certificates"

if [ ! -d "$CERT_DIR" ]; then
    echo "ERROR: Directory '$CERT_DIR' not found!"
    echo "Please download certificates from your MikroTik router"
    echo "and place them in the '$CERT_DIR' folder."
    exit 1
fi

echo "Processing $NUM_USERS user certificates..."
echo ""

for i in $(seq 1 $NUM_USERS); do
    KEY_FILE="$CERT_DIR/cert_export_user${i}@CA.key"
    if [ -f "$KEY_FILE" ]; then
        echo "Removing passphrase from user $i certificate key..."
        echo "  Password hint: user000${i}"
        openssl rsa -in "$KEY_FILE" -out "$KEY_FILE"
        if [ $? -eq 0 ]; then
            echo "  ✓ user${i} key processed successfully"
        else
            echo "  ✗ ERROR processing user${i} key"
        fi
    else
        echo "  ⚠ Key file not found: $KEY_FILE (skipping)"
    fi
    echo ""
done

echo "Done! Key files are now ready for client configuration."
