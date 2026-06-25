<?php
// Autorise le Front-Office local (port 8000) à interroger cette API
header("Access-Control-Allow-Origin: http://localhost:8000");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

// Inclusion du fichier de connexion à la base de données
require_once __DIR__ . '/../config/db.php';

// Définition des entêtes pour retourner du JSON propre
header("Content-Type: application/json; charset=UTF-8");

// Récupération et sécurisation du paramètre de filtrage de l'URL
$type = isset($_GET['type']) ? trim($_GET['type']) : '';

try {
    // Sélection explicite des nouvelles colonnes de prix d'achat et de location
    if ($type === 'achat' || $type === 'location') {
        $requete = $bdd->prepare("SELECT id, marque, modele, prix_achat, prix_location, type_commercial, options_incluses FROM vehicules WHERE type_commercial = :type");
        $requete->execute(['type' => $type]);
    } else {
        $requete = $bdd->prepare("SELECT id, marque, modele, prix_achat, prix_location, type_commercial, options_incluses FROM vehicules");
        $requete->execute();
    }

    $vehicules = $requete->fetchAll();

    // Renvoi des données au format JSON
    echo json_encode($vehicules);

} catch (PDOException $erreur) {
    http_response_code(500);
    echo json_encode(["erreur" => "Erreur lors de la récupération du catalogue."]);
}