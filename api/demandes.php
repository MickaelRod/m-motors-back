<?php
// Configuration stricte des cookies de session de manière globale
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_use_only_cookies', 1);

// Autorise le Front-Office local à interroger cette API avec le support des cookies
header("Access-Control-Allow-Origin: http://localhost:8000");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

// Démarrage de la session PHP sécurisée
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Vérification de l'authentification de l'utilisateur
if (!isset($_SESSION['utilisateur_id'])) {
    http_response_code(401);
    echo json_encode(["erreur" => "Accès non autorisé. Veuillez vous connecter."]);
    exit();
}

// Inclusion de la connexion à la base de données
require_once __DIR__ . '/../config/db.php';

$utilisateur_id = $_SESSION['utilisateur_id'];

try {
    // Récupération des demandes associées à l'utilisateur connecté
    // Le tri est effectué de la plus récente à la plus ancienne
    $requete = $bdd->prepare("
        SELECT id, type_demande, vehicule_nom, cree_le, statut_dossier AS statut 
        FROM messages 
        WHERE utilisateur_id = :utilisateur_id 
        ORDER BY cree_le DESC
    ");
    $requete->execute(['utilisateur_id' => $utilisateur_id]);
    $demandes = $requete->fetchAll(PDO::FETCH_ASSOC);

    // Retourne la liste des demandes, même si elle est vide
    echo json_encode(["demandes" => $demandes]);

} catch (PDOException $erreur) {
    http_response_code(500);
    echo json_encode(["erreur" => "Erreur technique lors de la récupération des dossiers."]);
}