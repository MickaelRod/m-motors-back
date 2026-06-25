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
    exit();
}

session_name('MMOTORS_BACK_SESSION');
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Sécurité : Interdiction si l'utilisateur n'est pas connecté ou n'est pas admin
if (!isset($_SESSION['utilisateur_role']) || $_SESSION['utilisateur_role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(["erreur" => "Accès interdit."]);
    exit();
}

require_once __DIR__ . '/../config/db.php';

$donnees = json_decode(file_get_contents("php://input"), true);
$vehicule_id = isset($donnees['id']) ? intval($donnees['id']) : 0;

if ($vehicule_id <= 0) {
    http_response_code(400);
    exit();
}

try {
    $requete_selection = $bdd->prepare("SELECT type_commercial FROM vehicules WHERE id = :id");
    $requete_selection->execute(['id' => $vehicule_id]);
    $vehicule = $requete_selection->fetch(PDO::FETCH_ASSOC);

    if (!$vehicule) {
        http_response_code(404);
        exit();
    }

    $nouveau_statut = ($vehicule['type_commercial'] === 'achat') ? 'location' : 'achat';

    $requete_mise_a_jour = $bdd->prepare("UPDATE vehicules SET type_commercial = :nouveau_statut WHERE id = :id");
    $requete_mise_a_jour->execute(['nouveau_statut' => $nouveau_statut, 'id' => $vehicule_id]);

    echo json_encode(["succes" => "Statut modifié.", "nouveau_statut" => $nouveau_statut]);
} catch (PDOException $erreur) {
    http_response_code(500);
}