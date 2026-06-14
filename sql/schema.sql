-- ============================================================
-- PLATEFORME LMS CAMEROUN -- SCHEMA POSTGRESQL (NEON)
-- ============================================================

-- Types personnalises
DO $$ BEGIN
    CREATE TYPE role_utilisateur AS ENUM ('etudiant', 'enseignant', 'promoteur');
EXCEPTION
    WHEN duplicate_object THEN null;
END $$;

DO $$ BEGIN
    CREATE TYPE type_contenu_lecon AS ENUM ('pdf', 'video');
EXCEPTION
    WHEN duplicate_object THEN null;
END $$;

-- ------------------------------------------------------------
-- 1. UTILISATEURS
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS utilisateurs (
    id              SERIAL PRIMARY KEY,
    nom             VARCHAR(100) NOT NULL,
    prenom          VARCHAR(100) NOT NULL,
    email           VARCHAR(150) UNIQUE NOT NULL,
    mot_de_passe    VARCHAR(255) NOT NULL,
    role            role_utilisateur NOT NULL DEFAULT 'etudiant',
    actif           BOOLEAN NOT NULL DEFAULT TRUE,
    date_creation   TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ------------------------------------------------------------
-- 2. MODULES (Promoteur)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS modules (
    id              SERIAL PRIMARY KEY,
    titre           VARCHAR(150) NOT NULL,
    description     TEXT,
    promoteur_id    INTEGER NOT NULL REFERENCES utilisateurs(id) ON DELETE CASCADE,
    date_creation   TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ------------------------------------------------------------
-- 3. COURS (Enseignant, rattachables a un module)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS cours (
    id              SERIAL PRIMARY KEY,
    titre           VARCHAR(150) NOT NULL,
    description     TEXT,
    enseignant_id   INTEGER NOT NULL REFERENCES utilisateurs(id) ON DELETE CASCADE,
    module_id       INTEGER REFERENCES modules(id) ON DELETE SET NULL,
    visible         BOOLEAN NOT NULL DEFAULT TRUE,
    date_creation   TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ------------------------------------------------------------
-- 4. LECONS
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS lecons (
    id              SERIAL PRIMARY KEY,
    cours_id        INTEGER NOT NULL REFERENCES cours(id) ON DELETE CASCADE,
    titre           VARCHAR(150) NOT NULL,
    description     TEXT,
    type_contenu    type_contenu_lecon NOT NULL,
    url_contenu     VARCHAR(500) NOT NULL,
    cloudinary_id   VARCHAR(255),
    duree_estimee   INTEGER,
    ordre           INTEGER NOT NULL DEFAULT 0,
    date_creation   TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ------------------------------------------------------------
-- 5. EVALUATIONS (une par lecon, relation 1-to-1)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS evaluations (
    id              SERIAL PRIMARY KEY,
    lecon_id        INTEGER UNIQUE NOT NULL REFERENCES lecons(id) ON DELETE CASCADE,
    titre           VARCHAR(150) NOT NULL,
    note_de_passage INTEGER NOT NULL DEFAULT 70,
    duree_limite    INTEGER,
    tentatives_max  INTEGER NOT NULL DEFAULT 3,
    date_creation   TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ------------------------------------------------------------
-- 6. QUESTIONS
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS questions (
    id                  SERIAL PRIMARY KEY,
    evaluation_id       INTEGER NOT NULL REFERENCES evaluations(id) ON DELETE CASCADE,
    question_text       TEXT NOT NULL,
    options_json        JSONB NOT NULL,
    reponse_correcte    VARCHAR(10) NOT NULL,
    ordre               INTEGER NOT NULL DEFAULT 0
);

-- ------------------------------------------------------------
-- 7. INSCRIPTIONS (etudiants aux cours)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS inscriptions (
    id                  SERIAL PRIMARY KEY,
    etudiant_id         INTEGER NOT NULL REFERENCES utilisateurs(id) ON DELETE CASCADE,
    cours_id            INTEGER NOT NULL REFERENCES cours(id) ON DELETE CASCADE,
    date_inscription    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT unique_inscription UNIQUE (etudiant_id, cours_id)
);

-- ------------------------------------------------------------
-- 8. PROGRESSIONS
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS progressions (
    id                  SERIAL PRIMARY KEY,
    etudiant_id         INTEGER NOT NULL REFERENCES utilisateurs(id) ON DELETE CASCADE,
    lecon_id            INTEGER NOT NULL REFERENCES lecons(id) ON DELETE CASCADE,
    evaluation_id       INTEGER NOT NULL REFERENCES evaluations(id) ON DELETE CASCADE,
    note_obtenue        INTEGER NOT NULL DEFAULT 0,
    valide              BOOLEAN NOT NULL DEFAULT FALSE,
    nb_tentatives       INTEGER NOT NULL DEFAULT 0,
    lecon_consultee     BOOLEAN NOT NULL DEFAULT FALSE,
    derniere_tentative  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT unique_progression UNIQUE (etudiant_id, lecon_id)
);

-- ------------------------------------------------------------
-- 9. CERTIFICATS
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS certificats (
    id                  SERIAL PRIMARY KEY,
    etudiant_id         INTEGER NOT NULL REFERENCES utilisateurs(id) ON DELETE CASCADE,
    module_id           INTEGER NOT NULL REFERENCES modules(id) ON DELETE CASCADE,
    code_verification   VARCHAR(64) UNIQUE NOT NULL,
    date_delivrance     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT unique_certificat UNIQUE (etudiant_id, module_id)
);

-- ============================================================
-- INDEX SUPPLEMENTAIRES (performance)
-- ============================================================
CREATE INDEX IF NOT EXISTS idx_progressions_etudiant ON progressions(etudiant_id);
CREATE INDEX IF NOT EXISTS idx_progressions_lecon ON progressions(lecon_id);
CREATE INDEX IF NOT EXISTS idx_progressions_valide ON progressions(etudiant_id, valide);
CREATE INDEX IF NOT EXISTS idx_cours_module ON cours(module_id);
CREATE INDEX IF NOT EXISTS idx_cours_enseignant ON cours(enseignant_id);
CREATE INDEX IF NOT EXISTS idx_lecons_cours ON lecons(cours_id, ordre);
CREATE INDEX IF NOT EXISTS idx_questions_evaluation ON questions(evaluation_id, ordre);
CREATE INDEX IF NOT EXISTS idx_inscriptions_etudiant ON inscriptions(etudiant_id);
