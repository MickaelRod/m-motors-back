<?php
// Configuration stricte des cookies de session de manière globale (Avant le session_start)
ini_set('session.cookie_httponly', 1); // Empeche l'acces aux cookies via JavaScript (protection XSS)
ini_set('session.cookie_use_only_cookies', 1);

// Autorise le Front-Office local (port 8000) à interroger cette API avec support des cookies/sessions
header("Access-Control-Allow-Origin: http://localhost:8000");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Access-Control-Allow-Credentials: true"); // Permet le transfert des cookies de session
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["erreur" => "Methode non autorisee. POST attendu."]);
    exit();
}

// Demarrage de la session PHP sécurisée
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Inclusion de la connexion à la base de données
require_once __DIR__ . '/../config/db.php';

// Récupération des données brutes JSON
$donnees_brutes = file_get_contents("php://input");
$donnees = json_decode($donnees_brutes, true);

$email = isset($donnees['email']) ? trim($donnees['email']) : '';
$mot_de_passe = isset($donnees['mot_de_passe']) ? $donnees['mot_de_passe'] : '';

if (empty($email) || empty($mot_de_passe)) {
    http_response_code(400);
    echo json_encode(["erreur" => "L'adresse e-mail et le mot de passe sont obligatoires."]);
    exit();
}

try {
    // Recherche de l'utilisateur par son identifiant unique (email)
    $requete = $bdd->prepare("SELECT id, nom, email, telephone, mot_de_passe FROM utilisateurs WHERE email = :email");
    $requete->execute(['email' => $email]);
    $utilisateur = $requete->fetch(PDO::FETCH_ASSOC);

    // Vérification de l'existence de l'utilisateur et validation du mot de passe haché
    if (!$utilisateur || !password_verify($mot_de_passe, $utilisateur['mot_de_passe'])) {
        http_response_code(401); // Non autorise
        echo json_encode(["erreur" => "Identifiants de connexion invalides."]);
        exit();
    }

    // Régénération de l'ID de session pour contrer les attaques de fixation de session
    session_regenerate_id(true);

    // Stockage des variables pivots au sein de la session serveur
    $_SESSION['utilisateur_id'] = $utilisateur['id'];
    $_SESSION['utilisateur_nom'] = $utilisateur['nom'];
    $_SESSION['utilisateur_email'] = $utilisateur['email'];
    $_SESSION['utilisateur_telephone'] = $utilisateur['telephone'];

    // Réponse de succès avec exclusion explicite du mot de passe haché pour la sécurité
    echo json_encode([
        "succes" => "Connexion établie avec succès.",
        "utilisateur" => [
            "id" => $utilisateur['id'],
            "nom" => $utilisateur['nom'],
            "email" => $utilisateur['email']
        ]
    ]);

} catch (PDOException $erreur) {
    http_response_code(500);
    echo json_encode(["erreur" => "Erreur technique : traitement de l'authentification impossible."]);
}