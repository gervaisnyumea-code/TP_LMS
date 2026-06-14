<!-- 
NOM: NYUMEA PEHA DARYL GERVAIS
MATRICULE: 24H2571
NIVEAU : LICENCE 2
UNIVERSITE : UNIVERSITE DE YAOUNDE 1
-->

# GUIDE DE DEMARRAGE RAPIDE -- LMS CAMEROUN

## Lancement en 5 minutes avec Docker

### 1. Cloner et configurer

```bash
git clone <url-du-repo> lms-cameroun
cd lms-cameroun
cp .env.example .env
```

### 2. Lancer les services

```bash
docker compose --profile dev up -d --build
```

Ceci demarre :
- **Application PHP** sur http://localhost:8080
- **PostgreSQL 16** sur le port 5433
- **Adminer** (admin BDD) sur http://localhost:8081

### 3. Initialiser les donnees

Le schema SQL est execute automatiquement au premier lancement.
Pour les donnees de demonstration :

```bash
docker compose exec app php sql/seed.php
```

### 4. Se connecter

| Role | Email | Mot de passe |
|------|-------|-------------|
| Promoteur | promoteur@lms.cm | promoteur123 |
| Enseignant | jean.dupont@lms.cm | enseignant123 |
| Etudiant | paul.kamga@lms.cm | etudiant123 |

Acces : http://localhost:8080/index.php?page=login

### 5. Configurer Cloudinary (optionnel, pour les uploads)

Si vous voulez tester les uploads PDF/video :

1. Creer un compte sur https://cloudinary.com (gratuit)
2. Copier vos identifiants dans `.env` :
   ```
   CLOUDINARY_CLOUD_NAME=votre_cloud_name
   CLOUDINARY_API_KEY=votre_api_key
   CLOUDINARY_API_SECRET=votre_api_secret
   ```
3. Redemarrer : `docker compose up -d`

Sans Cloudinary, les uploads seront ignorés. Les vues et la logique fonctionnent quand meme.

## Commandes utiles

```bash
# Voir les logs
docker compose logs -f app

# Arreter
docker compose down

# Rebuild apres modification de code
docker compose up -d --build

# Executer une commande PHP
docker compose exec app php -r "echo phpinfo();"

# Acces base de donnees
docker compose exec db psql -U lms_user -d lms_cameroun

# Reset complet (supprime toutes les donnees)
docker compose down -v
docker compose --profile dev up -d --build
docker compose exec app php sql/seed.php
```

## Deploiement sur Render

1. Pousser sur GitHub
2. Sur Render : creer un **Web Service** de type **Docker**
3. Dockerfile path : `./Dockerfile`
4. Ajouter les variables d'environnement :
   - `DATABASE_URL` (depuis Neon)
   - `CLOUDINARY_CLOUD_NAME`
   - `CLOUDINARY_API_KEY`
   - `CLOUDINARY_API_SECRET`
   - `APP_BASE_URL` (URL Render)
5. Deployer
6. Executer le schema et le seed sur Neon :
   ```bash
   psql "$DATABASE_URL" -f sql/schema.sql
   ```
