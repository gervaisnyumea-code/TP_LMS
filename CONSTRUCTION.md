<!-- 
NOM: NYUMEA PEHA DARYL GERVAIS
MATRICULE: 24H2571
NIVEAU : LICENCE 2
UNIVERSITE : UNIVERSITE DE YAOUNDE 1
-->

# DOCUMENT DE CONSTRUCTION -- PLATEFORME LMS CAMEROUN

> Reference technique unique pour le developpement. Ce document fait autorite sur toutes les decisions d'architecture, de design, de deploiement et d'implementation.
> Stack : PHP 8.2 / PostgreSQL (Neon) / Apache / Docker / Cloudinary / Vanilla JS / AJAX

---

## 1. ARCHITECTURE CIBLE -- DOSSIERS ET FICHIERS

```
TP_LMS/
|
|-- config/
|   |-- database.php              # Connexion PDO, lecture depuis variables d'env
|   |-- constants.php             # Constantes applicatives (chemins, seuils, roles)
|   `-- session.php               # Initialisation session, helpers auth, CSRF, flash
|
|-- models/
|   |-- Utilisateur.php           # CRUD utilisateurs, authentification bcrypt
|   |-- Module.php                # CRUD modules (Promoteur), validation module
|   |-- Cours.php                 # CRUD cours (Enseignant), findByInscriptions
|   |-- Lecon.php                 # CRUD lecons, ordonnancement, countByCours
|   |-- Evaluation.php            # CRUD evaluations + questions QCM
|   |-- Progression.php           # Suivi etudiant, calculs, upsert avec GREATEST
|   |-- Certificat.php            # Generation, verification, code unique SHA-256
|   `-- CloudinaryHelper.php      # Upload/suppression fichiers via API Cloudinary
|
|-- actions/
|   |-- auth/
|   |   |-- login.php             # Traitement connexion : authenticate + regenerate_id
|   |   |-- logout.php            # Destruction session + cookie
|   |   `-- register.php          # Inscription etudiant : validation + create
|   |
|   |-- enseignant/
|   |   |-- cours_create.php      # Creation cours : validation + INSERT
|   |   |-- cours_update.php      # Modification cours : verification propriete
|   |   |-- cours_delete.php      # Suppression cours : cascade BDD
|   |   |-- lecon_create.php      # Ajout lecon : upload PDF/video securise (MIME)
|   |   |-- lecon_update.php      # Modification lecon : remplacement fichier
|   |   |-- lecon_delete.php      # Suppression lecon : fichier physique + BDD
|   |   |-- evaluation_create.php # Creation evaluation + boucle questions
|   |   `-- evaluation_update.php # Modification evaluation + CRUD questions
|   |
|   |-- etudiant/
|   |   |-- cours_inscrire.php    # Inscription cours : verification unicite
|   |   |-- submit_quiz.php       # Soumission AJAX quiz : calcul + certificat auto
|   |   |-- lecon_viewed.php      # Marquage AJAX lecon consultee
|   |   |-- certificat_download.php # Generation vue imprimable certificat
|   |   `-- file_serve.php        # Servir fichiers PDF/video avec controle d'acces
|   |
|   `-- promoteur/
|       |-- module_create.php     # Creation module : promoteur_id
|       |-- module_update.php     # Modification module : titre + description
|       |-- module_delete.php     # Suppression module : cours liberes (SET NULL)
|       |-- module_assign.php     # Associer/retirer cours a un module
|       `-- promoteur_stats.php   # Statistiques globales plateforme
|
|-- views/
|   |-- layouts/
|   |   |-- header.php            # DOCTYPE, meta, CSS, Google Fonts, session check
|   |   |-- footer.php            # Fermeture HTML, JS conditionnels, flash messages
|   |   `-- sidebar.php           # Navigation laterale contextuelle (3 roles)
|   |
|   |-- auth/
|   |   |-- login.php             # Page connexion : centrage plein ecran, SVG
|   |   `-- register.php          # Page inscription : 5 champs, validation
|   |
|   |-- etudiant/
|   |   |-- dashboard.php         # Accueil : stats + cours en cours + disponibles
|   |   |-- cours_liste.php       # Catalogue : grille de cours + bouton inscription
|   |   |-- cours_detail.php      # Detail cours : plan lecons + progression + statuts
|   |   |-- lecon_player.php      # Lecteur : video HTML5 / PDF iframe + quiz
|   |   |-- quiz_modal.php        # Fragment AJAX : formulaire QCM + zone resultat
|   |   |-- progression.php       # Vue globale : detail par cours + stats
|   |   |-- certificats.php       # Liste des certificats obtenus + en attente
|   |   |-- certificat.php        # Vue imprimable d'un certificat (A4, @media print)
|   |   `-- certificat_verifier.php # Page publique : verification par code
|   |
|   |-- enseignant/
|   |   |-- dashboard.php         # Accueil : stats cours + lecons + etudiants
|   |   |-- cours_gestion.php     # Liste cours : cartes + creation + modification
|   |   |-- cours_edit.php        # Edition : lecons + evaluations + questions
|   |   `-- statistiques.php      # Stats reussite : tableau etudiants + taux
|   |
|   `-- promoteur/
|       |-- dashboard.php         # Accueil : KPIs globaux plateforme
|       |-- modules_gestion.php   # Liste modules : creation + modification
|       |-- module_edit.php       # Edition : associer/retirer cours + certificats
|       `-- supervision.php       # Supervision : enseignants, etudiants, certificats
|
|-- public/
|   |-- css/
|   |   |-- reset.css             # Reset CSS normalise (box-sizing, margin, padding)
|   |   |-- variables.css         # Variables : 12 couleurs, polices, espacements, ombres
|   |   |-- base.css              # Body, headings, liens, container, utilitaires
|   |   |-- components.css        # Boutons, cartes, badges, modales, formulaires
|   |   |-- layout.css            # Sidebar, topbar, grille app-layout, responsive
|   |   `-- pages.css             # Dashboard, cours, lecons, quiz, certificat, login
|   |
|   |-- js/
|   |   |-- app.js                # Init, sidebar toggle, flash auto-hide, confirm
|   |   |-- quiz.js               # Ouverture modale, soumission AJAX, corrections
|   |   |-- progression.js        # Mise a jour barres + badges apres quiz
|   |   `-- player.js             # Lecteur video HTML5 : play/pause, seek, volume, fin
|   |
|   |-- img/
|   |   |-- icons/                # Icones SVG inline (navigation, actions, etats)
|   |   `-- logo/                 # Logo plateforme SVG
|   |
|   |-- uploads/
|   |   |-- pdf/                  # Fichiers PDF des lecons
|   |   |-- video/                # Fichiers video des lecons
|   |   `-- .htaccess             # Deny from all (acces via file_serve.php uniquement)
|   |
|   `-- .htaccess                 # Protection fichiers sensibles, reecriture
|
|-- sql/
|   |-- schema.sql                # Script complet : 9 tables + 8 index
|   `-- seed.php                  # Donnees demo : 3 utilisateurs + cours + lecons + quiz
|
|-- docker/
|   `-- apache.conf               # Configuration VirtualHost Apache pour Docker
|
|-- .env.example                  # Template variables d'environnement
|-- .gitignore                    # Exclusions : .env, uploads, vendor, IDE
|-- Dockerfile                    # Image PHP 8.2 + Apache + extensions
|-- docker-compose.yml            # Orchestration : app + MySQL 8 + phpMyAdmin (dev)
|-- index.php                     # Routeur principal : dispatch GET 'page'
|-- .htaccess                     # Reecriture URL racine + headers securite
|-- CONSTRUCTION.md               # Ce document
`-- CHECKLIST.md                  # Checklist complete de realisation et validation
```

### 1.1 Recapitulatif fichiers

| Categorie | Fichiers | Remplis | Vides |
|-----------|----------|---------|-------|
| Config | 3 | 3 | 0 |
| Models | 8 | 8 | 0 |
| Actions | 18 | 15 | 3 |
| Views | 22 | 20 | 2 |
| CSS | 6 | 6 | 0 |
| JS | 4 | 4 | 0 |
| SQL | 2 | 2 | 0 |
| Infrastructure | 10 | 10 | 0 |
| **TOTAL** | **73** | **68** | **5** |

**5 fichiers encore vides a implementer :**
- `actions/enseignant/lecon_update.php`
- `actions/enseignant/evaluation_update.php`
- `actions/promoteur/promoteur_stats.php`
- `views/etudiant/quiz_modal.php`
- `views/etudiant/certificat.php`

---

## 2. DOCKER ET DEPLOIEMENT

### 2.1 Pourquoi Docker

Render ne supporte pas PHP nativement. Le deploiement se fait via un **Dockerfile** qui construit une image PHP 8.2 + Apache. La base de donnees est hebergee sur **Neon PostgreSQL** (service externe, serverless, gratuit). Les fichiers PDF/video sont stockes sur **Cloudinary** (CDN externe, gratuit 25GB). Localement, `docker-compose` orchestre l'application et un conteneur PostgreSQL pour le developpement.

### 2.2 Dockerfile

```dockerfile
FROM php:8.2-apache

RUN apt-get update && apt-get install -y \
    libzip-dev libpng-dev libjpeg-dev libfreetype6-dev libpq-dev libcurl4-openssl-dev unzip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo pdo_pgsql pgsql mbstring zip gd fileinfo curl \
    && a2enmod rewrite headers \
    && rm -rf /var/lib/apt/lists/*

RUN echo "upload_max_filesize = 512M" > /usr/local/etc/php/conf.d/uploads.ini \
    && echo "post_max_size = 512M" >> /usr/local/etc/php/conf.d/uploads.ini \
    && echo "memory_limit = 256M" >> /usr/local/etc/php/conf.d/uploads.ini

COPY docker/apache.conf /etc/apache2/sites-available/000-default.conf

WORKDIR /var/www/html
COPY . /var/www/html/

RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

EXPOSE 80
CMD ["apache2-foreground"]
```

**Extensions PHP installees :** pdo, pdo_pgsql, pgsql, mbstring, zip, gd, fileinfo, curl
**Modules Apache actives :** rewrite, headers

### 2.3 docker-compose.yml

3 services :

| Service | Image | Port | Role |
|---------|-------|------|------|
| `app` | Build local (Dockerfile) | 8080:80 | Application PHP + Apache |
| `db` | postgres:16-alpine | 5433:5432 | Base de donnees PostgreSQL |
| `adminer` | adminer:latest | 8081:8080 | Admin BDD (profil dev uniquement) |

**Volumes persistants :**
- `pg_data` : donnees PostgreSQL

**Healthcheck PostgreSQL :** Le service `app` attend que `db` soit `healthy` avant de demarrer.

**Initialisation automatique :** `sql/schema.sql` est monte dans `/docker-entrypoint-initdb.d/` et execute au premier lancement du conteneur `db`.

### 2.4 docker/apache.conf

Configuration Apache securisee :
- `AllowOverride All` pour supporter les `.htaccess`
- `Require all denied` sur `public/uploads/` (acces via `file_serve.php`)
- Headers de securite : X-Content-Type-Options, X-Frame-Options, X-XSS-Protection, Referrer-Policy

### 2.5 Commandes Docker

```bash
# Lancer en developpement (avec Adminer)
docker compose --profile dev up -d

# Lancer en production (sans Adminer)
docker compose up -d

# Arreter
docker compose down

# Rebuild apres modification
docker compose up -d --build

# Executer le seed
docker compose exec app php sql/seed.php

# Logs
docker compose logs -f app

# Acces PostgreSQL
docker compose exec db psql -U lms_user -d lms_cameroun
```

### 2.6 Deploiement sur Render

| Parametre | Valeur |
|-----------|--------|
| Type | Docker |
| Region | Frankfurt (le plus proche d'Afrique) |
| Dockerfile Path | `./Dockerfile` |
| Port | 80 |
| Plan | Free ou Starter |

**Variables d'environnement sur Render :**

| Variable | Exemple | Description |
|----------|---------|-------------|
| `DATABASE_URL` | `postgresql://user:pass@ep-xxx.neon.tech/db?sslmode=require` | URL Neon PostgreSQL (pooling) |
| `CLOUDINARY_CLOUD_NAME` | `votre_cloud` | Nom du cloud Cloudinary |
| `CLOUDINARY_API_KEY` | `123456789` | Cle API Cloudinary |
| `CLOUDINARY_API_SECRET` | `xxxxx` | Secret API Cloudinary |
| `APP_BASE_URL` | `https://lms.onrender.com` | URL publique |

**Neon PostgreSQL :** Creer un projet sur neon.tech (gratuit, 500MB). Copier l'URL de connexion pooling. Ajouter `?sslmode=require` si absent.

---

## 3. CHARTE GRAPHIQUE ET DESIGN SYSTEM

### 3.1 Palette de couleurs

| Token | Valeur | Usage |
|-------|--------|-------|
| `--color-primary` | `#1B3A5C` | Bleu marine. Navigation, sidebar, CTA principal |
| `--color-secondary` | `#2E6B9E` | Bleu moyen. Liens, accents, boutons secondaires |
| `--color-accent` | `#3D8ECF` | Bleu clair. Hover, focus, elements actifs |
| `--color-success` | `#2D7D46` | Vert sobre. Validation, lecon reussie, certificat |
| `--color-error` | `#B5352A` | Rouge discret. Erreurs, echec evaluation |
| `--color-warning` | `#C47F17` | Ocre. Avertissements, en attente |
| `--color-locked` | `#8C8C8C` | Gris. Verrouille, desactive |
| `--color-bg` | `#F5F6F8` | Gris tres clair. Fond de page |
| `--color-surface` | `#FFFFFF` | Blanc. Cartes, panneaux, modales |
| `--color-border` | `#D9DCE1` | Gris moyen. Bordures, separateurs |
| `--color-text` | `#1A1A2E` | Noir bleute. Texte principal |
| `--color-text-muted` | `#6B7280` | Gris. Texte secondaire, meta |

### 3.2 Typographie

| Usage | Police | Taille | Graisse |
|-------|--------|--------|---------|
| Titres page | Inter, system-ui | 1.75rem | 700 |
| Titres section | Inter, system-ui | 1.25rem | 600 |
| Corps de texte | Inter, system-ui | 1rem | 400 |
| Labels, metas | Inter, system-ui | 0.875rem | 500 |
| Code, donnees | JetBrains Mono, mono | 0.875rem | 400 |

Police chargee via Google Fonts : `Inter` (400, 500, 600, 700).

### 3.3 Iconographie

- Format : SVG inline uniquement. Aucun emoji. Aucune police d'icones externe.
- Stockage : `public/img/icons/` pour les SVG statiques.
- Integration : inline PHP pour les icones de navigation et d'etat.
- Tailles : 20px inline, 24px navigation, 32px dashboard stats, 48px illustrations.
- Couleur : `currentColor` (herite du contexte).

**Icones SVG requises (liste minimale) :**

| Usage | Fichier SVG |
|-------|-------------|
| Dashboard | `icons/dashboard.svg` |
| Cours / Livres | `icons/book-open.svg` |
| Lecons / Document | `icons/file-text.svg` |
| Video | `icons/video.svg` |
| PDF | `icons/file.svg` |
| Utilisateurs | `icons/users.svg` |
| Utilisateur | `icons/user.svg` |
| Progression | `icons/trending-up.svg` |
| Certificat | `icons/award.svg` |
| Verification | `icons/shield-check.svg` |
| Module / Dossier | `icons/folder.svg` |
| Recherche | `icons/search.svg` |
| Ajouter | `icons/plus.svg` |
| Modifier | `icons/edit.svg` |
| Supprimer | `icons/trash.svg` |
| Connexion | `icons/log-in.svg` |
| Deconnexion | `icons/log-out.svg` |
| Cadenas | `icons/lock.svg` |
| Valide / Coche | `icons/check-circle.svg` |
| Erreur | `icons/x-circle.svg` |
| Fleche haut | `icons/chevron-up.svg` |
| Fleche bas | `icons/chevron-down.svg` |
| Menu hamburger | `icons/menu.svg` |
| Fermer | `icons/x.svg` |
| Parametres | `icons/settings.svg` |
| Play | `icons/play.svg` |
| Pause | `icons/pause.svg` |
| Volume | `icons/volume-2.svg` |
| Mute | `icons/volume-x.svg` |
| Plein ecran | `icons/maximize.svg` |
| Imprimer | `icons/printer.svg` |
| Telecharger | `icons/download.svg` |
| Supervision | `icons/eye.svg` |
| Statistiques | `icons/bar-chart.svg` |

### 3.4 Composants UI

| Composant | Description |
|-----------|-------------|
| `.btn-primary` | Plein bleu marine. CTA principal |
| `.btn-secondary` | Contourne bleu. Actions secondaires |
| `.btn-danger` | Rouge discret. Suppressions |
| `.btn-success` | Vert sobre. Validation |
| `.btn-disabled` | Gris, cursor not-allowed, opacity 0.6 |
| `.card` | Fond blanc, ombre legere, border-radius 8px |
| `.card-header` | Bordure basse, padding, font-weight 600 |
| `.card-body` | Padding standard |
| `.card-footer` | Bordure haute, alignement droite |
| `.progress-bar` | Conteneur gris 8px, border-radius plein |
| `.progress-bar-fill` | Gradient bleu vers vert, transition 0.5s |
| `.progress-circle` | SVG circulaire stroke-dasharray/offset |
| `.badge-success` | Fond vert clair, texte vert fonce |
| `.badge-error` | Fond rouge clair, texte rouge fonce |
| `.badge-warning` | Fond ocre clair, texte ocre |
| `.badge-info` | Fond bleu clair, texte bleu |
| `.badge-locked` | Fond gris clair, texte gris |
| `.alert-success` | Bloc vert, bordure gauche 4px |
| `.alert-error` | Bloc rouge, bordure gauche 4px |
| `.modal-overlay` | Fixed, fond noir 50%, z-index 1000 |
| `.modal` | Centre, fond blanc, max-width 600px, ombre |
| `.table` | border-collapse, th gris leger, padding |
| `.form-group` | Margin-bottom, label + input empiles |
| `.form-input` | Width 100%, border, focus accent |
| `.form-select` | Base input + fleche personnalisee |
| `.form-error` | Texte rouge 0.875rem |
| `.sidebar-nav-item` | Flex icone SVG + texte, hover blanc 10% |
| `.sidebar-nav-item.active` | Fond blanc 15%, bordure gauche accent |

### 3.5 Etats visuels des lecons

| Etat | Badge | Icône SVG | Couleur fond |
|------|-------|-----------|--------------|
| Non commencee | `.badge-locked` | `lock.svg` | Gris clair |
| En cours | `.badge-info` | `file-text.svg` | Bleu clair |
| Validee | `.badge-success` | `check-circle.svg` | Vert clair |
| Verrouillee | `.badge-locked` | `lock.svg` | Gris, pas de lien |

### 3.6 Etats du bouton evaluation

| Etat | Classe | Texte | Condition |
|------|--------|-------|-----------|
| Verrouille | `.btn-disabled` | "Consultez la lecon d'abord" | Lecon non marquee lue |
| Pret | `.btn-primary` | "Passer l'evaluation" | Lecon marquee lue |
| Reussi | `.btn-success` | "Validee (XX%)" | Score >= seuil |
| Echoue | `.btn-danger` | "Retenter (Score: XX%)" | Score < seuil, tentatives restantes |
| Epouse | `.btn-disabled` | "Tentatives epuisees" | tentatives >= max ET non valide |

---

## 4. BASE DE DONNEES -- SCHEMA POSTGRESQL (NEON)

### 4.1 Tables (9 tables)

```sql
-- Types personnalises PostgreSQL
DO $$ BEGIN
    CREATE TYPE role_utilisateur AS ENUM ('etudiant', 'enseignant', 'promoteur');
EXCEPTION WHEN duplicate_object THEN null; END $$;
DO $$ BEGIN
    CREATE TYPE type_contenu_lecon AS ENUM ('pdf', 'video');
EXCEPTION WHEN duplicate_object THEN null; END $$;

-- 1. UTILISATEURS
CREATE TABLE utilisateurs (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    nom             VARCHAR(100) NOT NULL,
    prenom          VARCHAR(100) NOT NULL,
    email           VARCHAR(150) UNIQUE NOT NULL,
    mot_de_passe    VARCHAR(255) NOT NULL,
    role            ENUM('etudiant', 'enseignant', 'promoteur') NOT NULL DEFAULT 'etudiant',
    actif           TINYINT(1) NOT NULL DEFAULT 1,
    date_creation   TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. MODULES (Promoteur)
CREATE TABLE modules (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    titre           VARCHAR(150) NOT NULL,
    description     TEXT,
    promoteur_id    INT NOT NULL,
    date_creation   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (promoteur_id) REFERENCES utilisateurs(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. COURS (Enseignant)
CREATE TABLE cours (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    titre           VARCHAR(150) NOT NULL,
    description     TEXT,
    enseignant_id   INT NOT NULL,
    module_id       INT NULL,
    visible         TINYINT(1) NOT NULL DEFAULT 1,
    date_creation   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (enseignant_id) REFERENCES utilisateurs(id) ON DELETE CASCADE,
    FOREIGN KEY (module_id) REFERENCES modules(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. LECONS
CREATE TABLE lecons (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    cours_id        INT NOT NULL,
    titre           VARCHAR(150) NOT NULL,
    description     TEXT,
    type_contenu    ENUM('pdf', 'video') NOT NULL,
    url_contenu     VARCHAR(255) NOT NULL,
    duree_estimate  INT DEFAULT NULL,
    ordre           INT NOT NULL DEFAULT 0,
    date_creation   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (cours_id) REFERENCES cours(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. EVALUATIONS (1-to-1 avec lecon)
CREATE TABLE evaluations (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    lecon_id        INT UNIQUE NOT NULL,
    titre           VARCHAR(150) NOT NULL,
    note_de_passage INT NOT NULL DEFAULT 70,
    duree_limite    INT DEFAULT NULL,
    tentatives_max  INT NOT NULL DEFAULT 3,
    date_creation   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (lecon_id) REFERENCES lecons(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. QUESTIONS
CREATE TABLE questions (
    id                  INT AUTO_INCREMENT PRIMARY KEY,
    evaluation_id       INT NOT NULL,
    question_text       TEXT NOT NULL,
    options_json        JSON NOT NULL,
    reponse_correcte    VARCHAR(10) NOT NULL,
    ordre               INT NOT NULL DEFAULT 0,
    FOREIGN KEY (evaluation_id) REFERENCES evaluations(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. INSCRIPTIONS
CREATE TABLE inscriptions (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    etudiant_id     INT NOT NULL,
    cours_id        INT NOT NULL,
    date_inscription TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_inscription (etudiant_id, cours_id),
    FOREIGN KEY (etudiant_id) REFERENCES utilisateurs(id) ON DELETE CASCADE,
    FOREIGN KEY (cours_id) REFERENCES cours(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8. PROGRESSIONS
CREATE TABLE progressions (
    id                  INT AUTO_INCREMENT PRIMARY KEY,
    etudiant_id         INT NOT NULL,
    lecon_id            INT NOT NULL,
    evaluation_id       INT NOT NULL,
    note_obtenue        INT NOT NULL DEFAULT 0,
    valide              TINYINT(1) NOT NULL DEFAULT 0,
    nb_tentatives       INT NOT NULL DEFAULT 1,
    lecon_consultee     TINYINT(1) NOT NULL DEFAULT 0,
    derniere_tentative  TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_progression (etudiant_id, lecon_id),
    FOREIGN KEY (etudiant_id) REFERENCES utilisateurs(id) ON DELETE CASCADE,
    FOREIGN KEY (lecon_id) REFERENCES lecons(id) ON DELETE CASCADE,
    FOREIGN KEY (evaluation_id) REFERENCES evaluations(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 9. CERTIFICATS
CREATE TABLE certificats (
    id                  INT AUTO_INCREMENT PRIMARY KEY,
    etudiant_id         INT NOT NULL,
    module_id           INT NOT NULL,
    code_verification   VARCHAR(64) UNIQUE NOT NULL,
    date_delivrance     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (etudiant_id) REFERENCES utilisateurs(id) ON DELETE CASCADE,
    FOREIGN KEY (module_id) REFERENCES modules(id) ON DELETE CASCADE,
    UNIQUE KEY unique_certificat (etudiant_id, module_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
```

### 4.2 Index de performance

```sql
CREATE INDEX idx_progressions_etudiant ON progressions(etudiant_id);
CREATE INDEX idx_progressions_lecon ON progressions(lecon_id);
CREATE INDEX idx_progressions_valide ON progressions(etudiant_id, valide);
CREATE INDEX idx_cours_module ON cours(module_id);
CREATE INDEX idx_cours_enseignant ON cours(enseignant_id);
CREATE INDEX idx_lecons_cours ON lecons(cours_id, ordre);
CREATE INDEX idx_questions_evaluation ON questions(evaluation_id, ordre);
CREATE INDEX idx_inscriptions_etudiant ON inscriptions(etudiant_id);
```

### 4.3 Champ supplementaire : lecon_consultee

Le champ `progressions.lecon_consultee` (TINYINT, defaut 0) est mis a 1 par `actions/etudiant/lecon_viewed.php` quand l'etudiant marque la lecon comme lue. Ce champ controle le deverrouillage du bouton "Passer l'evaluation".

### 4.4 Donnees de demonstration

Le fichier `sql/seed.php` genere les hashs BCrypt et insere :

| Role | Email | Mot de passe |
|------|-------|-------------|
| Promoteur | promoteur@lms.cm | promoteur123 |
| Enseignant | jean.dupont@lms.cm | enseignant123 |
| Etudiant | paul.kamga@lms.cm | etudiant123 |

Plus : 1 module, 1 cours, 3 lecons, 3 evaluations, 9 questions QCM.

---

## 5. FLUX PAR ACTEUR -- LOGIQUE METIER

### 5.1 Enseignant

```
Connexion -> Dashboard (stats : cours, lecons, inscrits, taux reussite)
    |
    |-- Creer un cours (titre, description, module optionnel)
    |       |-- Ajouter des lecons (upload PDF/video, ordre auto)
    |       |       `-- Creer evaluation (questions QCM, seuil, tentatives max)
    |       `-- Publier le cours (visible = 1)
    |
    |-- Modifier cours / lecons / evaluations / questions
    |
    |-- Statistiques : tableau etudiants par cours (progression, score moyen)
    |
    `-- Deconnexion
```

### 5.2 Etudiant

```
Connexion -> Dashboard (stats + cours en cours + disponibles)
    |
    |-- Catalogue cours -> S'inscrire -> Detail cours (plan lecons + progression)
    |
    |-- Acceder lecon (video ou PDF via file_serve.php)
    |       |-- Marquer comme lue (AJAX -> lecon_consultee = 1)
    |       |-- Deverrouiller bouton "Passer l'evaluation"
    |       |-- Ouvrir modale quiz (AJAX -> quiz_modal.php)
    |       |-- Soumettre reponses (AJAX -> submit_quiz.php)
    |       |       |-- Score >= seuil : valide, progression maj, lecon suivante deverrouillee
    |       |       |       `-- Si cours 100% + module complet -> certificat auto
    |       |       `-- Score < seuil : echec, retenter si tentatives restantes
    |       `-- Lecon suivante deverrouillee (ordre + validation precedente)
    |
    |-- Progression globale (detail par cours)
    |
    |-- Certificats : liste obtenus + vue imprimable + verification publique
    |
    `-- Deconnexion
```

### 5.3 Promoteur

```
Connexion -> Dashboard (KPIs : modules, cours, utilisateurs, certificats)
    |
    |-- Creer / modifier / supprimer des modules
    |       `-- Associer / retirer des cours a un module
    |
    |-- Supervision : enseignants + cours, etudiants + progressions, certificats
    |
    `-- Deconnexion
```

---

## 6. LOGIQUES DE CALCUL

### 6.1 Score evaluation

```
Score(%) = round((bonnes_reponses / total_questions) * 100)
Validation : Score >= evaluations.note_de_passage
```

### 6.2 Progression cours

```
Progression(%) = (lecons_validees / total_lecons_du_cours) * 100

Lecon validee : progressions.valide = 1
```

### 6.3 Validation module et certificat

```
Module valide SI TOUS les cours du module ont Progression = 100%

Alors :
    INSERT INTO certificats (etudiant_id, module_id, code_verification)
    code_verification = bin2hex(random_bytes(32))
    Contrainte UNIQUE (etudiant_id, module_id) -> pas de doublon
```

### 6.4 Tentatives

```
Avant soumission :
    SELECT nb_tentatives, valide FROM progressions WHERE etudiant_id = ? AND lecon_id = ?

Si valide = 1 -> deja reussi, pas de nouvelle tentative necessaire
Si nb_tentatives >= evaluations.tentatives_max ET valide = 0 -> quiz verrouille

Apres soumission :
    UPSERT progressions SET nb_tentatives = nb_tentatives + 1
    note_obtenue = GREATEST(note_obtenue, nouveau_score)
    valide = GREATEST(valide, nouveau_valide)
```

### 6.5 Deverrouillage sequentiel

```
Lecon N est accessible si :
    - N = 1 (premiere lecon, toujours accessible)
    - OU progressions.valide = 1 pour la lecon N-1 du meme cours
```

---

## 7. REGLES DE SECURITE

| Menace | Protection |
|--------|-----------|
| Injection SQL | Requites preparees PDO. Aucune concatenation. |
| XSS | `htmlspecialchars($var, ENT_QUOTES, 'UTF-8')` sur toute sortie |
| CSRF | Token dans chaque formulaire, verifie avec `hash_equals()` |
| Mots de passe | `password_hash(PASSWORD_BCRYPT)` + `password_verify()` |
| Upload | Verification MIME avec `finfo_file()`, extension, taille max, renommage unique |
| Acces fichiers | `.htaccess` Deny from all + `file_serve.php` avec controle de droits |
| Autorisation | Verification role + propriete (enseignant) + inscription (etudiant) |
| Session | `session_regenerate_id(true)` apres login, cookie HttpOnly + SameSite |
| Headers | X-Content-Type-Options, X-Frame-Options, X-XSS-Protection, Referrer-Policy |

### 7.1 Upload de fichiers

```
PDF  : MIME application/pdf, max 20 Mo, extension .pdf
Video: MIME video/mp4 ou video/webm, max 500 Mo

Verification : finfo_file() (pas $_FILES['type'])
Nommage : {type}_{cours_id}_{timestamp}.{ext}
Stockage : public/uploads/{type}/
Acces : uniquement via actions/file_serve.php
```

### 7.2 Token CSRF

```php
// Generation
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Verification
if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
    http_response_code(403);
    exit('Erreur de securite CSRF');
}
```

### 7.3 file_serve.php -- Logique d'acces

```
1. Verification session (require_login)
2. Recuperation de la lecon en BDD (avec cours_id)
3. Verification autorisation :
   - Enseignant : proprietaire du cours parent
   - Etudiant : inscrit au cours parent
   - Promoteur : acces global
4. Si URL Cloudinary : redirection HTTP vers l'URL CDN
5. Sinon (fallback local) : envoi fichier avec headers Content-Type, Content-Disposition inline
```

---

## 8. CONVENTIONS DE CODE

### 8.1 PHP

- Encodage UTF-8 sans BOM
- Indentation : 4 espaces
- Variables : `snake_case`
- Classes : `PascalCase`
- Fichiers : `snake_case.php`
- Chaque action commence par : require session, require database, verification CSRF, verification role
- Reponses AJAX : `header('Content-Type: application/json')` + `echo json_encode()` + `exit`

### 8.2 HTML / CSS

- HTML5 semantique : `<header>`, `<main>`, `<nav>`, `<section>`, `<article>`
- Classes CSS : `kebab-case`
- Variables CSS pour toute couleur, espacement, taille
- Pas de style inline
- Responsive mobile-first : breakpoints 768px et 1024px

### 8.3 JavaScript

- Vanilla JS uniquement (pas de jQuery, pas de framework)
- `const` par defaut, `let` si reassignation
- `fetch()` pour les requetes AJAX
- Un fichier JS par domaine fonctionnel

---

## 9. ROUTAGE ET CONTROLE D'ACCES

### 9.1 Routes (index.php)

```
PUBLIQUES (sans authentification) :
    ?page=login                    -> Page connexion
    ?page=register                 -> Page inscription
    ?page=logout                   -> Deconnexion
    ?page=verifier-certificat      -> Verification publique certificat

ETUDIANT (role = etudiant) :
    ?page=etudiant/dashboard       -> Tableau de bord
    ?page=etudiant/catalogue       -> Catalogue cours disponibles
    ?page=etudiant/cours&id=X      -> Detail cours + plan lecons
    ?page=etudiant/lecon&id=X      -> Lecteur lecon (video/PDF + quiz)
    ?page=etudiant/progression     -> Progression globale detaillee
    ?page=etudiant/certificats     -> Liste certificats
    ?page=etudiant/certificat&id=X -> Vue imprimable certificat

ENSEIGNANT (role = enseignant) :
    ?page=enseignant/dashboard     -> Tableau de bord
    ?page=enseignant/cours         -> Liste + creation cours
    ?page=enseignant/cours/edit&id=X -> Edition : lecons + evaluations
    ?page=enseignant/statistiques  -> Stats reussite etudiants

PROMOTEUR (role = promoteur) :
    ?page=promoteur/dashboard      -> KPIs globaux
    ?page=promoteur/modules        -> Gestion modules
    ?page=promoteur/module/edit&id=X -> Edition module + cours associes
    ?page=promoteur/supervision    -> Supervision utilisateurs
```

### 9.2 Actions (traitement POST, redirection)

```
AUTH :
    actions/auth/login.php         -> Connexion (POST)
    actions/auth/register.php      -> Inscription (POST)
    actions/auth/logout.php        -> Deconnexion (GET)

ENSEIGNANT :
    actions/enseignant/cours_create.php      -> Creation cours (POST)
    actions/enseignant/cours_update.php      -> Modification cours (POST)
    actions/enseignant/cours_delete.php      -> Suppression cours (POST)
    actions/enseignant/lecon_create.php      -> Ajout lecon (POST + file)
    actions/enseignant/lecon_update.php      -> Modif lecon (POST + file)
    actions/enseignant/lecon_delete.php      -> Suppr lecon (POST)
    actions/enseignant/evaluation_create.php -> Creation eval + questions (POST)
    actions/enseignant/evaluation_update.php -> Modif eval + questions (POST)

ETUDIANT :
    actions/etudiant/cours_inscrire.php      -> Inscription cours (POST)
    actions/etudiant/lecon_viewed.php        -> Marquage lecon (AJAX POST -> JSON)
    actions/etudiant/submit_quiz.php         -> Soumission quiz (AJAX POST -> JSON)
    actions/etudiant/certificat_download.php -> Vue certificat (GET)
    actions/etudiant/file_serve.php          -> Servir fichier (GET + controle acces)

PROMOTEUR :
    actions/promoteur/module_create.php      -> Creation module (POST)
    actions/promoteur/module_update.php      -> Modification module (POST)
    actions/promoteur/module_delete.php      -> Suppression module (POST)
    actions/promoteur/module_assign.php      -> Associer cours (POST)
    actions/promoteur/promoteur_stats.php    -> Stats globales (AJAX GET -> JSON)
```

### 9.3 Middleware d'autorisation

Chaque vue protegee verifie en debut de fichier :

```php
require_once __DIR__ . '/../../config/session.php';
require_login();                    // Redirige si non connecte
require_role('etudiant');          // 403 si mauvais role

// Pour enseignant : verification propriete
$cours = Cours::findById($cours_id);
if ($cours['enseignant_id'] !== $_SESSION['user_id']) {
    http_response_code(403);
    exit('Acces refuse');
}

// Pour etudiant : verification inscription
$stmt = $pdo->prepare("SELECT id FROM inscriptions WHERE etudiant_id = ? AND cours_id = ?");
$stmt->execute([$_SESSION['user_id'], $cours_id]);
if (!$stmt->fetch()) {
    http_response_code(403);
    exit('Non inscrit a ce cours');
}
```

---

## 10. PLAN D'EXECUTION

```
PHASE 1 -- FONDATIONS (Infrastructure)
|   [1] config/database.php (PDO, variables d'env)
|   [2] config/constants.php (constantes applicatives)
|   [3] config/session.php (session, auth helpers, CSRF, flash, sanitize)
|   [4] sql/schema.sql (9 tables + 8 index)
|   [5] sql/seed.php (3 comptes + donnees demo)
|   [6] CSS : reset + variables + base + components + layout + pages
|   [7] Layouts : header + sidebar + footer
|   [8] Icones SVG (30+ icones dans public/img/icons/)
|   [9] index.php (routeur principal)
|   [10] .htaccess (racine + uploads)
|   [11] Dockerfile + docker-compose.yml + docker/apache.conf
|   [12] .env.example + .gitignore
|
PHASE 2 -- AUTHENTIFICATION
|   [13] views/auth/login.php + register.php
|   [14] actions/auth/login.php + register.php + logout.php
|   [15] models/Utilisateur.php (authenticate, create, findByEmail)
|
PHASE 3 -- ENSEIGNANT
|   [16] models/Cours.php + Lecon.php + Evaluation.php
|   [17] views/enseignant/dashboard.php + cours_gestion.php
|   [18] views/enseignant/cours_edit.php + statistiques.php
|   [19] actions/enseignant/cours_create.php + cours_update.php + cours_delete.php
|   [20] actions/enseignant/lecon_create.php + lecon_update.php + lecon_delete.php
|   [21] actions/enseignant/evaluation_create.php + evaluation_update.php
|
PHASE 4 -- ETUDIANT
|   [22] models/Progression.php + Certificat.php
|   [23] views/etudiant/dashboard.php + cours_liste.php + cours_detail.php
|   [24] views/etudiant/lecon_player.php + quiz_modal.php
|   [25] views/etudiant/progression.php + certificats.php + certificat.php + certificat_verifier.php
|   [26] public/js/player.js (lecteur video HTML5)
|   [27] public/js/quiz.js (modale quiz AJAX)
|   [28] public/js/progression.js (mise a jour barres + badges)
|   [29] public/js/app.js (init, sidebar, flash, confirm)
|   [30] actions/etudiant/cours_inscrire.php + lecon_viewed.php
|   [31] actions/etudiant/submit_quiz.php (calcul + certificat auto)
|   [32] actions/etudiant/file_serve.php (servir PDF/video securise)
|   [33] actions/etudiant/certificat_download.php
|
PHASE 5 -- PROMOTEUR
|   [34] models/Module.php
|   [35] views/promoteur/dashboard.php + modules_gestion.php + module_edit.php + supervision.php
|   [36] actions/promoteur/module_create.php + module_update.php + module_delete.php + module_assign.php
|   [37] actions/promoteur/promoteur_stats.php
|
PHASE 6 -- FINITION
    [38] Tests bout en bout (4 parcours complets)
    [39] Audit securite (SQL, XSS, CSRF, upload, autorisation)
    [40] Tests responsive (desktop, tablette, mobile)
    [41] Nettoyage code + README.md
    [42] Docker build + test local
    [43] Deploiement Render + tests production
```

---

## 11. VARIABLES D'ENVIRONNEMENT

### 11.1 config/database.php (lecture env)

```php
<?php
$dsn = getenv('DATABASE_URL') ?: null;

if ($dsn) {
    // Neon / Production : DATABASE_URL fournie directement
    // Format : postgresql://user:pass@host:port/db?sslmode=require
} else {
    // Developpement local
    $host = getenv('DB_HOST') ?: 'localhost';
    $port = getenv('DB_PORT') ?: '5432';
    $name = getenv('DB_NAME') ?: 'lms_cameroun';
    $user = getenv('DB_USER') ?: 'postgres';
    $pass = getenv('DB_PASS') ?: '';
    $dsn = "pgsql:host={$host};port={$port};dbname={$name}";
}

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, null, null, $options);
    if (!getenv('DATABASE_URL')) {
        $pdo = new PDO($dsn, $user, $pass, $options);
    }
} catch (PDOException $e) {
    http_response_code(500);
    exit('Erreur de connexion a la base de donnees.');
}
```

### 11.2 config/constants.php

```php
<?php
define('APP_NAME', getenv('APP_NAME') ?: 'LMS Cameroun');
define('APP_ENV', getenv('APP_ENV') ?: 'development');
define('APP_BASE_URL', getenv('APP_BASE_URL') ?: '');

define('UPLOAD_PATH_PDF', __DIR__ . '/../public/uploads/pdf/');
define('UPLOAD_PATH_VIDEO', __DIR__ . '/../public/uploads/video/');

define('MAX_FILE_SIZE_PDF', 20 * 1024 * 1024);
define('MAX_FILE_SIZE_VIDEO', 500 * 1024 * 1024);

define('DEFAULT_NOTE_PASSAGE', 70);
define('DEFAULT_TENTATIVES_MAX', 3);

define('ROLES', ['etudiant', 'enseignant', 'promoteur']);
define('MIME_PDF', ['application/pdf']);
define('MIME_VIDEO', ['video/mp4', 'video/webm']);

define('SESSION_TIMEOUT', (int)(getenv('SESSION_TIMEOUT') ?: 3600));
define('SESSION_NAME', 'LMS_SESSION');
```

### 11.3 .env.example

```env
# Neon PostgreSQL (Production)
DATABASE_URL=postgresql://user:password@ep-xxx.neon.tech/neondb?sslmode=require

# PostgreSQL (Local Docker)
DB_HOST=db
DB_PORT=5432
DB_NAME=lms_cameroun
DB_USER=lms_user
DB_PASS=lms_password

# Cloudinary
CLOUDINARY_CLOUD_NAME=votre_cloud_name
CLOUDINARY_API_KEY=votre_api_key
CLOUDINARY_API_SECRET=votre_api_secret

# Application
APP_NAME=LMS Cameroun
APP_ENV=development
APP_BASE_URL=http://localhost:8080
SESSION_TIMEOUT=3600
```

---

## 12. REPONSE JSON -- SOUMISSION QUIZ

Format complet retourne par `actions/etudiant/submit_quiz.php` :

```json
{
    "success": true,
    "score": 80,
    "seuil": 70,
    "valide": true,
    "bonnes_reponses": 4,
    "total_questions": 5,
    "tentatives_utilisees": 2,
    "tentatives_max": 3,
    "nouvelle_progression": 66.67,
    "lecon_validee": true,
    "certificat_genere": false,
    "corrections": [
        {"question_id": 1, "correct": true, "reponse_correcte": "A"},
        {"question_id": 2, "correct": false, "reponse_correcte": "B", "reponse_donnee": "C"}
    ]
}
```

Si `certificat_genere` est `true`, ajouter :
```json
{
    "certificat_genere": true,
    "certificat_id": 5,
    "module_titre": "Developpement Web",
    "code_verification": "a1b2c3..."
}
```
