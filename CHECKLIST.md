# CHECKLIST COMPLETE DE REALISATION ET VALIDATION -- LMS CAMEROUN

> Document de suivi exhaustif. Chaque ligne est une action concrete a realiser et valider.
> Cocher `[x]` chaque etape une fois terminee et testee.
> Reference : CONSTRUCTION.md (73 fichiers, 12 sections, 43 etapes d'execution)
> Stack : PHP 8.2 / PostgreSQL (Neon) / Docker / Cloudinary / Vanilla JS / AJAX

---

## PHASE 0 -- PREREQUIS ET ENVIRONNEMENT

### 0.1 Verification serveur

- [ ] PHP 8.2+ installe (`php -v`)
- [ ] Extension `pdo_pgsql` activee (`php -m | grep pdo_pgsql`)
- [ ] Extension `pgsql` activee
- [ ] Extension `json` activee
- [ ] Extension `mbstring` activee
- [ ] Extension `fileinfo` activee (verification MIME)
- [ ] Extension `gd` activee
- [ ] Extension `curl` activee (pour Cloudinary)
- [ ] Apache avec `mod_rewrite` active
- [x] `AllowOverride All` dans la config Apache
- [x] Docker installe (`docker --version`)
- [x] Docker Compose installe (`docker compose version`)

### 0.2 Base de donnees

- [ ] Compte Neon cree sur neon.tech (gratuit)
- [ ] Projet `lms-cameroun` cree sur Neon
- [ ] URL de connexion pooling copiee (`DATABASE_URL`)
- [ ] SSL active (`?sslmode=require` dans l'URL)
- [ ] Connexion testee : `psql "$DATABASE_URL" -c "SELECT 1;"`

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
- [ ] `APP_NAME` defini (lecture env avec fallback)
- [ ] `APP_ENV` defini
- [ ] `APP_BASE_URL` defini
- [x] `CLOUDINARY_CLOUD_NAME` defini (lecture env)
- [ ] `CLOUDINARY_API_KEY` defini (lecture env)
- [ ] `CLOUDINARY_API_SECRET` defini (lecture env)
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
- [ ] `SESSION_NAME` defini

**config/session.php**
- [x] `session_name(SESSION_NAME)` appele
- [x] `session_start()` appele
- [x] Cookie params : `HttpOnly`, `SameSite=Strict`, `path=/`
- [x] Fonction `is_logged_in()` retourne bool
- [x] Fonction `get_current_user_id()` retourne ID ou redirige
- [x] Fonction `get_current_role()` retourne role
- [x] Fonction `require_role($role)` verifie ou 403
- [x] Fonction `require_login()` verifie ou redirige
- [x] Fonction `csrf_token()` genere/retourne token
- [x] Fonction `verify_csrf($token)` avec `hash_equals()`
- [x] Fonction `set_flash($type, $message)`
- [x] Fonction `get_flash()` retourne et supprime
- [x] Fonction `sanitize($value)` wrapper `htmlspecialchars()`
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
- [ ] 3 evaluations (une par lecon)
- [ ] 9 questions QCM (3 par evaluation, 4 options chacune)
- [ ] Script execute : `php sql/seed.php` sans erreur
- [ ] Verification SELECT : 3 utilisateurs, 1 module, 1 cours, 3 lecons, 3 evals, 9 questions

### 1.3 Modeles (7 fichiers)

**models/Utilisateur.php**
- [ ] Classe `Utilisateur` avec propriete statique `$pdo`
- [ ] `findByEmail($email)` requete preparee, retourne array|false
- [ ] `findById($id)` requete preparee
- [ ] `create($nom, $prenom, $email, $password, $role)` INSERT avec `password_hash()`
- [ ] `authenticate($email, $password)` SELECT + `password_verify()`, retourne user|false
- [ ] `findByRole($role)` liste utilisateurs
- [ ] `update($id, $data)` UPDATE dynamique
- [ ] `delete($id)` DELETE
- [ ] Aucune sortie de `mot_de_passe` dans les methodes de lecture

**models/Module.php**
- [ ] Classe `Module`
- [ ] `findAll()` avec JOIN promoteur
- [ ] `findById($id)`
- [ ] `create($titre, $description, $promoteur_id)`
- [ ] `update($id, $titre, $description)`
- [ ] `delete($id)`
- [ ] `getCours($module_id)` cours associes
- [ ] `isValide($etudiant_id, $module_id)` verifie tous cours a 100%

**models/Cours.php**
- [ ] Classe `Cours`
- [ ] `findAll()` avec JOIN enseignant et module
- [ ] `findById($id)`
- [ ] `findByEnseignant($enseignant_id)`
- [ ] `findByModule($module_id)`
- [ ] `findByInscriptions($etudiant_id)` cours inscrits par l'etudiant
- [ ] `findAvailable($etudiant_id)` cours visibles non inscrits
- [ ] `create($titre, $description, $enseignant_id, $module_id)`
- [ ] `update($id, $data)`
- [ ] `delete($id)` cascade
- [ ] `belongsToEnseignant($cours_id, $enseignant_id)` verification propriete

**models/Lecon.php**
- [ ] Classe `Lecon`
- [ ] `findByCours($cours_id)` ordonnees par ordre ASC
- [ ] `findById($id)`
- [ ] `create($cours_id, $titre, $description, $type, $url, $ordre)`
- [ ] `update($id, $data)`
- [ ] `delete($id)` cascade
- [ ] `reordonner($cours_id, $ordre_array)`
- [ ] `countByCours($cours_id)` nombre total

**models/Evaluation.php**
- [ ] Classe `Evaluation`
- [ ] `findByLecon($lecon_id)` avec questions JOIN
- [ ] `findById($id)`
- [ ] `create($lecon_id, $titre, $note_de_passage, $duree_limite, $tentatives_max)`
- [ ] `update($id, $data)`
- [ ] `getQuestions($evaluation_id)` ordonnees
- [ ] `addQuestion($evaluation_id, $texte, $options_json, $reponse, $ordre)`
- [ ] `updateQuestion($question_id, $data)`
- [ ] `deleteQuestion($question_id)`

**models/Progression.php**
- [ ] Classe `Progression`
- [ ] `findByEtudiant($etudiant_id)` toutes progressions
- [ ] `findByEtudiantAndLecon($etudiant_id, $lecon_id)`
- [ ] `findByEtudiantAndCours($etudiant_id, $cours_id)`
- [ ] `upsert($etudiant_id, $lecon_id, $evaluation_id, $note, $valide)` INSERT ON DUPLICATE KEY UPDATE GREATEST
- [ ] `calculerProgressionCours($etudiant_id, $cours_id)` pourcentage
- [ ] `leconsValidees($etudiant_id, $cours_id)` liste
- [ ] `getTentatives($etudiant_id, $evaluation_id)` nombre
- [ ] `marquerConsultee($etudiant_id, $lecon_id, $evaluation_id)` UPSERT lecon_consultee = 1

**models/Certificat.php**
- [ ] Classe `Certificat`
- [ ] `findByEtudiant($etudiant_id)` avec JOIN module
- [ ] `findByCode($code)` verification publique
- [ ] `create($etudiant_id, $module_id)` avec code SHA-256
- [ ] `genererCode()` `bin2hex(random_bytes(32))`
- [ ] `exists($etudiant_id, $module_id)` verifie doublon

**models/CloudinaryHelper.php**
- [ ] Methode statique `upload($file_path, $folder, $public_id, $resource_type)` via API REST curl
- [ ] Methode statique `destroy($public_id, $resource_type)` pour suppression
- [ ] Methode statique `getConfigForMime($mime)` retourne resource_type + folder
- [ ] Signature SHA1 construite correctement (parametres tries + secret)
- [ ] Gestion erreur curl (timeout, HTTP non-200)
- [ ] Verification que les constantes Cloudinary sont definies avant appel

### 1.4 CSS (6 fichiers)

**public/css/reset.css**
- [x] `box-sizing: border-box` universel
- [x] `margin: 0; padding: 0` sur body
- [x] Listes sans style
- [x] Images `max-width: 100%`
- [x] Police Inter sur body

**public/css/variables.css**
- [x] 12 couleurs de la charte (section 3.1 CONSTRUCTION.md)
- [x] 5 tailles de police
- [x] Espacements : 4px a 48px
- [x] Rayons de bordure : 4px, 8px, 12px
- [x] Ombres : legere, moyenne, elevee
- [x] Transitions

**public/css/base.css**
- [x] Body : fond, couleur, line-height 1.6, font-family
- [x] Headings h1-h6 : tailles, poids, marges
- [x] Liens : couleur secondary, hover accent
- [x] Paragraphes, listes : marges
- [x] `.container` max-width 1200px centre
- [x] Utilitaires : `.text-muted`, `.text-center`, `.text-right`, `.mt-1`-`.mt-4`, `.mb-1`-`.mb-4`, `.hidden`

**public/css/components.css**
- [x] `.btn` base : padding, border-radius, cursor, transition
- [x] `.btn-primary` fond primary, texte blanc, hover
- [x] `.btn-secondary` contourne, hover fond leger
- [x] `.btn-danger` fond error
- [x] `.btn-success` fond success
- [x] `.btn-disabled` fond locked, not-allowed, opacity
- [x] `.card` fond blanc, ombre, border-radius, padding
- [x] `.card-header`, `.card-body`, `.card-footer`
- [x] `.progress-bar` conteneur 8px
- [x] `.progress-bar-fill` gradient, transition
- [x] `.progress-circle` SVG stroke-dasharray
- [x] `.badge` + 5 variantes (success, error, warning, info, locked)
- [x] `.alert` + 2 variantes (success, error)
- [x] `.modal-overlay` fixed, fond noir 50%, z-index 1000
- [x] `.modal` centre, fond blanc, max-width 600px
- [x] `.modal-header`, `.modal-body`, `.modal-footer`
- [x] `.table` border-collapse, th gris, cellules padding
- [x] `.form-group`, `.form-label`, `.form-input`, `.form-select`, `.form-error`

**public/css/layout.css**
- [x] `.app-layout` grid sidebar + main
- [x] `.sidebar` 260px, fond primary, sticky
- [x] `.sidebar-logo`, `.sidebar-nav`, `.sidebar-nav-item`, `.sidebar-user`
- [x] `.sidebar-nav-item.active` fond blanc 15%, bordure gauche
- [x] `.main-content` padding, min-height 100vh
- [x] `.topbar` flex titre + actions
- [x] Responsive 768px : sidebar overlay
- [x] Responsive 1024px : sidebar fixe

**public/css/pages.css**
- [x] `.dashboard-grid` CSS Grid responsive
- [x] `.dashboard-stat` carte stat : icone, valeur, label
- [x] `.course-grid` grille cartes cours
- [x] `.course-card` image, titre, enseignant, barre progression
- [x] `.lesson-list` liste ordonnee lecons
- [x] `.lesson-item` flex, icone etat, titre, badge
- [x] `.video-player` 16:9, controles
- [x] `.pdf-viewer` iframe 80vh
- [x] `.quiz-container`, `.quiz-option`, `.quiz-result`
- [x] `.certificate` format A4, bordure, centrage
- [x] `.login-page` centrage vertical/horizontal
- [x] `@media print` pour certificat

### 1.5 Layouts PHP (3 fichiers)

**views/layouts/header.php**
- [x] `<!DOCTYPE html>` + `lang="fr"`
- [x] `<meta charset="UTF-8">`
- [x] `<meta name="viewport" content="width=device-width, initial-scale=1.0">`
- [x] `<title>` dynamique + APP_NAME
- [x] 6 liens CSS : reset, variables, base, components, layout, pages
- [x] Google Fonts link pour Inter (400, 500, 600, 700)
- [x] Require config/session.php
- [x] Verification login / redirection si non connecte (sauf pages publiques)
- [x] Ouverture `<body>` + `<div class="app-layout">`
- [x] Inclusion sidebar.php si connecte

**views/layouts/sidebar.php**
- [x] Logo SVG en haut
- [x] Navigation conditionnelle par role :
  - [x] Etudiant : Dashboard, Catalogue, Mes cours, Progression, Certificats
  - [x] Enseignant : Dashboard, Mes cours, Creer un cours, Statistiques
  - [x] Promoteur : Dashboard, Modules, Supervision
- [x] Icones SVG inline pour chaque lien
- [x] Classe `.active` selon page courante
- [x] Bloc utilisateur bas : nom, role, deconnexion SVG

**views/layouts/footer.php**
- [x] Fermeture `</main>` + `</div>` (app-layout)
- [x] Affichage message flash si present
- [x] Scripts JS : app.js toujours, quiz.js/player.js/progression.js conditionnels
- [x] Fermeture `</body>` + `</html>`

### 1.6 Icones SVG

**public/img/icons/ (30+ fichiers)**
- [ ] `dashboard.svg`, `book-open.svg`, `file-text.svg`, `video.svg`, `file.svg`
- [ ] `users.svg`, `user.svg`, `trending-up.svg`, `award.svg`, `shield-check.svg`
- [ ] `folder.svg`, `search.svg`, `plus.svg`, `edit.svg`, `trash.svg`
- [ ] `log-in.svg`, `log-out.svg`, `lock.svg`, `check-circle.svg`, `x-circle.svg`
- [ ] `chevron-up.svg`, `chevron-down.svg`, `menu.svg`, `x.svg`, `settings.svg`
- [ ] `play.svg`, `pause.svg`, `volume-2.svg`, `volume-x.svg`, `maximize.svg`
- [ ] `printer.svg`, `download.svg`, `eye.svg`, `bar-chart.svg`
- [ ] Chaque SVG utilise `currentColor` pour la couleur
- [ ] Chaque SVG a `viewBox="0 0 24 24"` et `width/height` par defaut 24

**public/img/logo/**
- [ ] `logo.svg` logo de la plateforme

### 1.7 Routeur et securite

**index.php**
- [x] Require config (database, constants, session)
- [x] Lecture `$_GET['page']` avec defaut
- [x] Pages publiques autorisees sans login : login, register, verifier-certificat
- [x] Si non connecte et page non publique : redirection login
- [x] Si connecte sans page : redirection dashboard du role
- [x] Parsing route : `role/vue` avec parametres GET additionnels
- [x] Verification `file_exists` de la vue
- [x] Inclusion header + vue + footer
- [x] Gestion 404

**.htaccess (racine)**
- [x] `RewriteEngine On`
- [x] Headers securite (X-Content-Type-Options, X-Frame-Options, X-XSS-Protection, Referrer-Policy)
- [x] Protection des dossiers config/, models/, actions/ de l'acces direct

**public/.htaccess**
- [x] Protection fichiers PHP dans public (sauf ceux voulus)

**public/uploads/.htaccess**
- [x] `Deny from all` empeche acces direct

### 1.8 Docker (3 fichiers)

**Dockerfile**
- [x] Base `php:8.2-apache`
- [x] Extensions installees : pdo, pdo_pgsql, pgsql, mbstring, zip, gd, fileinfo, curl
- [x] Modules Apache : rewrite, headers
- [x] PHP config : upload_max_filesize 512M, post_max_size 512M, memory_limit 256M
- [x] COPY docker/apache.conf
- [x] WORKDIR /var/www/html
- [x] COPY source, chown www-data
- [x] EXPOSE 80

**docker-compose.yml**
- [x] Service `app` : build local, port 8080:80, volumes, depends_on db healthy
- [x] Service `db` : postgres:16-alpine, port 5433:5432, volumes, healthcheck, schema.sql init
- [x] Service `adminer` : profil dev uniquement, port 8081:8080
- [x] Volumes : pg_data
- [x] Variables d'env lues depuis .env

**docker/apache.conf**
- [x] VirtualHost *:80
- [x] DocumentRoot /var/www/html
- [x] AllowOverride All
- [ ] Headers securite

**.env.example**
- [x] DATABASE_URL (Neon production)
- [x] DB_HOST, DB_PORT, DB_NAME, DB_USER, DB_PASS (Docker local)
- [x] CLOUDINARY_CLOUD_NAME, CLOUDINARY_API_KEY, CLOUDINARY_API_SECRET
- [x] APP_NAME, APP_ENV, APP_BASE_URL, SESSION_TIMEOUT

**.gitignore**
- [x] .env, .env.local exclus
- [x] config/database.php exclu (credentials)
- [x] public/uploads/pdf/* et video/* exclus (sauf .gitkeep)
- [x] vendor/, node_modules/ exclus
- [x] Fichiers IDE exclus (.vscode, .idea, *.swp)

### 1.9 Tests Phase 1

- [x] `config/database.php` charge sans erreur, `$pdo` valide
- [x] `sql/schema.sql` execute sur Neon : 9 tables creees
- [x] `sql/seed.php` execute : 3 utilisateurs + demo
- [x] Chaque modele instancie sans erreur
- [x] `Utilisateur::authentifier('paul.kamga@lms.cm', 'etudiant123')` retourne user
- [x] `Utilisateur::authentifier('paul.kamga@lms.cm', 'mauvais')` retourne null
- [x] `Cours::listerParEnseignant(id)` retourne le cours demo
- [x] `Lecon::listerParCours(id)` retourne 3 lecons ordonnees
- [x] CSS charge : styles de base appliques sur page blanche
- [x] Layout header + sidebar + footer s'affiche sans erreur
- [x] Routeur : redirection login si non connecte
- [x] Routeur : `?page=login` affiche la page
- [x] `docker compose --profile dev up -d` lance les 3 services
- [x] `localhost:8080` affiche l'application
- [x] `localhost:8081` affiche Adminer
- [x] `localhost:5433` accessible pour PostgreSQL

---

## PHASE 2 -- AUTHENTIFICATION

### 2.1 Vues

**views/auth/login.php**
- [x] Layout sans sidebar (centrage plein ecran)
- [x] Logo SVG centre
- [x] Champ email (type email, required)
- [x] Champ mot de passe (type password, required)
- [x] Token CSRF en hidden
- [x] Bouton "Se connecter" `.btn-primary`
- [x] Lien vers register
- [x] Affichage flash errors
- [x] Icones SVG sur les champs (user, lock)

**views/auth/register.php**
- [x] Layout sans sidebar
- [x] Champ nom (required, max 100)
- [x] Champ prenom (required, max 100)
- [x] Champ email (type email, required)
- [x] Champ mot de passe (required, minlength 6)
- [x] Champ confirmation (required)
- [x] Token CSRF
- [x] Bouton "Creer mon compte"
- [x] Lien vers login
- [x] Affichage erreurs

### 2.2 Actions

**actions/auth/login.php**
- [x] Verification POST
- [x] Verification CSRF avec `hash_equals()`
- [x] Validation email : non vide, `filter_var FILTER_VALIDATE_EMAIL`
- [x] Validation mot de passe : non vide
- [x] `Utilisateur::authenticate($email, $password)`
- [x] Echec : `set_flash('error')` + redirect login
- [x] Succes : `session_regenerate_id(true)`
- [x] Stockage session : user_id, user_role, user_nom, user_prenom
- [x] Redirection selon role : etudiant/enseignant/promoteur

**actions/auth/register.php**
- [x] Verification POST + CSRF
- [x] Validation nom/prenom : non vides, max 100
- [x] Validation email : format valide
- [x] Verification email unique : `Utilisateur::findByEmail`
- [x] Validation mot de passe : longueur >= 6
- [x] Validation confirmation : identique
- [x] `Utilisateur::create()` role 'etudiant'
- [x] Succes : flash success + redirect login
- [x] Echec : flash error + redirect register

**actions/auth/logout.php**
- [x] `$_SESSION = []`
- [x] Suppression cookie session
- [x] `session_destroy()`
- [x] Redirection login

### 2.3 Tests Phase 2

- [ ] Login : mauvais email -> erreur
- [ ] Login : mauvais mot de passe -> erreur
- [ ] Login etudiant -> redirect dashboard etudiant
- [ ] Login enseignant -> redirect dashboard enseignant
- [ ] Login promoteur -> redirect dashboard promoteur
- [ ] Session : user_id, user_role, user_nom, user_prenom remplis
- [ ] Session ID regenere apres login
- [ ] Register : email existant -> erreur
- [ ] Register : mot de passe court -> erreur
- [ ] Register : confirmation differente -> erreur
- [ ] Register valide -> compte BDD + redirect login
- [ ] Nouveau compte se connecte immediatement
- [ ] CSRF invalide -> 403
- [ ] Deconnexion -> session detruite + redirect login
- [ ] Apres deconnexion, page protegee -> redirect login

---

## PHASE 3 -- ESPACE ENSEIGNANT

### 3.1 Vues

**views/enseignant/dashboard.php**
- [x] Header + sidebar enseignant
- [x] 4 cartes stats : cours, lecons, inscrits, taux reussite (icones SVG)
- [x] Tableau cours recents : Titre, Lecons, Inscrits, Taux
- [x] Bouton "Creer un cours" `.btn-primary` + icone SVG plus
- [x] Footer

**views/enseignant/cours_gestion.php**
- [x] Liste cours en cartes : titre, description tronquee, nb lecons, date
- [x] Bouton Modifier (SVG edit) sur chaque carte
- [x] Bouton Supprimer (SVG trash) avec confirm JS
- [x] Formulaire creation : titre, description, select module, CSRF, bouton

**views/enseignant/cours_edit.php**
- [x] Titre et description modifiables
- [x] Liste lecons ordonnee : ordre, titre, type (SVG), statut eval
- [x] Bouton "Ajouter lecon" : formulaire titre, description, type, file, CSRF
- [x] Section evaluation par lecon : creer/modifier quiz
- [x] Formulaire quiz : titre, note passage, tentatives max
- [x] Liste questions existantes + ajout/modif/suppr
- [x] Formulaire question : texte, 4 options, reponse correcte

**views/enseignant/statistiques.php**
- [x] Select cours pour filtrer
- [x] Tableau etudiants : nom, progression %, lecons validees/total, derniere activite
- [x] Barre progression par etudiant
- [x] Stats globales : moyenne scores, taux completion

### 3.2 Actions

**actions/enseignant/cours_create.php**
- [x] POST + CSRF + role enseignant
- [x] Validation titre : non vide, max 150
- [x] INSERT cours avec enseignant_id session
- [x] Redirect cours_edit + flash

**actions/enseignant/cours_update.php**
- [x] POST + CSRF + role + propriete
- [x] Validation champs
- [x] UPDATE cours
- [x] Redirect + flash

**actions/enseignant/cours_delete.php**
- [x] POST + CSRF + role + propriete
- [x] DELETE cours (cascade)
- [x] Redirect cours_gestion + flash

**actions/enseignant/lecon_create.php**
- [x] POST + CSRF + role + propriete cours parent
- [ ] Validation titre, type_contenu
- [x] Verification `$_FILES['contenu']` present + error OK
- [x] Verification taille max selon type
- [x] Verification MIME avec `finfo_file()`
- [x] Generation nom unique : `{type}_{cours_id}_{timestamp}.{ext}`
- [ ] `move_uploaded_file()` vers UPLOAD_PATH
- [x] Calcul ordre auto (MAX + 1)
- [x] INSERT lecon
- [x] Redirect cours_edit + flash

**actions/enseignant/lecon_update.php**
- [x] POST + CSRF + role + propriete
- [x] UPDATE titre, description
- [ ] Si nouveau fichier : supprimer ancien, upload nouveau avec memes verifications
- [x] Redirect + flash

**actions/enseignant/lecon_delete.php**
- [x] POST + CSRF + role + propriete
- [ ] Suppression fichier physique du disque
- [ ] DELETE lecon (cascade)
- [ ] Reordonnancement lecons restantes
- [x] Redirect + flash

**actions/enseignant/evaluation_create.php**
- [x] POST + CSRF + role + propriete
- [x] Validation titre, note_de_passage (1-100), tentatives_max (>=1)
- [x] INSERT evaluation
- [x] Boucle questions : validation texte, 4 options, reponse A/B/C/D
- [x] INSERT chaque question
- [x] Redirect + flash

**actions/enseignant/evaluation_update.php**
- [x] POST + CSRF + role + propriete
- [x] UPDATE evaluation
- [x] Gestion questions : ajout, modification, suppression
- [x] Redirect + flash

### 3.3 Tests Phase 3

- [ ] Dashboard affiche stats correctes
- [ ] Liste cours affiche tous les cours de l'enseignant
- [ ] Creation cours valide -> cree + redirect
- [ ] Creation sans titre -> erreur
- [ ] Modification cours -> enregistre
- [ ] Suppression cours -> cascade BDD
- [ ] Upload PDF valide -> fichier sur Cloudinary, URL en BDD, cloudinary_id enregistre
- [ ] Upload video MP4 valide -> fichier sur Cloudinary, URL en BDD, cloudinary_id enregistre
- [ ] Upload trop lourd -> erreur
- [ ] Upload mauvais MIME -> erreur
- [ ] Suppression lecon -> fichier supprime de Cloudinary + BDD
- [ ] Ordre lecons respecte apres ajout/suppression
- [ ] Creation eval + 3 questions -> BDD
- [ ] Modification question -> enregistre
- [ ] Suppression question -> BDD
- [ ] Enseignant A ne peut modifier cours de B -> 403
- [ ] CSRF invalide -> 403

---

## PHASE 4 -- ESPACE ETUDIANT

### 4.1 Vues

**views/etudiant/dashboard.php**
- [x] Header + sidebar etudiant
- [x] Message bienvenue avec prenom
- [x] 4 cartes stats : cours en cours, lecons validees, cours termines, certificats (SVG)
- [x] Section "Mes cours en cours" : cartes avec barre progression
- [x] Section "Cours disponibles" : 3 derniers non inscrits
- [x] Footer

**views/etudiant/cours_liste.php**
- [x] Grille cours disponibles (non inscrits)
- [x] Chaque carte : titre, description, enseignant, nb lecons, module
- [x] Bouton "S'inscrire" (SVG plus) sur chaque carte
- [x] Onglets "Mes cours" / "Catalogue"

**views/etudiant/cours_detail.php**
- [x] En-tete : titre, enseignant, description
- [x] Barre progression globale cours
- [x] Liste lecons ordonnee avec statuts :
  - [ ] "Non commencee" badge gris (SVG lock)
  - [ ] "En cours" badge bleu (SVG file-text)
  - [ ] "Validee" badge vert (SVG check-circle)
  - [ ] "Verrouillee" badge gris, pas de lien
- [x] Lecons accessibles : lien vers lecon_player
- [x] Mention module associe

**views/etudiant/lecon_player.php**
- [x] Layout : contenu 70% gauche, plan 30% droite
- [x] En-tete : titre lecon, fil d'ariane
- [ ] Zone contenu conditionnelle :
  - [x] PDF : iframe via `file_serve.php`
  - [x] Video : `<video>` HTML5 + controles personnalises
- [x] Bouton "Marquer comme lu"
  - [x] Desactive si video (active apres fin)
  - [x] Desactive si PDF (active apres consultation)
  - [x] AJAX : envoie lecon_viewed
- [x] Bouton "Passer l'evaluation"
  - [x] `.btn-disabled` tant que non lu
  - [x] `.btn-primary` apres marquage
  - [x] Clic : ouvre modale quiz
- [x] Panneau lateral droit :
  - [x] Liste lecons avec statut actuel
  - [x] Lecon active en evidence
  - [x] Barre progression cours
- [x] Si deja validee : score affiche, bouton "Repasser"

**views/etudiant/quiz_modal.php**
- [x] Fragment HTML (pas de layout complet)
- [x] Titre evaluation + infos (nb questions, seuil, tentatives restantes)
- [x] Chaque question : texte + 4 options radio (labels cliquables `.quiz-option`)
- [x] Bouton "Soumettre" (appelle quiz.js)
- [ ] Zone resultat (cachee, revelee apres AJAX) :
  - [x] Score pourcentage
  - [x] Alert success ou error
  - [x] Si valide : bouton "Lecon suivante"
  - [x] Si echec : bouton "Retenter" (si tentatives restantes)
  - [x] Corrections question par question (vert/rouge)

**views/etudiant/progression.php**
- [x] Header + sidebar
- [x] 4 stats globales : progression %, cours inscrits, cours termines, lecons validees
- [x] Detail par cours : carte avec barre progression + lecons validees/total
- [x] Lien vers chaque cours

**views/etudiant/certificats.php**
- [x] Liste certificats obtenus : module, date, code
- [x] Liste modules en cours : progression, cours restants
- [x] Lien vers vue imprimable pour chaque certificat
- [x] Lien "Continuer" pour modules en cours

**views/etudiant/certificat.php**
- [x] Layout minimal (pas de sidebar pour impression)
- [x] Conteneur A4
- [x] Bordure decorative CSS
- [x] Titre "CERTIFICAT DE REUSSITE"
- [x] Nom complet etudiant
- [x] Titre module
- [x] Liste cours du module
- [x] Date delivrance
- [x] Code verification unique
- [x] Logo SVG plateforme
- [x] `@media print` : masquer header/sidebar/footer/boutons, fond blanc
- [x] `@page { size: A4; margin: 1cm; }`
- [x] Bouton "Imprimer" (`window.print()`) masque a l'impression
- [x] Bouton "Retour" masque a l'impression

**views/etudiant/certificat_verifier.php**
- [x] Page publique (pas besoin de login)
- [x] Formulaire saisie code verification
- [x] Si trouve : infos etudiant, module, date
- [x] Si non trouve : "Certificat non trouve ou invalide"

### 4.2 JavaScript (4 fichiers)

**public/js/player.js**
- [x] Selection `<video>` + controles
- [x] Play/Pause toggle
- [x] Barre progression : update sur `timeupdate`, clic seek
- [x] Affichage temps MM:SS
- [x] Controle volume : slider + mute/unmute
- [x] Detection fin (`ended`) : indicateur "Video terminee" + active bouton "Marquer comme lu"
- [ ] Suivi >= 90% visionne (envoi AJAX optionnel)
- [x] Plein ecran toggle

**public/js/quiz.js**
- [x] `ouvrirQuiz(leconId)` : fetch quiz_modal.php, injection HTML, affichage modale
- [x] `soumettreQuiz(evaluationId)` : collecte radios, FormData + CSRF, fetch POST submit_quiz.php
- [x] Parsing JSON : affichage resultat, corrections vert/rouge
- [x] Si valide : appel `progression.js`, desactivation quiz, bouton "Lecon suivante"
- [x] Si echec : bouton "Retenter" si tentatives restantes
- [x] `fermerQuiz()` : masque modale
- [x] Clic overlay pour fermer

**public/js/progression.js**
- [x] `mettreAJourProgression(pourcentage)` : animation barre, texte %, celebration sobre a 100%
- [x] `mettreAJourStatutLecon(leconId, statut)` : badge + icone SVG
- [x] Ecoute evenements custom de quiz.js

**public/js/app.js**
- [ ] Init `DOMContentLoaded`
- [x] Toggle sidebar mobile (hamburger)
- [x] Flash messages auto-disparition 5s
- [x] Confirmations suppression (`confirm()`)
- [ ] Navigation active : classe `.active` sur lien courant

### 4.3 Actions

**actions/etudiant/cours_inscrire.php**
- [x] POST + CSRF + role etudiant
- [x] Verification cours existe + visible
- [x] Verification non deja inscrit (unicite)
- [x] INSERT inscriptions
- [x] Redirect cours_detail + flash

**actions/etudiant/lecon_viewed.php**
- [x] Verification AJAX + role etudiant
- [x] Verification inscription cours parent
- [x] UPSERT progressions SET lecon_consultee = 1
- [x] Reponse JSON `{"success": true, "lecon_id": X}`

**actions/etudiant/submit_quiz.php**
- [x] Verification AJAX + role etudiant
- [x] Verification evaluation existe
- [x] Verification inscription cours parent
- [x] Verification tentatives : `getTentatives() < tentatives_max`
- [x] Recuperation questions
- [x] Boucle comparaison reponses
- [x] Calcul score %
- [x] Determination validation
- [x] UPSERT progression avec GREATEST (meilleure note)
- [x] Calcul nouvelle progression cours
- [ ] Si 100% + module_id non NULL :
  - [x] Verification tous cours module a 100%
  - [x] Si tous valides : `Certificat::exists()` pour doubler
  - [x] Si non existant : `Certificat::create()` + code unique
  - [ ] Ajout `certificat_genere: true` dans JSON
- [x] Reponse JSON complete (score, seuil, valide, corrections, progression, certificat)

**actions/etudiant/file_serve.php**
- [x] require_login()
- [ ] Validation type (pdf|video) + file (basename)
- [x] Verification fichier physique
- [x] Verification reference BDD
- [ ] Verification autorisation :
  - [x] Enseignant : proprietaire cours
  - [x] Etudiant : inscrit cours
  - [x] Promoteur : global
- [x] Headers : Content-Type, Content-Length, Content-Disposition inline, Cache-Control
- [x] `readfile()` + exit

**actions/etudiant/certificat_download.php**
- [x] Verification role etudiant
- [x] Verification certificat appartient etudiant
- [x] Recuperation donnees (etudiant, module, date, code)
- [x] Redirect vers vue certificat.php

### 4.4 Tests Phase 4

- [ ] Dashboard stats correctes
- [ ] Catalogue affiche cours visibles non inscrits
- [ ] Inscription -> ligne inscriptions BDD + redirect
- [ ] Double inscription -> bloquee
- [ ] Detail cours : lecons ordonnees, statuts corrects
- [ ] Premiere lecon accessible, suivantes verrouillees
- [ ] Video : play/pause, seek, fin -> bouton lu actif
- [ ] PDF : iframe affiche via URL Cloudinary, bouton lu actif
- [ ] Marquer comme lu -> AJAX OK, bouton quiz actif
- [ ] Modale quiz : questions + 4 options affichees
- [ ] Soumission 100% -> valide, progression 33% (1/3)
- [ ] Soumission < seuil -> echec, bouton retenter
- [ ] Apres validation : progression maj, badge vert, lecon suivante deverrouillee
- [ ] Tentatives max -> quiz verrouille
- [ ] Corrections affichees (vert/rouge)
- [ ] Parcours 3 lecons -> 100% cours
- [ ] Acces lecon via file_serve.php -> redirection vers URL Cloudinary
- [ ] Etudiant A ne voit pas donnees de B
- [ ] Progression globale : detail par cours correct
- [ ] Liste certificats : obtenus + en attente
- [ ] Vue certificat : infos correctes, impression OK
- [ ] Verification publique : code valide -> infos, code invalide -> erreur

---

## PHASE 5 -- ESPACE PROMOTEUR

### 5.1 Vues

**views/promoteur/dashboard.php**
- [x] 5 cartes stats : modules, cours, enseignants, etudiants, certificats (SVG)
- [x] Tableau derniers certificats
- [x] Tableau modules avec nb cours associes

**views/promoteur/modules_gestion.php**
- [x] Liste modules : titre, description, nb cours, date
- [x] Bouton "Creer un module"
- [x] Boutons Modifier / Supprimer
- [x] Formulaire creation : titre, description, CSRF

**views/promoteur/module_edit.php**
- [x] Titre et description modifiables
- [x] Section cours associes : liste + bouton "Retirer"
- [x] Select ajouter cours + bouton "Associer"
- [x] Statut chaque cours (nb lecons, evaluations)
- [x] Section certificats delivres pour ce module

**views/promoteur/supervision.php**
- [x] Onglet Enseignants : liste avec cours et lecons
- [x] Onglet Etudiants : liste avec progressions
- [x] Onglet Certificats : liste avec code, date, etudiant, module
- [x] Filtres par module, cours, date

### 5.2 Actions

**actions/promoteur/module_create.php**
- [x] POST + CSRF + role promoteur
- [x] Validation titre non vide, max 150
- [x] INSERT modules avec promoteur_id
- [x] Redirect module_edit + flash

**actions/promoteur/module_update.php**
- [x] POST + CSRF + role promoteur
- [x] UPDATE titre, description
- [x] Redirect + flash

**actions/promoteur/module_delete.php**
- [x] POST + CSRF + role promoteur
- [x] Verification certificats lies (confirmation si existants)
- [x] DELETE module (cours : module_id SET NULL)
- [x] Redirect + flash

**actions/promoteur/module_assign.php**
- [x] POST + CSRF + role promoteur
- [x] Validation cours_id + module_id existent
- [x] UPDATE cours SET module_id
- [x] Redirect module_edit + flash

**actions/promoteur/promoteur_stats.php**
- [x] AJAX GET + role promoteur
- [x] Stats globales : nb utilisateurs par role, nb cours, nb modules, nb certificats
- [x] Reponse JSON

### 5.3 Tests Phase 5

- [ ] Dashboard stats globales correctes
- [ ] Creation module -> BDD + redirect
- [ ] Modification module -> enregistre
- [ ] Suppression module -> cours liberes (module_id NULL)
- [ ] Association cours -> module_id mis a jour
- [ ] Retrait cours -> module_id NULL
- [ ] Supervision enseignants correcte
- [ ] Supervision etudiants avec progressions
- [ ] Supervision certificats complete
- [ ] Promoteur ne peut acceder actions enseignant/etudiant -> 403

---

## PHASE 6 -- FINITION

### 6.1 Nettoyage code

- [x] Suppression `var_dump()`, `print_r()`, `echo` debug
- [x] Suppression code commente inutile
- [x] Suppression fichiers temporaires
- [x] Verification : aucun credential en dur (sauf config/database.php)
- [x] Indentation uniforme 4 espaces
- [x] Tous `require`/`include` chemins corrects
- [x] Tous les fichiers de CONSTRUCTION.md existent

### 6.2 README.md

- [x] Titre du projet
- [x] Description courte
- [x] Stack technique (PHP 8.2, MySQL 8, Docker, Apache)
- [x] Pre-requis : PHP, MySQL, Docker
- [ ] Installation :
  - [x] Cloner le repo
  - [x] Copier .env.example vers .env
  - [x] `docker compose --profile dev up -d`
  - [x] `docker compose exec app php sql/seed.php`
  - [x] Acceder localhost:8080
- [x] Comptes demo avec identifiants
- [x] Structure resumee

### 6.3 Verification structure

- [x] 72 fichiers presents (voir recapitulatif CONSTRUCTION.md section 1.1)
- [x] 5 fichiers vides remplis : lecon_update.php, evaluation_update.php, promoteur_stats.php, quiz_modal.php, certificat.php
- [x] Aucun fichier orphelin
- [x] sql/schema.sql executable en une commande
- [x] sql/seed.php executable sans erreur

---

## PHASE 7 -- AUDIT SECURITE

### 7.1 Injection SQL

- [ ] Toutes requetes : `$pdo->prepare()` + `execute([$params])`
- [ ] Aucune concatenation variable dans SQL
- [ ] `grep -r "WHERE.*\\$" --include="*.php"` -> 0 resultat
- [ ] Test : `' OR 1=1 --` dans chaque champ -> aucun effet

### 7.2 XSS

- [ ] Toutes sorties : `htmlspecialchars()` ou `sanitize()`
- [ ] `grep -rn 'echo \$' --include="*.php"` -> chaque echo protege
- [ ] Test : `<script>alert(1)</script>` dans chaque champ -> texte brut

### 7.3 CSRF

- [ ] Chaque formulaire : champ hidden CSRF
- [ ] Chaque action : `hash_equals()` sur token
- [ ] Test : formulaire sans token -> 403
- [ ] Test : token falsifie -> 403

### 7.4 Autorisation

- [x] Pages protegees : `require_login()` + `require_role()`
- [ ] Enseignant : verification propriete sur chaque cours
- [ ] Etudiant : verification inscription sur chaque cours
- [ ] Promoteur : seul role autorise
- [ ] Test : URL action enseignant en tant qu'etudiant -> 403
- [ ] Test : URL action promoteur en tant qu'enseignant -> 403

### 7.5 Upload

- [ ] MIME verifie avec `finfo_file()` (pas `$_FILES['type']`)
- [ ] Extension verifiee
- [ ] Taille max verifiee
- [ ] Upload vers Cloudinary (pas de stockage local en production)
- [ ] Cloudinary gere la securite des fichiers (CDN, acces controle)
- [ ] Test : PHP deguise en PDF -> refuse (MIME)
- [ ] Test : .exe renomme .pdf -> MIME refuse
- [ ] Test : suppression lecon -> fichier supprime de Cloudinary

### 7.6 Sessions

- [ ] `session_regenerate_id(true)` apres login
- [ ] Cookie HttpOnly + SameSite=Strict
- [ ] Deconnexion : destroy + suppression cookie
- [ ] Pas de donnees sensibles en session (seulement id, role, nom, prenom)

---

## PHASE 8 -- TESTS RESPONSIVE

### 8.1 Desktop (>= 1024px)

- [ ] Sidebar fixe visible
- [ ] Dashboard : 4 cartes cote a cote
- [ ] Cours : grille 3 colonnes
- [ ] Lecteur : 70% + 30%

### 8.2 Tablette (768px - 1023px)

- [ ] Sidebar overlay (hamburger)
- [ ] Dashboard : 2 cartes par ligne
- [ ] Cours : grille 2 colonnes
- [ ] Lecteur : 100%, panneau dessous

### 8.3 Mobile (< 768px)

- [ ] Sidebar cachee, toggle hamburger
- [ ] Dashboard : 1 carte par ligne
- [ ] Cours : 1 colonne
- [ ] Lecteur : plein ecran, lecons accordion
- [ ] Modale quiz : plein ecran
- [ ] Formulaires : champs pleine largeur
- [ ] Tableau : scroll horizontal

### 8.4 Navigateurs

- [ ] Chrome : toutes fonctionnalites OK
- [ ] Firefox : toutes fonctionnalites OK
- [ ] Safari : fonctionnalites de base OK
- [ ] Edge : fonctionnalites de base OK

### 8.5 Accessibilite

- [ ] Tous formulaires : `<label>` associes
- [ ] `aria-label` sur icones SVG interactives
- [ ] Contraste couleurs >= 4.5:1
- [ ] Navigation clavier : Tab, Enter, Escape modales
- [ ] Focus visible sur elements interactifs

---

## PHASE 9 -- TESTS BOUT EN BOUT

### 9.1 Parcours Enseignant complet

- [ ] Connexion jean.dupont@lms.cm / enseignant123
- [ ] Dashboard : 0 cours initialement
- [ ] Creation cours "JavaScript Avance"
- [ ] Ajout 3 lecons (PDF, Video, PDF)
- [ ] 3 fichiers dans uploads/
- [ ] Creation eval Lecon 1 : 4 questions, seuil 75%
- [ ] Creation eval Lecon 2 : 3 questions, seuil 70%
- [ ] Creation eval Lecon 3 : 5 questions, seuil 60%
- [ ] Modification question -> enregistre
- [ ] Suppression + ajout question -> OK
- [ ] Modification titre cours -> enregistre
- [ ] Stats : 0 etudiant

### 9.2 Parcours Etudiant complet (happy path)

- [ ] Connexion paul.kamga@lms.cm / etudiant123
- [ ] Inscription cours "JavaScript Avance"
- [ ] Detail : 3 lecons, premiere accessible
- [ ] Progression : 0%
- [ ] Lecon 1 (PDF) : lire, marquer lu, quiz 100% -> valide, 33%
- [ ] Lecon 2 (Video) : lire, fin, marquer lu, quiz 66% -> echec, retenter 100% -> valide, 66%
- [ ] Lecon 3 (PDF) : lire, marquer lu, quiz 80% -> valide, 100%
- [ ] Dashboard : cours termine

### 9.3 Parcours Promoteur + Certificat

- [ ] Connexion promoteur@lms.cm / promoteur123
- [ ] Creation module "Dev Web Frontend"
- [ ] Association 2 cours au module
- [ ] Etudiant complete cours 2 -> certificat auto genere
- [ ] Vue certificat : infos correctes
- [ ] Impression PDF : mise en page OK
- [ ] Verification publique : code valide -> infos affichees

### 9.4 Cas limites

- [ ] Quiz sans marquer lecon lue -> refuse
- [ ] Tentatives epuisees -> verrouille
- [ ] Lecon verrouillee -> pas d'acces
- [ ] Upload 25 Mo PDF -> refuse
- [ ] Upload .txt deguise .pdf -> MIME refuse
- [ ] Double soumission rapide quiz -> une seule prise en compte
- [ ] Deconnexion + page protegee -> redirect login
- [ ] Session expiree -> redirect login

---

## PHASE 10 -- DEPLOIEMENT

### 10.1 Docker local

- [x] `docker compose --profile dev up -d` : 3 services lancent
- [x] `localhost:8080` : application accessible
- [x] `localhost:8081` : Adminer accessible
- [x] `docker compose exec app php sql/seed.php` : donnees demo inserees
- [ ] Login + parcours complet fonctionne dans Docker
- [ ] Upload fichier fonctionne (Cloudinary ou fallback local si non configure)
- [ ] `docker compose down` : arret propre
- [ ] `docker compose up -d --build` : rebuild sans erreur

### 10.2 Deploiement Render

- [ ] Repo pousse sur GitHub
- [ ] Service Docker cree sur Render
- [ ] Dockerfile path : `./Dockerfile`
- [x] Variables d'env configurees : DATABASE_URL, CLOUDINARY_CLOUD_NAME, CLOUDINARY_API_KEY, CLOUDINARY_API_SECRET, APP_BASE_URL
- [ ] Neon PostgreSQL provisionne et schema.sql execute
- [ ] Premier build reussi
- [ ] `psql "$DATABASE_URL" -f sql/schema.sql` en production
- [ ] `php sql/seed.php` en production
- [ ] Tests complets en production :
  - [ ] Login 3 roles
  - [ ] Creation cours + lecon + quiz
  - [ ] Upload PDF/Video vers Cloudinary reussi
  - [ ] Inscription + parcours etudiant
  - [ ] Certificat genere
  - [ ] URL Cloudinary accessible pour les fichiers
- [ ] URL publique fonctionnelle

### 10.3 Commit final

- [ ] `git add` de tous les fichiers
- [x] `.gitignore` verifie : .env, config/database.php, uploads exclus
- [ ] Message commit descriptif
- [ ] Push vers repo distant

---

## RECAPITULATIF CHIFFRE

| Phase | Fichiers | Tests |
|-------|----------|-------|
| 0. Prerequis | 0 | 12 |
| 1. Fondations | 28 | 16 |
| 2. Authentification | 5 | 15 |
| 3. Enseignant | 12 | 17 |
| 4. Etudiant | 18 | 22 |
| 5. Promoteur | 9 | 10 |
| 6. Finition | 0 | 5 |
| 7. Securite | 0 (audit) | 24 |
| 8. Responsive | 0 | 20 |
| 9. Bout en bout | 0 (tests) | 4 parcours |
| 10. Deploiement | 0 | 18 |
| **TOTAL** | **73 fichiers** | **163 tests** |
