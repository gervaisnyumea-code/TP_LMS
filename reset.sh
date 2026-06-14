# NOM: NYUMEA PEHA DARYL GERVAIS
# MATRICULE: 24H2571
# NIVEAU : LICENCE 2
# UNIVERSITE : UNIVERSITE DE YAOUNDE 1

#!/bin/bash

# NOM: NYUMEA PEHA DARYL GERVAIS
# MATRICULE: 24H2571
# NIVEAU : LICENCE 2
# UNIVERSITE : UNIVERSITE DE YAOUNDE 1

cd "$(dirname "$0")"

echo "Reset complet de LMS Cameroun (donnees supprimees)..."
docker compose down -v
echo "Volumes supprimes."
echo ""
echo "Relancez avec : ./start.sh"
