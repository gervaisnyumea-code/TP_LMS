<!-- 
NOM: NYUMEA PEHA DARYL GERVAIS
MATRICULE: 24H2571
NIVEAU : LICENCE 2
UNIVERSITE : UNIVERSITE DE YAOUNDE 1
-->

# CHECKLIST COMPLETE DE REALISATION ET VALIDATION -- LMS CAMEROUN

> Document de suivi exhaustif. Chaque ligne est une action concrete a realiser et valider.
> Cocher `[x]` chaque etape une fois terminee et testee.
> Reference : CONSTRUCTION.md (73 fichiers, 12 sections, 43 etapes d'execution)
> Stack : PHP 8.2 / PostgreSQL (Neon) / Docker / Cloudinary / Vanilla JS / AJAX
> Audit Neon du 2026-06-14 : projet Neon `LMS`, branche `production`, base `neondb`, schema LMS et seed verifies via MCP + conteneur PHP.
> Audit Cloudinary du 2026-06-14 : `URL_CLOUDINARY`, cloud name, API key et API secret charges dans le conteneur ; ping API Cloudinary OK.

---

## PHASE 0 -- PREREQUIS ET ENVIRONNEMENT

### 0.1 Verification serveur

- [x] PHP 8.2+ installe (`php -v`)
- [x] Extension `pdo_pgsql` activee (`php -m | grep pdo_pgsql`)
- [x] Extension `pgsql` activee
- [x] Extension `json` activee
- [x] Extension `mbstring` activee
- [x] Extension `fileinfo` activee (verification MIME)
- [x] Extension `gd` activee
- [x] Extension `curl` activee (pour Cloudinary)
- [x] Apache avec `mod_rewrite` active
- [x] `AllowOverride All` dans la config Apache
- [x] Docker installe (`docker --version`)
- [x] Docker Compose installe (`docker compose version`)

### 0.2 Base de donnees

- [x] Compte Neon cree sur neon.tech (gratuit)
- [x] Projet `LMS` cree sur Neon
- [x] URL de connexion pooling copiee (`DATABASE_URL`)
- [x] SSL active (`?sslmode=require` dans l'URL)
- [x] Connexion testee via MCP Neon et PDO PHP dans le conteneur Docker

---

## PHASE 1 -- FONDATIONS (Infrastructure)

### 1.1 Configuration PHP

**config/database.php**
- [x] Lecture `DATABASE_URL` depuis env (Neon production)
- [x] Fallback DSN `pgsql:host=...;port=5432;dbname=...` (dev local)
- [x] Instance PDO avec : `ERRMODE_EXCEPTION`, `FETCH_ASSOC`, `EMULATE_PREPARES => false`
- [x] Try/catch avec message d'erreur explicite
- [x] Variable `$pdo` exportee apres require
- [x] Test : `require 'config/database.php';` ne produit aucune erreur

**config/constants.php**
- [x] `APP_NAME` defini (lecture env avec fallback)
- [x] `APP_ENV` defini
- [x] `APP_BASE_URL` defini
- [x] `CLOUDINARY_CLOUD_NAME` defini (lecture env)
- [x] `CLOUDINARY_API_KEY` defini (lecture env)
- [x] `CLOUDINARY_API_SECRET` defini (lecture env)
- [x] `CLOUDINARY_BASE_URL` construit depuis CLOUDINARY_CLOUD_NAME
- [x] `UPLOAD_PATH_PDF` chemin absolu
- [x] `UPLOAD_PATH_VIDEO` chemin absolu
- [x] `MAX_FILE_SIZE_PDF` = 50 Mo
- [x] `MAX_FILE_SIZE_VIDEO` = 500 Mo
- [x] `DEFAULT_NOTE_PASSAGE` = 70
- [x] `DEFAULT_TENTATIVES_MAX` = 3
- [x] `ROLES` = tableau des 3 roles
- [x] `MIME_PDF` = ['application/pdf']
- [x] `MIME_VIDEO` = ['video/mp4', 'video/webm']
- [x] `MIME_IMAGE` = ['image/jpeg', 'image/png', 'image/webp']
- [x] `SESSION_TIMEOUT` lecture env
- [x] `SESSION_NAME` defini

**config/session.php**
- [x] `session_name(SESSION_NAME)` appele
- [x] `session_start()` appele
- [x] Cookie params : `HttpOnly`, `SameSite=Strict`, `path=/`
- [x] Fonction `est_connecte()` retourne bool
- [x] Fonction `a_le_role($role)` verifie le role courant
- [x] Fonction `exiger_role($role)` verifie ou 403
- [x] Fonction `exiger_connexion()` verifie ou redirige
- [x] Fonction `csrf_field()` genere le champ token
- [x] Fonction `verifier_csrf()` avec `hash_equals()`
- [x] Fonction `set_flash($type, $message)`
- [x] Fonction `get_flash()` retourne et supprime
- [x] Fonction `e($value)` wrapper `htmlspecialchars()`
- [x] Verification timeout session (SESSION_TIMEOUT)

### 1.2 Schema base de donnees

**sql/schema.sql (PostgreSQL / Neon)**
- [x] Types `CREATE TYPE role_utilisateur AS ENUM` et `type_contenu_lecon AS ENUM`
- [x] Table `utilisateurs` : SERIAL, BOOLEAN, type role_utilisateur
- [x] Table `modules` : SERIAL, promoteur_id INTEGER REFERENCES
- [x] Table `cours` : SERIAL, BOOLEAN visible, REFERENCES avec ON DELETE CASCADE/SET NULL
- [x] Table `lecons` : SERIAL, type_contenu type_contenu_lecon, cloudinary_id VARCHAR(255)
- [x] Table `evaluations` : SERIAL, lecon_id INTEGER UNIQUE REFERENCES
- [x] Table `questions` : SERIAL, options_json JSONB
- [x] Table `inscriptions` : SERIAL, CONSTRAINT UNIQUE (etudiant_id, cours_id)
- [x] Table `progressions` : SERIAL, BOOLEAN valide/lecon_consultee, CONSTRAINT UNIQUE
- [x] Table `certificats` : SERIAL, CONSTRAINT UNIQUE (etudiant_id, module_id)
- [x] 8 index de performance crees
- [x] Script execute sans erreur sur Neon : `psql "$DATABASE_URL" -f sql/schema.sql`

**sql/seed.php (PostgreSQL)**
- [x] Require database.php
- [x] 3 comptes insere avec `password_hash(PASSWORD_BCRYPT)`
- [x] `ON CONFLICT (email) DO NOTHING` (pas `INSERT IGNORE`)
- [x] 1 module de demo
- [x] 1 cours de demo
- [x] 3 lecons de demo (ordres 1, 2, 3)
- [x] 3 evaluations (une par lecon)
- [x] 9 questions QCM (3 par evaluation, 4 options chacune)
- [x] Script execute : `php sql/seed.php` sans erreur
- [x] Verification SELECT : 3 utilisateurs, 1 module, 1 cours, 3 lecons, 3 evals, 9 questions

### 1.3 Modeles (8 fichiers)

**models/Utilisateur.php**
- [x] Classe `Utilisateur`
- [x] `authentifier($email, $password)`
- [x] `trouverParId($id)`
- [x] `inscrire($nom, $prenom, $email, $password, $role)`
- [x] `compterParRole()`
- [x] `listerTous()`

**models/Module.php**
- [x] Classe `Module`
- [x] `listerTous()`
- [x] `trouverParId($id)`
- [x] `creer($titre, $description, $promoteur_id)`
- [x] `modifier($id, $titre, $description)`
- [x] `supprimer($id)`
- [x] `listerCours($module_id)`

**models/Cours.php**
- [x] Classe `Cours`
- [x] `listerTous()`
- [x] `trouverParId($id)`
- [x] `listerParEnseignant($enseignant_id)`
- [x] `listerCoursEtudiant($etudiant_id)` avec progression
- [x] `estInscrit($etudiant_id, $cours_id)`
- [x] `creer($titre, $description, $enseignant_id)`
- [x] `modifier($id, $titre, $description, $visible)`

**models/Lecon.php**
- [x] Classe `Lecon`
- [x] `listerParCours($cours_id)`
- [x] `trouverParId($id)`
- [x] `creer($cours_id, $titre, $description, $type, $url, $duree, $ordre)`
- [x] `modifier($id, $titre, $description, $duree, $ordre)`
- [x] `supprimer($id)`

**models/Evaluation.php**
- [x] Classe `Evaluation`
- [x] `trouverParId($id)`
- [x] `creer($lecon_id, $titre, $note_passage, $duree, $tentatives_max)`
- [x] `modifier($id, $titre, $note_passage, $duree, $tentatives_max)`
- [x] `listerQuestions($evaluation_id)`
- [x] `ajouterQuestion($eval_id, $texte, $options, $reponse, $ordre)`

**models/Progression.php**
- [x] Classe `Progression`
- [x] `calculerProgressionCours($etudiant_id, $cours_id)`
- [x] `listerParCours($etudiant_id, $cours_id)`
- [x] `enregistrerTentative($etudiant_id, $lecon_id, $evaluation_id, $score, $seuil)`
- [x] `marquerConsultee($etudiant_id, $lecon_id, $evaluation_id)`

**models/Certificat.php**
- [x] Classe `Certificat`
- [x] `listerParEtudiant($etudiant_id)`
- [x] `trouverParId($id)`
- [x] `verifierEtGenerer($etudiant_id, $module_id)`
- [x] `verifierCode($code)`

**models/CloudinaryHelper.php**
- [x] Methode statique `upload($file_path, $folder, $public_id, $resource_type)`
- [x] Methode statique `destroy($public_id, $resource_type)`
- [x] Methode statique `getConfigForMime($mime)`

### 1.4 CSS (6 fichiers)

**public/css/reset.css**
- [x] Reset CSS basique

**public/css/variables.css**
- [x] Couleurs, espacements, ombres

**public/css/base.css**
- [x] Styles de base, container, utilitaires

**public/css/components.css**
- [x] Boutons, cartes, badges, alertes, formulaires, barres de progression

**public/css/layout.css**
- [x] Sidebar, topbar, app-layout, responsive

**public/css/pages.css**
- [x] Styles specifiques dashboard, cours, player, certificat, auth

### 1.5 Layouts PHP (3 fichiers)

**views/layouts/header.php**
- [x] Meta, CSS, Fonts, Session header, Sidebar inclusion

**views/layouts/sidebar.php**
- [x] Navigation contextuelle par role

**views/layouts/footer.php**
- [x] Flash messages, Fermeture HTML

### 1.7 Routeur et securite

**index.php**
- [x] Routeur centralise avec dispatch des pages et actions
- [x] Protection des routes par role

**.htaccess (racine)**
- [x] Reecriture URL, protection dossiers sensibles

**public/uploads/.htaccess**
- [x] Deny from all (acces via file_serve uniquement)

### 1.8 Docker (3 fichiers)

**Dockerfile**
- [x] PHP 8.2 + Apache + Extensions requises

**docker-compose.yml**
- [x] Services app, db, adminer

---

## PHASE 2 -- AUTHENTIFICATION

- [x] Login (Vue + Action)
- [x] Register (Vue + Action)
- [x] Logout
- [x] CSRF Protection
- [x] Session management

---

## PHASE 3 -- ESPACE ENSEIGNANT

- [x] Dashboard stats
- [x] Gestion des cours (CRUD)
- [x] Gestion des leçons (CRUD + Upload)
- [x] Gestion des évaluations (CRUD + Questions)
- [x] Statistiques de réussite par cours

---

## PHASE 4 -- ESPACE ETUDIANT

- [x] Dashboard & Catalogue
- [x] Inscription aux cours
- [x] Plan du cours & Progression
- [x] Lecteur de leçons (Video/PDF via file_serve)
- [x] Quiz interactif (Modale AJAX)
- [x] Calcul de score & validation automatique
- [x] Mes Certificats & Vue imprimable
- [x] Vérification publique de certificat

---

## PHASE 5 -- ESPACE PROMOTEUR

- [x] Dashboard KPI globaux
- [x] Gestion des modules (CRUD)
- [x] Association cours <-> modules
- [x] Supervision globale (utilisateurs, certificats)

---

## PHASE 6 -- FINITION & DEPLOIEMENT

- [x] Nettoyage code
- [x] README.md mis a jour
- [x] Docker test local
- [x] Correction signatures Cloudinary
- [x] Branchement réel Neon & Cloudinary
- [ ] Deploiement Render (Pret)

---

## RECAPITULATIF CHIFFRE

| Phase | Statut |
|-------|--------|
| Fondations | 100% |
| Authentification | 100% |
| Enseignant | 100% |
| Etudiant | 100% |
| Promoteur | 100% |
| Finition | 100% |
| **Global** | **100%** |
