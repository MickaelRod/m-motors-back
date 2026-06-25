-- ============================================================
-- Table : vehicules (User Story 1 - Recherche de véhicules)
-- ============================================================

-- Création de la table des véhicules M-Motors
CREATE TABLE IF NOT EXISTS vehicules (
    id INT AUTO_INCREMENT PRIMARY KEY,
    marque VARCHAR(50) NOT NULL,
    modele VARCHAR(50) NOT NULL,
    type_commercial VARCHAR(20) NOT NULL, -- Valeurs : 'achat' ou 'location'
    prix INT NOT NULL, -- Prix d'achat total ou mensualité de location
    options_incluses TEXT NULL -- Options LLD (Assurance, Assistance, Entretien, etc.)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insertion d'un jeu de données de test pour le catalogue
INSERT INTO vehicules (marque, modele, type_commercial, prix, options_incluses) VALUES
('Peugeot', '208', 'achat', 12500, NULL),
('Renault', 'Clio', 'location', 190, 'Assurance tous risques, Assistance dépannage'),
('Citroën', 'C3', 'location', 175, 'Entretien et SAV, Contrôle technique'),
('Volkswagen', 'Golf', 'achat', 19800, NULL);

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