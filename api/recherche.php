<?php
// Autorise le Front-Office local (port 8000) à interroger cette API
header("Access-Control-Allow-Origin: http://localhost:8000");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Si c'est une requête de vérification (OPTIONS), on arrête le script ici
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
    // Si un type valide est fourni, on filtre. Sinon, on renvoie tout le catalogue.
    if ($type === 'achat' || $type === 'location') {
        $requete = $bdd->prepare("SELECT id, marque, modele, type_commercial, prix, options_incluses FROM vehicules WHERE type_commercial = :type");
        $requete->execute(['type' => $type]);
    } else {
        $requete = $bdd->prepare("SELECT id, marque, modele, type_commercial, prix, options_incluses FROM vehicules");
        $requete->execute();
    }

    $vehicules = $requete->fetchAll();

    // Renvoi des données sous format JSON à l'interface utilisateur
    echo json_encode($vehicules);

} catch (PDOException $erreur) {
    http_response_code(500);
    echo json_encode(["erreur" => "Erreur lors de la recuperation du catalogue."]);
}