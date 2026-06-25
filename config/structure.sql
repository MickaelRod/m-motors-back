-- ============================================================
-- Table : vehicules (User Story 1 - Recherche de véhicules)
-- ============================================================

-- Création de la table des véhicules M-Motors
CREATE TABLE IF NOT EXISTS vehicules (
    id INT AUTO_INCREMENT PRIMARY KEY,
    marque VARCHAR(50) NOT NULL,
    modele VARCHAR(50) NOT NULL,
    prix_achat INT NULL, -- Prix d'achat total (si applicable)
    prix_location INT NULL, -- Prix mensuel de la location (si applicable)
    type_commercial VARCHAR(20) NOT NULL, -- Valeurs : 'achat' ou 'location'
    options_incluses TEXT NULL -- Options LLD (Assurance, Assistance, Entretien, etc.)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
²
-- Insertion d'un jeu de données de test pour le catalogue
INSERT INTO vehicules (marque, modele, type_commercial, prix_achat, prix_location, options_incluses) VALUES
('Peugeot', '208', 'achat', 12500, 150, NULL),
('Renault', 'Clio', 'location', 15000, 190, 'Assurance tous risques, Assistance dépannage'),
('Citroën', 'C3', 'location', 14200, 175, 'Entretien et SAV, Contrôle technique'),
('Volkswagen', 'Golf', 'achat', 19800, 240, NULL),
('Toyota', 'Yaris', 'achat', 16200, 195, NULL),
('Fiat', '500e', 'location', 22000, 210, 'Assurance tous risques, Entretien des batteries, Assistance 24/7');

-- ============================================================
-- Table : messages (User Story 2 - Formulaire de contact & Documents)
-- ============================================================
CREATE TABLE IF NOT EXISTS `messages` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `nom` VARCHAR(100) NOT NULL,
  `email` VARCHAR(150) NOT NULL,
  `telephone` VARCHAR(20) NOT NULL,
  `type_demande` VARCHAR(30) NOT NULL, -- Valeurs strictes : 'achat', 'financement', 'location', 'autre'
  `vehicule_id` INT DEFAULT NULL, -- Identifiant unique du véhicule lié (clé étrangère logique)
  `vehicule_nom` VARCHAR(100) DEFAULT NULL, -- Nom du véhicule pour historique (ex: "Peugeot 208")
  `message` TEXT NOT NULL,
  `document_path` VARCHAR(255) DEFAULT NULL,
  `statut_dossier` VARCHAR(20) DEFAULT 'en_attente', -- Utile pour le traitement back-office (ex: 'valide', 'refuse')
  `cree_le` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Ajout de la clé étrangère pour lier un message ou une demande à un utilisateur inscrit
ALTER TABLE messages ADD COLUMN utilisateur_id INT NULL DEFAULT NULL AFTER id;
ALTER TABLE messages ADD CONSTRAINT fk_messages_utilisateurs FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id) ON DELETE SET NULL;

-- ============================================================
-- Table : utilisateurs (User Story 3 - Gestion des comptes clients)
-- ============================================================
CREATE TABLE IF NOT EXISTS `utilisateurs` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `nom` VARCHAR(100) NOT NULL,
  `email` VARCHAR(150) NOT NULL UNIQUE, -- L'email est unique pour servir d'identifiant de connexion
  `telephone` VARCHAR(20) NOT NULL,
  `mot_de_passe` VARCHAR(255) NOT NULL, -- Stockage securise du mot de passe hache
  `cree_le` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Ajout du champ role avec la valeur 'client' par défaut (User Story 4 - Gestion des véhicules dans le Back-Office)
ALTER TABLE utilisateurs ADD COLUMN role VARCHAR(20) NOT NULL DEFAULT 'client' AFTER mot_de_passe;

-- Insertion du compte administrateur requis par l'énoncé (User Story 4 - Gestion des véhicules dans le Back-Office)
INSERT INTO utilisateurs (nom, email, telephone, mot_de_passe, role) 
VALUES ('Administrateur', 'admin@m-motors.fr', '0102030405', '$2y$10$7vMhUjG19Zg.eBvF.qjZQuP3/63jX8w3U6Kfe5BwIscT9f0gA8fUe', 'admin');