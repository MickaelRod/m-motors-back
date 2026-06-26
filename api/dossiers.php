<?php
require_once __DIR__ . '/../config/bootstrap.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../src/dossier.php';
require_once __DIR__ . '/../src/logger.php';

initialiser_cors_json();
demarrer_session_admin();
exiger_admin();

$methode = $_SERVER['REQUEST_METHOD'];

// --- CAS 1 : RÉCUPÉRATION DE L'INTÉGRALITÉ DES DOSSIERS (GET) ---
if ($methode === 'GET') {
    try {
        $sql = "SELECT
                    m.id,
                    m.nom AS contact_nom,
                    m.email AS contact_email,
                    m.telephone AS contact_telephone,
                    m.message,
                    m.document_path,
                    m.statut_dossier,
                    m.type_demande,
                    m.vehicule_id,
                    m.vehicule_nom,
                    COALESCE(u1.nom,   u2.nom)       AS client_nom,
                    COALESCE(u1.email, u2.email)     AS client_email,
                    COALESCE(u1.telephone, u2.telephone) AS client_telephone
                FROM messages m
                LEFT JOIN utilisateurs u1 ON m.utilisateur_id = u1.id
                LEFT JOIN utilisateurs u2 ON u1.id IS NULL AND m.email = u2.email
                ORDER BY m.id DESC";

        $requete = $bdd->prepare($sql);
        $requete->execute();
        echo json_encode($requete->fetchAll(PDO::FETCH_ASSOC));

    } catch (PDOException $erreur) {
        Logger::error('dossiers.php', "Erreur PDO (GET) : " . $erreur->getMessage());
        http_response_code(500);
        echo json_encode(["erreur" => "Erreur lors de la récupération des dossiers."]);
    }
    exit();
}

// --- CAS 2 : MISE À JOUR DU STATUT DU DOSSIER (POST) ---
if ($methode === 'POST') {
    $donnees        = lire_json_entrant();
    $id_dossier     = isset($donnees['id'])             ? intval($donnees['id'])            : 0;
    $nouveau_statut = isset($donnees['statut_dossier']) ? trim($donnees['statut_dossier'])  : '';

    if ($id_dossier <= 0 || !Dossier::estStatutValide($nouveau_statut)) {
        http_response_code(400);
        echo json_encode(["erreur" => "Données invalides ou statut non reconnu."]);
        exit();
    }

    try {
        $requete_maj = $bdd->prepare("UPDATE messages SET statut_dossier = :statut WHERE id = :id");
        $requete_maj->execute(['statut' => $nouveau_statut, 'id' => $id_dossier]);
        Logger::info('dossiers.php', "Statut de la demande #" . $id_dossier . " mis à jour : " . $nouveau_statut);
        echo json_encode(["succes" => "Statut du dossier mis à jour avec succès.", "statut_dossier" => $nouveau_statut]);

    } catch (PDOException $erreur) {
        Logger::error('dossiers.php', "Erreur PDO (POST) : " . $erreur->getMessage());
        http_response_code(500);
        echo json_encode(["erreur" => "Erreur technique lors de la mise à jour du dossier."]);
    }
    exit();
}

http_response_code(405);
echo json_encode(["erreur" => "Méthode non autorisée."]);
