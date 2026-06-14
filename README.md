# LMS Cameroun

Plateforme d'apprentissage en ligne (LMS) pour l'ecosysteme universitaire camerounais.

## Stack

- **Backend** : PHP 8.2, Apache, PDO PostgreSQL
- **Base de donnees** : PostgreSQL (Neon, serverless)
- **Stockage fichiers** : Cloudinary (CDN)
- **Frontend** : HTML5, CSS3, Vanilla JavaScript, AJAX
- **Deploiement** : Docker sur Render

## Demarrage rapide

```bash
cp .env.example .env
docker compose --profile dev up -d --build
# Installation des dépendances PHP
docker exec lms_cameroun_app php composer.phar install
docker compose exec app php sql/seed.php
```

Acces : http://localhost:8080

Voir [QUICKSTART.md](QUICKSTART.md) pour le guide complet.

## Comptes de demonstration

| Role | Email | Mot de passe |
|------|-------|-------------|
| Promoteur | promoteur@lms.cm | promoteur123 |
| Enseignant | jean.dupont@lms.cm | enseignant123 |
| Etudiant | paul.kamga@lms.cm | etudiant123 |

## Fonctionnalites

- **3 roles** : Etudiant, Enseignant, Promoteur (cloisonnes)
- **Cours** : creation, lecons PDF/video, evaluations QCM
- **Progression** : suivi par lecon, calcul automatique, deverrouillage sequentiel
- **Certificats** : generation automatique a la validation d'un module complet
- **Securite** : requetes preparees, CSRF, XSS, upload MIME, bcrypt, sessions securisees
- **Responsive** : mobile-first, breakpoints 768px et 1024px

## Structure du projet

```
TP_LMS/
├── config/          # Configuration (BDD, constantes, session)
├── models/          # Modeles de donnees (7 classes + CloudinaryHelper)
├── actions/         # Traitements PHP (auth, enseignant, etudiant, promoteur)
├── views/           # Vues HTML (layouts, auth, 3 dashboards)
├── public/          # Assets (CSS, JS, icons SVG, uploads)
├── sql/             # Schema PostgreSQL + seed
├── docker/          # Configuration Apache pour Docker
├── index.php        # Routeur principal
├── Dockerfile       # Image PHP 8.2 + Apache
└── docker-compose.yml
```

## Documentation

- [QUICKSTART.md](QUICKSTART.md) -- Guide de demarrage rapide
- [REQUIREMENTS.md](REQUIREMENTS.md) -- Pre-requis techniques
- [CONSTRUCTION.md](CONSTRUCTION.md) -- Document de construction complet
- [CHECKLIST.md](CHECKLIST.md) -- Checklist de realisation et validation (163 tests)

## Licence

Voir [LICENSE](LICENSE).
