<?php
// Configuration stricte des cookies de session de manière globale
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_use_only_cookies', 1);

// Autorise le Front-Office local à interroger cette API avec support des cookies de session
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

// Vérification de la présence des variables pivots de session
if (!isset($_SESSION['utilisateur_id']) || !isset($_SESSION['utilisateur_nom'])) {
    http_response_code(401); // Non autorisé
    echo json_encode(["erreur" => "Session invalide ou expirée. Accès refusé."]);
    exit();
}

// Réponse positive si la session est active et valide
echo json_encode([
    "authentifie" => true,
    "utilisateur" => [
        "id" => $_SESSION['utilisateur_id'],
        "nom" => $_SESSION['utilisateur_nom'],
        "email" => $_SESSION['utilisateur_email']
    ]
]);