#!/bin/bash
cd "$(dirname "$0")"

echo "Arret de LMS Cameroun..."
docker compose down

echo "LMS Cameroun arrete."
