#!/bin/bash
cd "$(dirname "$0")"

echo "Reset complet de LMS Cameroun (donnees supprimees)..."
docker compose down -v
echo "Volumes supprimes."
echo ""
echo "Relancez avec : ./start.sh"
