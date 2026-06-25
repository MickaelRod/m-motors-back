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

require_once __DIR__ . '/../config/db.php';

$type = isset($_GET['type']) ? trim($_GET['type']) : '';

try {
    if ($type === 'achat' || $type === 'location') {
        $requete = $bdd->prepare("SELECT id, marque, modele, prix_achat, prix_location, type_commercial, options_incluses FROM vehicules WHERE type_commercial = :type");
        $requete->execute(['type' => $type]);
    } else {
        $requete = $bdd->prepare("SELECT id, marque, modele, prix_achat, prix_location, type_commercial, options_incluses FROM vehicules");
        $requete->execute();
    }
    echo json_encode($requete->fetchAll(PDO::FETCH_ASSOC));
} catch (PDOException $erreur) {
    http_response_code(500);
    echo json_encode(["erreur" => "Erreur lors de la récupération du catalogue."]);
}