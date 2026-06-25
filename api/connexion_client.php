<?php
$origine = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : 'http://localhost:8000';
header("Access-Control-Allow-Origin: " . $origine);
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_samesite', 'Lax');
session_name('MMOTORS_FRONT_SESSION');

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/db.php';
   
$donnees_brutes = file_get_contents("php://input");
$donnees = json_decode($donnees_brutes, true);

$email = isset($donnees['email']) ? trim($donnees['email']) : '';
$mot_de_passe = isset($donnees['mot_de_passe']) ? $donnees['mot_de_passe'] : '';

if (empty($email) || empty($mot_de_passe)) {
    http_response_code(400);
    echo json_encode(["erreur" => "L'adresse email et le mot de passe sont obligatoires."]);
    exit();
}

try {
    $requete = $bdd->prepare("SELECT id, nom, email, telephone, mot_de_passe, role FROM utilisateurs WHERE email = :email");
    $requete->execute(['email' => $email]);
    $utilisateur = $requete->fetch(PDO::FETCH_ASSOC);

    if ($utilisateur && password_verify($mot_de_passe, $utilisateur['mot_de_passe'])) {
        session_regenerate_id(true);

        $_SESSION['utilisateur_id'] = $utilisateur['id'];
        $_SESSION['utilisateur_nom'] = $utilisateur['nom'];
        $_SESSION['utilisateur_email'] = $utilisateur['email'];
        $_SESSION['utilisateur_telephone'] = $utilisateur['telephone'];
        $_SESSION['utilisateur_role'] = $utilisateur['role'];

        echo json_encode([
            "succes" => "Authentification réussie.",
            "utilisateur" => [
                "nom" => $utilisateur['nom'],
                "email" => $utilisateur['email'],
                "role" => $utilisateur['role']
            ]
        ]);
    } else {
        http_response_code(401);
        echo json_encode(["erreur" => "Identifiants de connexion incorrects."]);
    }
} catch (PDOException $erreur) {
    http_response_code(500);
    echo json_encode(["erreur" => "Erreur technique lors de l'authentification."]);
}