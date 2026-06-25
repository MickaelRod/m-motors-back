<?php
// Autorise le Front-Office local à interroger cette API avec support des cookies de session
header("Access-Control-Allow-Origin: http://localhost:8000");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

// Démarrage de la session si elle n'est pas déjà active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Destruction de toutes les variables de session
$_SESSION = array();

// Destruction du cookie de session dans le navigateur
if (ini_get("session.use_cookies")) {
    $parametres = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $parametres["path"],
        $parametres["domain"],
        $parametres["secure"],
        $parametres["httponly"]
    );
}

// Destruction de la session sur le serveur
session_destroy();

echo json_encode(["succes" => "Déconnexion finalisée avec succès."]);