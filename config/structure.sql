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