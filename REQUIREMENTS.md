<!-- 
NOM: NYUMEA PEHA DARYL GERVAIS
MATRICULE: 24H2571
NIVEAU : LICENCE 2
UNIVERSITE : UNIVERSITE DE YAOUNDE 1
-->

# PRE-REQUIS TECHNIQUES -- LMS CAMEROUN

## Stack technique

| Composant | Technologie | Version |
|-----------|-------------|---------|
| Langage | PHP | 8.2+ |
| Base de donnees | PostgreSQL (Neon) | 16 |
| Serveur web | Apache | 2.4+ |
| Conteneurisation | Docker + Docker Compose | 24+ / 2+ |
| Stockage fichiers | Cloudinary | API v2 |
| Frontend | HTML5 + CSS3 + Vanilla JS | - |
| Police | Inter (Google Fonts) | 400-700 |

## Extensions PHP requises

| Extension | Usage |
|-----------|-------|
| `pdo_pgsql` | Connexion PostgreSQL |
| `pgsql` | Fonctions PostgreSQL natives |
| `mbstring` | Traitement chaines Unicode |
| `fileinfo` | Verification MIME des uploads |
| `gd` | Traitement images |
| `curl` | Appels API Cloudinary |
| `json` | Encodage/decodage JSON |
| `zip` | Compression (optionnel) |

## Modules Apache requis

| Module | Usage |
|--------|-------|
| `mod_rewrite` | Reecriture d'URL (.htaccess) |
| `mod_headers` | Headers de securite HTTP |

## Services externes (gratuits)

| Service | URL | Usage | Plan gratuit |
|---------|-----|-------|-------------|
| Neon | neon.tech | Base de donnees PostgreSQL serverless | 500 MB |
| Cloudinary | cloudinary.com | Stockage et CDN fichiers PDF/video | 25 GB |
| Render | render.com | Hebergement Docker | Free (spin-down) |

## Configuration PHP recommandee

| Parametre | Valeur | Raison |
|-----------|--------|--------|
| `upload_max_filesize` | 512M | Uploads video jusqu'a 500 Mo |
| `post_max_size` | 512M | Doit etre >= upload_max_filesize |
| `memory_limit` | 256M | Traitement fichiers volumineux |
| `max_execution_time` | 300 | Uploads longs |

## Variables d'environnement

Voir `.env.example` pour la liste complete.

### Obligatoires

| Variable | Description |
|----------|-------------|
| `DATABASE_URL` | URL de connexion Neon PostgreSQL |
| `APP_BASE_URL` | URL publique de l'application |

### Recommandees

| Variable | Description |
|----------|-------------|
| `CLOUDINARY_CLOUD_NAME` | Pour les uploads PDF/video |
| `CLOUDINARY_API_KEY` | Pour les uploads |
| `CLOUDINARY_API_SECRET` | Pour les uploads |

### Optionnelles (dev local Docker)

| Variable | Defaut | Description |
|----------|--------|-------------|
| `DB_HOST` | `db` | Hote PostgreSQL (Docker) |
| `DB_PORT` | `5432` | Port PostgreSQL |
| `DB_NAME` | `lms_cameroun` | Nom de la base |
| `DB_USER` | `lms_user` | Utilisateur |
| `DB_PASS` | `lms_password` | Mot de passe |

## Navigateurs supportes

| Navigateur | Version minimale |
|------------|-----------------|
| Chrome | 90+ |
| Firefox | 90+ |
| Safari | 14+ |
| Edge | 90+ |

## Limites connues (plan Render Free)

| Limitation | Impact | Contournement |
|------------|--------|---------------|
| Spin-down apres 15 min d'inactivite | Premier acces lent (~30s) | Acceptable pour un TP |
| 512 MB RAM | Builds potentiellement lents | Image Docker optimisee |
| Pas de systeme de fichiers persistant | Uploads locaux perdus | Cloudinary obligatoire |
