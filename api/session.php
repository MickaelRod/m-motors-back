<?php
$origine = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : 'http://localhost:8001';
header("Access-Control-Allow-Origin: " . $origine);
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

// Détection de la provenance pour attribuer le cookie correct
$demandeDepuisFront = (strpos($origine, ':8000') !== false) || (isset($_SERVER['SERVER_NAME']) && strpos($_SERVER['REQUEST_URI'], '/back/') === false && $origine !== 'http://localhost:8001');

if ($demandeDepuisFront) {
    session_name('MMOTORS_FRONT_SESSION');
} else {
    session_name('MMOTORS_BACK_SESSION');
}

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (isset($_SESSION['utilisateur_id'])) {
    echo json_encode([
        "connecte" => true,
        "utilisateur" => [
            "id"        => $_SESSION['utilisateur_id'],
            "nom"       => $_SESSION['utilisateur_nom'],
            "email"     => $_SESSION['utilisateur_email'],
            "telephone" => $_SESSION['utilisateur_telephone'],
            "role"      => isset($_SESSION['utilisateur_role']) ? $_SESSION['utilisateur_role'] : 'client'
        ]
    ]);
} else {
    http_response_code(401);
    echo json_encode([
        "connecte" => false,
        "erreur" => "Aucune session active ou utilisateur non connecté."
    ]);
}