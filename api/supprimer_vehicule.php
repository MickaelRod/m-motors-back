<?php
header("Access-Control-Allow-Origin: http://localhost:8000");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["erreur" => "Méthode non autorisée. POST attendu."]);
    exit();
}

require_once __DIR__ . '/../config/db.php';

$donnees_brutes = file_get_contents("php://input");
$donnees = json_decode($donnees_brutes, true);

$vehicule_id = isset($donnees['id']) ? intval($donnees['id']) : 0;

if ($vehicule_id <= 0) {
    http_response_code(400);
    echo json_encode(["erreur" => "L'identifiant unique du véhicule est obligatoire."]);
    exit();
}

try {
    // Vérification de l'existence du véhicule avant suppression
    $verification = $bdd->prepare("SELECT id FROM vehicules WHERE id = :id");
    $verification->execute(['id' => $vehicule_id]);
    
    if (!$verification->fetch()) {
        http_response_code(404);
        echo json_encode(["erreur" => "Aucun véhicule trouvé avec l'identifiant fourni."]);
        exit();
    }

    // Suppression définitive du véhicule du catalogue
    $requete = $bdd->prepare("DELETE FROM vehicules WHERE id = :id");
    $requete->execute(['id' => $vehicule_id]);

    echo json_encode(["succes" => "Le véhicule a été supprimé du catalogue avec succès."]);

} catch (PDOException $erreur) {
    http_response_code(500);
    echo json_encode(["erreur" => "Erreur technique : suppression du véhicule impossible."]);
}