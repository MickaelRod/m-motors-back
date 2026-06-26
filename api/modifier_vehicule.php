<?php
$origine = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : 'http://localhost:8001';
header("Access-Control-Allow-Origin: " . $origine);
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

// Vérification de la session administrateur
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_samesite', 'Lax');
session_name('MMOTORS_BACK_SESSION');
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['utilisateur_role']) || $_SESSION['utilisateur_role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(["erreur" => "Accès interdit. Droits d'administration requis."]);
    exit();
}

require_once __DIR__ . '/../config/db.php';

$donnees_brutes = file_get_contents("php://input");
$donnees = json_decode($donnees_brutes, true);

$vehicule_id = isset($donnees['id']) ? intval($donnees['id']) : 0;
$marque = isset($donnees['marque']) ? trim($donnees['marque']) : '';
$modele = isset($donnees['modele']) ? trim($donnees['modele']) : '';
$type_commercial = isset($donnees['type_commercial']) ? trim($donnees['type_commercial']) : '';
$prix_achat = (isset($donnees['prix_achat']) && $donnees['prix_achat'] !== '') ? intval($donnees['prix_achat']) : null;
$prix_location = (isset($donnees['prix_location']) && $donnees['prix_location'] !== '') ? intval($donnees['prix_location']) : null;
$options_incluses = isset($donnees['options_incluses']) ? trim($donnees['options_incluses']) : null;

if ($vehicule_id <= 0 || empty($marque) || empty($modele) || empty($type_commercial)) {
    http_response_code(400);
    echo json_encode(["erreur" => "Tous les champs obligatoires doivent être valides."]);
    exit();
}

if ($type_commercial !== 'achat' && $type_commercial !== 'location') {
    http_response_code(400);
    echo json_encode(["erreur" => "Le type commercial doit être 'achat' ou 'location'."]);
    exit();
}

try {
    $requete = $bdd->prepare("
        UPDATE vehicules 
        SET marque = :marque, 
            modele = :modele, 
            type_commercial = :type_commercial, 
            prix_achat = :prix_achat, 
            prix_location = :prix_location, 
            options_incluses = :options_incluses 
        WHERE id = :id
    ");

    $requete->execute([
        'marque'          => $marque,
        'modele'          => $modele,
        'type_commercial' => $type_commercial,
        'prix_achat'      => $prix_achat,
        'prix_location'   => $prix_location,
        'options_incluses'=> ($type_commercial === 'location') ? $options_incluses : null,
        'id'              => $vehicule_id
    ]);

    echo json_encode(["succes" => "La fiche du véhicule a été mise à jour avec succès."]);

} catch (PDOException $erreur) {
    http_response_code(500);
    echo json_encode(["erreur" => "Erreur technique : modification impossible."]);
}