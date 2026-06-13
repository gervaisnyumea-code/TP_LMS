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
- [ ] `AllowOverride All` dans la config Apache
- [ ] Docker installe (`docker --version`)
- [ ] Docker Compose installe (`docker compose version`)

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
- [ ] Lecture `DATABASE_URL` depuis env (Neon production)
- [ ] Fallback DSN `pgsql:host=...;port=5432;dbname=...` (dev local)
- [ ] Instance PDO avec : `ERRMODE_EXCEPTION`, `FETCH_ASSOC`, `EMULATE_PREPARES => false`
- [ ] Try/catch avec message d'erreur explicite
- [ ] Variable `$pdo` exportee apres require
- [ ] Test : `require 'config/database.php';` ne produit aucune erreur

**config/constants.php**
- [ ] `APP_NAME` defini (lecture env avec fallback)
- [ ] `APP_ENV` defini
- [ ] `APP_BASE_URL` defini
- [ ] `CLOUDINARY_CLOUD_NAME` defini (lecture env)
- [ ] `CLOUDINARY_API_KEY` defini (lecture env)
- [ ] `CLOUDINARY_API_SECRET` defini (lecture env)
- [ ] `CLOUDINARY_BASE_URL` construit depuis CLOUDINARY_CLOUD_NAME
- [ ] `UPLOAD_PATH_PDF` chemin absolu
- [ ] `UPLOAD_PATH_VIDEO` chemin absolu
- [ ] `MAX_FILE_SIZE_PDF` = 50 Mo
- [ ] `MAX_FILE_SIZE_VIDEO` = 500 Mo
- [ ] `DEFAULT_NOTE_PASSAGE` = 70
- [ ] `DEFAULT_TENTATIVES_MAX` = 3
- [ ] `ROLES` = tableau des 3 roles
- [ ] `MIME_PDF` = ['application/pdf']
- [ ] `MIME_VIDEO` = ['video/mp4', 'video/webm']
- [ ] `MIME_IMAGE` = ['image/jpeg', 'image/png', 'image/webp']
- [ ] `SESSION_TIMEOUT` lecture env
- [ ] `SESSION_NAME` defini

**config/session.php**
- [ ] `session_name(SESSION_NAME)` appele
- [ ] `session_start()` appele
- [ ] Cookie params : `HttpOnly`, `SameSite=Strict`, `path=/`
- [ ] Fonction `is_logged_in()` retourne bool
- [ ] Fonction `get_current_user_id()` retourne ID ou redirige
- [ ] Fonction `get_current_role()` retourne role
- [ ] Fonction `require_role($role)` verifie ou 403
- [ ] Fonction `require_login()` verifie ou redirige
- [ ] Fonction `csrf_token()` genere/retourne token
- [ ] Fonction `verify_csrf($token)` avec `hash_equals()`
- [ ] Fonction `set_flash($type, $message)`
- [ ] Fonction `get_flash()` retourne et supprime
- [ ] Fonction `sanitize($value)` wrapper `htmlspecialchars()`
- [ ] Verification timeout session (SESSION_TIMEOUT)

### 1.2 Schema base de donnees

**sql/schema.sql (PostgreSQL / Neon)**
- [ ] Types `CREATE TYPE role_utilisateur AS ENUM` et `type_contenu_lecon AS ENUM`
- [ ] Table `utilisateurs` : SERIAL, BOOLEAN, type role_utilisateur
- [ ] Table `modules` : SERIAL, promoteur_id INTEGER REFERENCES
- [ ] Table `cours` : SERIAL, BOOLEAN visible, REFERENCES avec ON DELETE CASCADE/SET NULL
- [ ] Table `lecons` : SERIAL, type_contenu type_contenu_lecon, cloudinary_id VARCHAR(255)
- [ ] Table `evaluations` : SERIAL, lecon_id INTEGER UNIQUE REFERENCES
- [ ] Table `questions` : SERIAL, options_json JSONB
- [ ] Table `inscriptions` : SERIAL, CONSTRAINT UNIQUE (etudiant_id, cours_id)
- [ ] Table `progressions` : SERIAL, BOOLEAN valide/lecon_consultee, CONSTRAINT UNIQUE
- [ ] Table `certificats` : SERIAL, CONSTRAINT UNIQUE (etudiant_id, module_id)
- [ ] 8 index de performance crees
- [ ] Script execute sans erreur sur Neon : `psql "$DATABASE_URL" -f sql/schema.sql`

**sql/seed.php (PostgreSQL)**
- [ ] Require database.php
- [ ] 3 comptes insere avec `password_hash(PASSWORD_BCRYPT)`
- [ ] `ON CONFLICT (email) DO NOTHING` (pas `INSERT IGNORE`)
- [ ] 1 module de demo
- [ ] 1 cours de demo
- [ ] 3 lecons de demo (ordres 1, 2, 3)
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
- [ ] `box-sizing: border-box` universel
- [ ] `margin: 0; padding: 0` sur body
- [ ] Listes sans style
- [ ] Images `max-width: 100%`
- [ ] Police Inter sur body

**public/css/variables.css**
- [ ] 12 couleurs de la charte (section 3.1 CONSTRUCTION.md)
- [ ] 5 tailles de police
- [ ] Espacements : 4px a 48px
- [ ] Rayons de bordure : 4px, 8px, 12px
- [ ] Ombres : legere, moyenne, elevee
- [ ] Transitions

**public/css/base.css**
- [ ] Body : fond, couleur, line-height 1.6, font-family
- [ ] Headings h1-h6 : tailles, poids, marges
- [ ] Liens : couleur secondary, hover accent
- [ ] Paragraphes, listes : marges
- [ ] `.container` max-width 1200px centre
- [ ] Utilitaires : `.text-muted`, `.text-center`, `.text-right`, `.mt-1`-`.mt-4`, `.mb-1`-`.mb-4`, `.hidden`

**public/css/components.css**
- [ ] `.btn` base : padding, border-radius, cursor, transition
- [ ] `.btn-primary` fond primary, texte blanc, hover
- [ ] `.btn-secondary` contourne, hover fond leger
- [ ] `.btn-danger` fond error
- [ ] `.btn-success` fond success
- [ ] `.btn-disabled` fond locked, not-allowed, opacity
- [ ] `.card` fond blanc, ombre, border-radius, padding
- [ ] `.card-header`, `.card-body`, `.card-footer`
- [ ] `.progress-bar` conteneur 8px
- [ ] `.progress-bar-fill` gradient, transition
- [ ] `.progress-circle` SVG stroke-dasharray
- [ ] `.badge` + 5 variantes (success, error, warning, info, locked)
- [ ] `.alert` + 2 variantes (success, error)
- [ ] `.modal-overlay` fixed, fond noir 50%, z-index 1000
- [ ] `.modal` centre, fond blanc, max-width 600px
- [ ] `.modal-header`, `.modal-body`, `.modal-footer`
- [ ] `.table` border-collapse, th gris, cellules padding
- [ ] `.form-group`, `.form-label`, `.form-input`, `.form-select`, `.form-error`

**public/css/layout.css**
- [ ] `.app-layout` grid sidebar + main
- [ ] `.sidebar` 260px, fond primary, sticky
- [ ] `.sidebar-logo`, `.sidebar-nav`, `.sidebar-nav-item`, `.sidebar-user`
- [ ] `.sidebar-nav-item.active` fond blanc 15%, bordure gauche
- [ ] `.main-content` padding, min-height 100vh
- [ ] `.topbar` flex titre + actions
- [ ] Responsive 768px : sidebar overlay
- [ ] Responsive 1024px : sidebar fixe

**public/css/pages.css**
- [ ] `.dashboard-grid` CSS Grid responsive
- [ ] `.dashboard-stat` carte stat : icone, valeur, label
- [ ] `.course-grid` grille cartes cours
- [ ] `.course-card` image, titre, enseignant, barre progression
- [ ] `.lesson-list` liste ordonnee lecons
- [ ] `.lesson-item` flex, icone etat, titre, badge
- [ ] `.video-player` 16:9, controles
- [ ] `.pdf-viewer` iframe 80vh
- [ ] `.quiz-container`, `.quiz-option`, `.quiz-result`
- [ ] `.certificate` format A4, bordure, centrage
- [ ] `.login-page` centrage vertical/horizontal
- [ ] `@media print` pour certificat

### 1.5 Layouts PHP (3 fichiers)

**views/layouts/header.php**
- [ ] `<!DOCTYPE html>` + `lang="fr"`
- [ ] `<meta charset="UTF-8">`
- [ ] `<meta name="viewport" content="width=device-width, initial-scale=1.0">`
- [ ] `<title>` dynamique + APP_NAME
- [ ] 6 liens CSS : reset, variables, base, components, layout, pages
- [ ] Google Fonts link pour Inter (400, 500, 600, 700)
- [ ] Require config/session.php
- [ ] Verification login / redirection si non connecte (sauf pages publiques)
- [ ] Ouverture `<body>` + `<div class="app-layout">`
- [ ] Inclusion sidebar.php si connecte

**views/layouts/sidebar.php**
- [ ] Logo SVG en haut
- [ ] Navigation conditionnelle par role :
  - [ ] Etudiant : Dashboard, Catalogue, Mes cours, Progression, Certificats
  - [ ] Enseignant : Dashboard, Mes cours, Creer un cours, Statistiques
  - [ ] Promoteur : Dashboard, Modules, Supervision
- [ ] Icones SVG inline pour chaque lien
- [ ] Classe `.active` selon page courante
- [ ] Bloc utilisateur bas : nom, role, deconnexion SVG

**views/layouts/footer.php**
- [ ] Fermeture `</main>` + `</div>` (app-layout)
- [ ] Affichage message flash si present
- [ ] Scripts JS : app.js toujours, quiz.js/player.js/progression.js conditionnels
- [ ] Fermeture `</body>` + `</html>`

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
- [ ] Require config (database, constants, session)
- [ ] Lecture `$_GET['page']` avec defaut
- [ ] Pages publiques autorisees sans login : login, register, verifier-certificat
- [ ] Si non connecte et page non publique : redirection login
- [ ] Si connecte sans page : redirection dashboard du role
- [ ] Parsing route : `role/vue` avec parametres GET additionnels
- [ ] Verification `file_exists` de la vue
- [ ] Inclusion header + vue + footer
- [ ] Gestion 404

**.htaccess (racine)**
- [ ] `RewriteEngine On`
- [ ] Headers securite (X-Content-Type-Options, X-Frame-Options, X-XSS-Protection, Referrer-Policy)
- [ ] Protection des dossiers config/, models/, actions/ de l'acces direct

**public/.htaccess**
- [ ] Protection fichiers PHP dans public (sauf ceux voulus)

**public/uploads/.htaccess**
- [ ] `Deny from all` empeche acces direct

### 1.8 Docker (3 fichiers)

**Dockerfile**
- [ ] Base `php:8.2-apache`
- [ ] Extensions installees : pdo, pdo_pgsql, pgsql, mbstring, zip, gd, fileinfo, curl
- [ ] Modules Apache : rewrite, headers
- [ ] PHP config : upload_max_filesize 512M, post_max_size 512M, memory_limit 256M
- [ ] COPY docker/apache.conf
- [ ] WORKDIR /var/www/html
- [ ] COPY source, chown www-data
- [ ] EXPOSE 80

**docker-compose.yml**
- [ ] Service `app` : build local, port 8080:80, volumes, depends_on db healthy
- [ ] Service `db` : postgres:16-alpine, port 5433:5432, volumes, healthcheck, schema.sql init
- [ ] Service `adminer` : profil dev uniquement, port 8081:8080
- [ ] Volumes : pg_data
- [ ] Variables d'env lues depuis .env

**docker/apache.conf**
- [ ] VirtualHost *:80
- [ ] DocumentRoot /var/www/html
- [ ] AllowOverride All
- [ ] Headers securite

**.env.example**
- [ ] DATABASE_URL (Neon production)
- [ ] DB_HOST, DB_PORT, DB_NAME, DB_USER, DB_PASS (Docker local)
- [ ] CLOUDINARY_CLOUD_NAME, CLOUDINARY_API_KEY, CLOUDINARY_API_SECRET
- [ ] APP_NAME, APP_ENV, APP_BASE_URL, SESSION_TIMEOUT

**.gitignore**
- [ ] .env, .env.local exclus
- [ ] config/database.php exclu (credentials)
- [ ] public/uploads/pdf/* et video/* exclus (sauf .gitkeep)
- [ ] vendor/, node_modules/ exclus
- [ ] Fichiers IDE exclus (.vscode, .idea, *.swp)

### 1.9 Tests Phase 1

- [ ] `config/database.php` charge sans erreur, `$pdo` valide
- [ ] `sql/schema.sql` execute sur Neon : 9 tables creees
- [ ] `sql/seed.php` execute : 3 utilisateurs + demo
- [ ] Chaque modele instancie sans erreur
- [ ] `Utilisateur::authentifier('paul.kamga@lms.cm', 'etudiant123')` retourne user
- [ ] `Utilisateur::authentifier('paul.kamga@lms.cm', 'mauvais')` retourne null
- [ ] `Cours::listerParEnseignant(id)` retourne le cours demo
- [ ] `Lecon::listerParCours(id)` retourne 3 lecons ordonnees
- [ ] CSS charge : styles de base appliques sur page blanche
- [ ] Layout header + sidebar + footer s'affiche sans erreur
- [ ] Routeur : redirection login si non connecte
- [ ] Routeur : `?page=login` affiche la page
- [ ] `docker compose --profile dev up -d` lance les 3 services
- [ ] `localhost:8080` affiche l'application
- [ ] `localhost:8081` affiche Adminer
- [ ] `localhost:5433` accessible pour PostgreSQL

---

## PHASE 2 -- AUTHENTIFICATION

### 2.1 Vues

**views/auth/login.php**
- [ ] Layout sans sidebar (centrage plein ecran)
- [ ] Logo SVG centre
- [ ] Champ email (type email, required)
- [ ] Champ mot de passe (type password, required)
- [ ] Token CSRF en hidden
- [ ] Bouton "Se connecter" `.btn-primary`
- [ ] Lien vers register
- [ ] Affichage flash errors
- [ ] Icones SVG sur les champs (user, lock)

**views/auth/register.php**
- [ ] Layout sans sidebar
- [ ] Champ nom (required, max 100)
- [ ] Champ prenom (required, max 100)
- [ ] Champ email (type email, required)
- [ ] Champ mot de passe (required, minlength 6)
- [ ] Champ confirmation (required)
- [ ] Token CSRF
- [ ] Bouton "Creer mon compte"
- [ ] Lien vers login
- [ ] Affichage erreurs

### 2.2 Actions

**actions/auth/login.php**
- [ ] Verification POST
- [ ] Verification CSRF avec `hash_equals()`
- [ ] Validation email : non vide, `filter_var FILTER_VALIDATE_EMAIL`
- [ ] Validation mot de passe : non vide
- [ ] `Utilisateur::authenticate($email, $password)`
- [ ] Echec : `set_flash('error')` + redirect login
- [ ] Succes : `session_regenerate_id(true)`
- [ ] Stockage session : user_id, user_role, user_nom, user_prenom
- [ ] Redirection selon role : etudiant/enseignant/promoteur

**actions/auth/register.php**
- [ ] Verification POST + CSRF
- [ ] Validation nom/prenom : non vides, max 100
- [ ] Validation email : format valide
- [ ] Verification email unique : `Utilisateur::findByEmail`
- [ ] Validation mot de passe : longueur >= 6
- [ ] Validation confirmation : identique
- [ ] `Utilisateur::create()` role 'etudiant'
- [ ] Succes : flash success + redirect login
- [ ] Echec : flash error + redirect register

**actions/auth/logout.php**
- [ ] `$_SESSION = []`
- [ ] Suppression cookie session
- [ ] `session_destroy()`
- [ ] Redirection login

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
- [ ] Header + sidebar enseignant
- [ ] 4 cartes stats : cours, lecons, inscrits, taux reussite (icones SVG)
- [ ] Tableau cours recents : Titre, Lecons, Inscrits, Taux
- [ ] Bouton "Creer un cours" `.btn-primary` + icone SVG plus
- [ ] Footer

**views/enseignant/cours_gestion.php**
- [ ] Liste cours en cartes : titre, description tronquee, nb lecons, date
- [ ] Bouton Modifier (SVG edit) sur chaque carte
- [ ] Bouton Supprimer (SVG trash) avec confirm JS
- [ ] Formulaire creation : titre, description, select module, CSRF, bouton

**views/enseignant/cours_edit.php**
- [ ] Titre et description modifiables
- [ ] Liste lecons ordonnee : ordre, titre, type (SVG), statut eval
- [ ] Bouton "Ajouter lecon" : formulaire titre, description, type, file, CSRF
- [ ] Section evaluation par lecon : creer/modifier quiz
- [ ] Formulaire quiz : titre, note passage, tentatives max
- [ ] Liste questions existantes + ajout/modif/suppr
- [ ] Formulaire question : texte, 4 options, reponse correcte

**views/enseignant/statistiques.php**
- [ ] Select cours pour filtrer
- [ ] Tableau etudiants : nom, progression %, lecons validees/total, derniere activite
- [ ] Barre progression par etudiant
- [ ] Stats globales : moyenne scores, taux completion

### 3.2 Actions

**actions/enseignant/cours_create.php**
- [ ] POST + CSRF + role enseignant
- [ ] Validation titre : non vide, max 150
- [ ] INSERT cours avec enseignant_id session
- [ ] Redirect cours_edit + flash

**actions/enseignant/cours_update.php**
- [ ] POST + CSRF + role + propriete
- [ ] Validation champs
- [ ] UPDATE cours
- [ ] Redirect + flash

**actions/enseignant/cours_delete.php**
- [ ] POST + CSRF + role + propriete
- [ ] DELETE cours (cascade)
- [ ] Redirect cours_gestion + flash

**actions/enseignant/lecon_create.php**
- [ ] POST + CSRF + role + propriete cours parent
- [ ] Validation titre, type_contenu
- [ ] Verification `$_FILES['contenu']` present + error OK
- [ ] Verification taille max selon type
- [ ] Verification MIME avec `finfo_file()`
- [ ] Generation nom unique : `{type}_{cours_id}_{timestamp}.{ext}`
- [ ] `move_uploaded_file()` vers UPLOAD_PATH
- [ ] Calcul ordre auto (MAX + 1)
- [ ] INSERT lecon
- [ ] Redirect cours_edit + flash

**actions/enseignant/lecon_update.php**
- [ ] POST + CSRF + role + propriete
- [ ] UPDATE titre, description
- [ ] Si nouveau fichier : supprimer ancien, upload nouveau avec memes verifications
- [ ] Redirect + flash

**actions/enseignant/lecon_delete.php**
- [ ] POST + CSRF + role + propriete
- [ ] Suppression fichier physique du disque
- [ ] DELETE lecon (cascade)
- [ ] Reordonnancement lecons restantes
- [ ] Redirect + flash

**actions/enseignant/evaluation_create.php**
- [ ] POST + CSRF + role + propriete
- [ ] Validation titre, note_de_passage (1-100), tentatives_max (>=1)
- [ ] INSERT evaluation
- [ ] Boucle questions : validation texte, 4 options, reponse A/B/C/D
- [ ] INSERT chaque question
- [ ] Redirect + flash

**actions/enseignant/evaluation_update.php**
- [ ] POST + CSRF + role + propriete
- [ ] UPDATE evaluation
- [ ] Gestion questions : ajout, modification, suppression
- [ ] Redirect + flash

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
- [ ] Header + sidebar etudiant
- [ ] Message bienvenue avec prenom
- [ ] 4 cartes stats : cours en cours, lecons validees, cours termines, certificats (SVG)
- [ ] Section "Mes cours en cours" : cartes avec barre progression
- [ ] Section "Cours disponibles" : 3 derniers non inscrits
- [ ] Footer

**views/etudiant/cours_liste.php**
- [ ] Grille cours disponibles (non inscrits)
- [ ] Chaque carte : titre, description, enseignant, nb lecons, module
- [ ] Bouton "S'inscrire" (SVG plus) sur chaque carte
- [ ] Onglets "Mes cours" / "Catalogue"

**views/etudiant/cours_detail.php**
- [ ] En-tete : titre, enseignant, description
- [ ] Barre progression globale cours
- [ ] Liste lecons ordonnee avec statuts :
  - [ ] "Non commencee" badge gris (SVG lock)
  - [ ] "En cours" badge bleu (SVG file-text)
  - [ ] "Validee" badge vert (SVG check-circle)
  - [ ] "Verrouillee" badge gris, pas de lien
- [ ] Lecons accessibles : lien vers lecon_player
- [ ] Mention module associe

**views/etudiant/lecon_player.php**
- [ ] Layout : contenu 70% gauche, plan 30% droite
- [ ] En-tete : titre lecon, fil d'ariane
- [ ] Zone contenu conditionnelle :
  - [ ] PDF : iframe via `file_serve.php`
  - [ ] Video : `<video>` HTML5 + controles personnalises
- [ ] Bouton "Marquer comme lu"
  - [ ] Desactive si video (active apres fin)
  - [ ] Desactive si PDF (active apres consultation)
  - [ ] AJAX : envoie lecon_viewed
- [ ] Bouton "Passer l'evaluation"
  - [ ] `.btn-disabled` tant que non lu
  - [ ] `.btn-primary` apres marquage
  - [ ] Clic : ouvre modale quiz
- [ ] Panneau lateral droit :
  - [ ] Liste lecons avec statut actuel
  - [ ] Lecon active en evidence
  - [ ] Barre progression cours
- [ ] Si deja validee : score affiche, bouton "Repasser"

**views/etudiant/quiz_modal.php**
- [ ] Fragment HTML (pas de layout complet)
- [ ] Titre evaluation + infos (nb questions, seuil, tentatives restantes)
- [ ] Chaque question : texte + 4 options radio (labels cliquables `.quiz-option`)
- [ ] Bouton "Soumettre" (appelle quiz.js)
- [ ] Zone resultat (cachee, revelee apres AJAX) :
  - [ ] Score pourcentage
  - [ ] Alert success ou error
  - [ ] Si valide : bouton "Lecon suivante"
  - [ ] Si echec : bouton "Retenter" (si tentatives restantes)
  - [ ] Corrections question par question (vert/rouge)

**views/etudiant/progression.php**
- [ ] Header + sidebar
- [ ] 4 stats globales : progression %, cours inscrits, cours termines, lecons validees
- [ ] Detail par cours : carte avec barre progression + lecons validees/total
- [ ] Lien vers chaque cours

**views/etudiant/certificats.php**
- [ ] Liste certificats obtenus : module, date, code
- [ ] Liste modules en cours : progression, cours restants
- [ ] Lien vers vue imprimable pour chaque certificat
- [ ] Lien "Continuer" pour modules en cours

**views/etudiant/certificat.php**
- [ ] Layout minimal (pas de sidebar pour impression)
- [ ] Conteneur A4
- [ ] Bordure decorative CSS
- [ ] Titre "CERTIFICAT DE REUSSITE"
- [ ] Nom complet etudiant
- [ ] Titre module
- [ ] Liste cours du module
- [ ] Date delivrance
- [ ] Code verification unique
- [ ] Logo SVG plateforme
- [ ] `@media print` : masquer header/sidebar/footer/boutons, fond blanc
- [ ] `@page { size: A4; margin: 1cm; }`
- [ ] Bouton "Imprimer" (`window.print()`) masque a l'impression
- [ ] Bouton "Retour" masque a l'impression

**views/etudiant/certificat_verifier.php**
- [ ] Page publique (pas besoin de login)
- [ ] Formulaire saisie code verification
- [ ] Si trouve : infos etudiant, module, date
- [ ] Si non trouve : "Certificat non trouve ou invalide"

### 4.2 JavaScript (4 fichiers)

**public/js/player.js**
- [ ] Selection `<video>` + controles
- [ ] Play/Pause toggle
- [ ] Barre progression : update sur `timeupdate`, clic seek
- [ ] Affichage temps MM:SS
- [ ] Controle volume : slider + mute/unmute
- [ ] Detection fin (`ended`) : indicateur "Video terminee" + active bouton "Marquer comme lu"
- [ ] Suivi >= 90% visionne (envoi AJAX optionnel)
- [ ] Plein ecran toggle

**public/js/quiz.js**
- [ ] `ouvrirQuiz(leconId)` : fetch quiz_modal.php, injection HTML, affichage modale
- [ ] `soumettreQuiz(evaluationId)` : collecte radios, FormData + CSRF, fetch POST submit_quiz.php
- [ ] Parsing JSON : affichage resultat, corrections vert/rouge
- [ ] Si valide : appel `progression.js`, desactivation quiz, bouton "Lecon suivante"
- [ ] Si echec : bouton "Retenter" si tentatives restantes
- [ ] `fermerQuiz()` : masque modale
- [ ] Clic overlay pour fermer

**public/js/progression.js**
- [ ] `mettreAJourProgression(pourcentage)` : animation barre, texte %, celebration sobre a 100%
- [ ] `mettreAJourStatutLecon(leconId, statut)` : badge + icone SVG
- [ ] Ecoute evenements custom de quiz.js

**public/js/app.js**
- [ ] Init `DOMContentLoaded`
- [ ] Toggle sidebar mobile (hamburger)
- [ ] Flash messages auto-disparition 5s
- [ ] Confirmations suppression (`confirm()`)
- [ ] Navigation active : classe `.active` sur lien courant

### 4.3 Actions

**actions/etudiant/cours_inscrire.php**
- [ ] POST + CSRF + role etudiant
- [ ] Verification cours existe + visible
- [ ] Verification non deja inscrit (unicite)
- [ ] INSERT inscriptions
- [ ] Redirect cours_detail + flash

**actions/etudiant/lecon_viewed.php**
- [ ] Verification AJAX + role etudiant
- [ ] Verification inscription cours parent
- [ ] UPSERT progressions SET lecon_consultee = 1
- [ ] Reponse JSON `{"success": true, "lecon_id": X}`

**actions/etudiant/submit_quiz.php**
- [ ] Verification AJAX + role etudiant
- [ ] Verification evaluation existe
- [ ] Verification inscription cours parent
- [ ] Verification tentatives : `getTentatives() < tentatives_max`
- [ ] Recuperation questions
- [ ] Boucle comparaison reponses
- [ ] Calcul score %
- [ ] Determination validation
- [ ] UPSERT progression avec GREATEST (meilleure note)
- [ ] Calcul nouvelle progression cours
- [ ] Si 100% + module_id non NULL :
  - [ ] Verification tous cours module a 100%
  - [ ] Si tous valides : `Certificat::exists()` pour doubler
  - [ ] Si non existant : `Certificat::create()` + code unique
  - [ ] Ajout `certificat_genere: true` dans JSON
- [ ] Reponse JSON complete (score, seuil, valide, corrections, progression, certificat)

**actions/etudiant/file_serve.php**
- [ ] require_login()
- [ ] Validation type (pdf|video) + file (basename)
- [ ] Verification fichier physique
- [ ] Verification reference BDD
- [ ] Verification autorisation :
  - [ ] Enseignant : proprietaire cours
  - [ ] Etudiant : inscrit cours
  - [ ] Promoteur : global
- [ ] Headers : Content-Type, Content-Length, Content-Disposition inline, Cache-Control
- [ ] `readfile()` + exit

**actions/etudiant/certificat_download.php**
- [ ] Verification role etudiant
- [ ] Verification certificat appartient etudiant
- [ ] Recuperation donnees (etudiant, module, date, code)
- [ ] Redirect vers vue certificat.php

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
- [ ] 5 cartes stats : modules, cours, enseignants, etudiants, certificats (SVG)
- [ ] Tableau derniers certificats
- [ ] Tableau modules avec nb cours associes

**views/promoteur/modules_gestion.php**
- [ ] Liste modules : titre, description, nb cours, date
- [ ] Bouton "Creer un module"
- [ ] Boutons Modifier / Supprimer
- [ ] Formulaire creation : titre, description, CSRF

**views/promoteur/module_edit.php**
- [ ] Titre et description modifiables
- [ ] Section cours associes : liste + bouton "Retirer"
- [ ] Select ajouter cours + bouton "Associer"
- [ ] Statut chaque cours (nb lecons, evaluations)
- [ ] Section certificats delivres pour ce module

**views/promoteur/supervision.php**
- [ ] Onglet Enseignants : liste avec cours et lecons
- [ ] Onglet Etudiants : liste avec progressions
- [ ] Onglet Certificats : liste avec code, date, etudiant, module
- [ ] Filtres par module, cours, date

### 5.2 Actions

**actions/promoteur/module_create.php**
- [ ] POST + CSRF + role promoteur
- [ ] Validation titre non vide, max 150
- [ ] INSERT modules avec promoteur_id
- [ ] Redirect module_edit + flash

**actions/promoteur/module_update.php**
- [ ] POST + CSRF + role promoteur
- [ ] UPDATE titre, description
- [ ] Redirect + flash

**actions/promoteur/module_delete.php**
- [ ] POST + CSRF + role promoteur
- [ ] Verification certificats lies (confirmation si existants)
- [ ] DELETE module (cours : module_id SET NULL)
- [ ] Redirect + flash

**actions/promoteur/module_assign.php**
- [ ] POST + CSRF + role promoteur
- [ ] Validation cours_id + module_id existent
- [ ] UPDATE cours SET module_id
- [ ] Redirect module_edit + flash

**actions/promoteur/promoteur_stats.php**
- [ ] AJAX GET + role promoteur
- [ ] Stats globales : nb utilisateurs par role, nb cours, nb modules, nb certificats
- [ ] Reponse JSON

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

- [ ] Suppression `var_dump()`, `print_r()`, `echo` debug
- [ ] Suppression code commente inutile
- [ ] Suppression fichiers temporaires
- [ ] Verification : aucun credential en dur (sauf config/database.php)
- [ ] Indentation uniforme 4 espaces
- [ ] Tous `require`/`include` chemins corrects
- [ ] Tous les fichiers de CONSTRUCTION.md existent

### 6.2 README.md

- [ ] Titre du projet
- [ ] Description courte
- [ ] Stack technique (PHP 8.2, MySQL 8, Docker, Apache)
- [ ] Pre-requis : PHP, MySQL, Docker
- [ ] Installation :
  - [ ] Cloner le repo
  - [ ] Copier .env.example vers .env
  - [ ] `docker compose --profile dev up -d`
  - [ ] `docker compose exec app php sql/seed.php`
  - [ ] Acceder localhost:8080
- [ ] Comptes demo avec identifiants
- [ ] Structure resumee

### 6.3 Verification structure

- [ ] 72 fichiers presents (voir recapitulatif CONSTRUCTION.md section 1.1)
- [ ] 5 fichiers vides remplis : lecon_update.php, evaluation_update.php, promoteur_stats.php, quiz_modal.php, certificat.php
- [ ] Aucun fichier orphelin
- [ ] sql/schema.sql executable en une commande
- [ ] sql/seed.php executable sans erreur

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

- [ ] Pages protegees : `require_login()` + `require_role()`
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

- [ ] `docker compose --profile dev up -d` : 3 services lancent
- [ ] `localhost:8080` : application accessible
- [ ] `localhost:8081` : Adminer accessible
- [ ] `docker compose exec app php sql/seed.php` : donnees demo inserees
- [ ] Login + parcours complet fonctionne dans Docker
- [ ] Upload fichier fonctionne (Cloudinary ou fallback local si non configure)
- [ ] `docker compose down` : arret propre
- [ ] `docker compose up -d --build` : rebuild sans erreur

### 10.2 Deploiement Render

- [ ] Repo pousse sur GitHub
- [ ] Service Docker cree sur Render
- [ ] Dockerfile path : `./Dockerfile`
- [ ] Variables d'env configurees : DATABASE_URL, CLOUDINARY_CLOUD_NAME, CLOUDINARY_API_KEY, CLOUDINARY_API_SECRET, APP_BASE_URL
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
- [ ] `.gitignore` verifie : .env, config/database.php, uploads exclus
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
