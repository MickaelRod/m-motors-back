<?php
require_once __DIR__ . '/../config/bootstrap.php';

initialiser_cors_json();
demarrer_session_client();

if (isset($_SESSION['utilisateur_id'])) {
    echo json_encode([
        "connecte" => true,
        "utilisateur" => [
            "id"        => $_SESSION['utilisateur_id'],
            "nom"       => $_SESSION['utilisateur_nom'],
            "email"     => $_SESSION['utilisateur_email'],
            "telephone" => $_SESSION['utilisateur_telephone'],
            "role"      => $_SESSION['utilisateur_role'] ?? 'client'
        ]
    ]);
} else {
    http_response_code(401);
    echo json_encode([
        "connecte" => false,
        "erreur"   => "Aucune session active ou utilisateur non connecté."
    ]);
}
