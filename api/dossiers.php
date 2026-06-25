<?php
$origine = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : 'http://localhost:8001';
header("Access-Control-Allow-Origin: " . $origine);
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

session_name('MMOTORS_BACK_SESSION');
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Sécurité : Vérification stricte des droits d'administration
if (!isset($_SESSION['utilisateur_role']) || $_SESSION['utilisateur_role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(["erreur" => "Accès interdit. Droits d'administration requis."]);
    exit();
}

require_once __DIR__ . '/../config/db.php';

$methode = $_SERVER['REQUEST_METHOD'];

// --- CAS 1 : RÉCUPÉRATION DE L'INTÉGRALITÉ DES DOSSIERS (GET) ---
if ($methode === 'GET') {
    try {
        // Jointure pour obtenir les coordonnées de l'utilisateur (si connecté lors de la demande)
        // ou conservation des coordonnées saisies manuellement dans le formulaire de contact
        $sql = "SELECT 
                    m.id, 
                    m.nom AS contact_nom, 
                    m.email AS contact_email, 
                    m.telephone AS contact_telephone, 
                    m.sujet, 
                    m.message, 
                    m.fichier_joint, 
                    m.statut_dossier, 
                    m.type_demande, 
                    m.vehicule_id, 
                    m.vehicule_nom, 
                    u.nom AS client_nom, 
                    u.email AS client_email, 
                    u.telephone AS client_telephone
                FROM messages m
                LEFT JOIN utilisateurs u ON m.utilisateur_id = u.id
                ORDER BY m.id DESC";

        $requete = $bdd->prepare($sql);
        $requete->execute();
        $dossiers = $requete->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode($dossiers);
    } catch (PDOException $erreur) {
        http_response_code(500);
        echo json_encode(["erreur" => "Erreur lors de la récupération des dossiers."]);
    }
    exit();
}

// --- CAS 2 : MISE À JOUR DU STATUT DU DOSSIER (POST) ---
if ($methode === 'POST') {
    $donnees = json_decode(file_get_contents("php://input"), true);
    
    $id_dossier = isset($donnees['id']) ? intval($donnees['id']) : 0;
    $nouveau_statut = isset($donnees['statut_dossier']) ? trim($donnees['statut_dossier']) : '';

    // Validation des valeurs de statut autorisées
    if ($id_dossier <= 0 || !in_array($nouveau_statut, ['en_attente', 'valide', 'refuse'])) {
        http_response_code(400);
        echo json_encode(["erreur" => "Données invalides ou statut non reconnu."]);
        exit();
    }

    try {
        $requete_maj = $bdd->prepare("UPDATE messages SET statut_dossier = :statut WHERE id = :id");
        $requete_maj->execute([
            'statut' => $nouveau_statut,
            'id' => $id_dossier
        ]);

        echo json_encode(["succes" => "Statut du dossier mis à jour avec succès.", "statut_dossier" => $nouveau_statut]);
    } catch (PDOException $erreur) {
        http_response_code(500);
        echo json_encode(["erreur" => "Erreur technique lors de la mise à jour du dossier."]);
    }
    exit();
}

http_response_code(405);
echo json_encode(["erreur" => "Méthode non autorisée."]);