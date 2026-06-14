# NOM: NYUMEA PEHA DARYL GERVAIS
# MATRICULE: 24H2571
# NIVEAU : LICENCE 2
# UNIVERSITE : UNIVERSITE DE YAOUNDE 1

#!/bin/bash
set -e

SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"
cd "$SCRIPT_DIR"

GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m'

echo -e "${BLUE}╔══════════════════════════════════════════╗${NC}"
echo -e "${BLUE}║      LMS CAMEROUN -- LANCEMENT          ║${NC}"
echo -e "${BLUE}╚══════════════════════════════════════════╝${NC}"
echo ""

# 1. Verifier Docker
if ! command -v docker &> /dev/null; then
    echo -e "${RED}[ERREUR] Docker n'est pas installe.${NC}"
    echo "Installe Docker : https://docs.docker.com/get-docker/"
    exit 1
fi

if ! docker compose version &> /dev/null; then
    echo -e "${RED}[ERREUR] Docker Compose n'est pas disponible.${NC}"
    exit 1
fi

echo -e "${GREEN}[OK]${NC} Docker detecte"

# 2. Creer .env si absent
if [ ! -f .env ]; then
    echo -e "${YELLOW}[INFO]${NC} Creation du fichier .env depuis .env.example..."
    cp .env.example .env
    echo -e "${GREEN}[OK]${NC} .env cree"
else
    echo -e "${GREEN}[OK]${NC} .env existe deja"
fi

# NOM: NYUMEA PEHA DARYL GERVAIS
# MATRICULE: 24H2571
# NIVEAU : LICENCE 2
# UNIVERSITE : UNIVERSITE DE YAOUNDE 1


# 3. Lancer les conteneurs
echo ""
echo -e "${BLUE}[...] Build et lancement des conteneurs...${NC}"
docker compose --profile dev up -d --build

# 4. Attendre que PostgreSQL soit pret
echo -e "${BLUE}[...] Attente de PostgreSQL...${NC}"
for i in $(seq 1 30); do
    if docker compose exec -T db pg_isready -U lms_user -d lms_cameroun &> /dev/null; then
        echo -e "${GREEN}[OK]${NC} PostgreSQL est pret"
        break
    fi
    if [ "$i" -eq 30 ]; then
        echo -e "${RED}[ERREUR] PostgreSQL n'a pas demarre a temps.${NC}"
        docker compose logs db
        exit 1
    fi
    sleep 1
done

# 5. Seed (donnees de demo)
echo ""
echo -e "${BLUE}[...] Insertion des donnees de demonstration...${NC}"
docker compose exec -T app php sql/seed.php

# 6. Resultat
echo ""
echo -e "${GREEN}╔══════════════════════════════════════════╗${NC}"
echo -e "${GREEN}║       LMS CAMEROUN -- PRET !            ║${NC}"
echo -e "${GREEN}╚══════════════════════════════════════════╝${NC}"
echo ""
echo -e "  Application :  ${BLUE}http://localhost:8080${NC}"
echo -e "  Adminer BDD :  ${BLUE}http://localhost:8081${NC}"
echo ""
echo -e "  ${YELLOW}Comptes de connexion :${NC}"
echo -e "    Promoteur  : promoteur@lms.cm / promoteur123"
echo -e "    Enseignant : jean.dupont@lms.cm / enseignant123"
echo -e "    Etudiant   : paul.kamga@lms.cm / etudiant123"
echo ""
echo -e "  ${YELLOW}Commandes utiles :${NC}"
echo -e "    ./start.sh          # Relancer"
echo -e "    ./stop.sh           # Arreter"
echo -e "    ./reset.sh          # Reset complet (supprime donnees)"
echo ""
