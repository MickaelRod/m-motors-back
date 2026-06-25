<?php
// Autorise le Front-Office local (port 8000) à interroger cette API
header("Access-Control-Allow-Origin: http://localhost:8000");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["erreur" => "Méthode non autorisée. POST attendu."]);
    exit();
}

// Inclusion du fichier de connexion à la base de données
require_once __DIR__ . '/../config/db.php';

// Récupération des données brutes de la requête JSON
$donnees_brutes = file_get_contents("php://input");
$donnees = json_decode($donnees_brutes, true);

// Extraction et nettoyage des variables
$nom = isset($donnees['nom']) ? trim($donnees['nom']) : '';
$email = isset($donnees['email']) ? trim($donnees['email']) : '';
$telephone = isset($donnees['telephone']) ? trim($donnees['telephone']) : '';
$mot_de_passe = isset($donnees['mot_de_passe']) ? $donnees['mot_de_passe'] : '';

// Validation des champs obligatoires
if (empty($nom) || empty($email) || empty($telephone) || empty($mot_de_passe)) {
    http_response_code(400);
    echo json_encode(["erreur" => "Tous les champs sont obligatoires pour l'inscription."]);
    exit();
}

// Validation du format de l'adresse e-mail
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(["erreur" => "Le format de l'adresse e-mail n'est pas valide."]);
    exit();
}

// Validation du format du numéro de téléphone (regex identique au Front)
if (!preg_match("/^\+?[0-9\s\-]{8,20}$/", $telephone)) {
    http_response_code(400);
    echo json_encode(["erreur" => "Le format du numéro de téléphone n'est pas valide."]);
    exit();
}

// Validation de la complexité du mot de passe (sécurité minimale : 8 caractères)
if (strlen($mot_de_passe) < 8) {
    http_response_code(400);
    echo json_encode(["erreur" => "Le mot de passe doit contenir au moins 8 caractères."]);
    exit();
}

try {
    // Vérification de l'existence de l'adresse e-mail en base de données
    $verification = $bdd->prepare("SELECT id FROM utilisateurs WHERE email = :email");
    $verification->execute(['email' => $email]);
    
    if ($verification->fetch()) {
        http_response_code(409); // Conflit
        echo json_encode(["erreur" => "Cette adresse e-mail est déjà associée à un compte client."]);
        exit();
    }

    // Hachage sécurisé du mot de passe
    $mot_de_passe_hache = password_hash($mot_de_passe, PASSWORD_DEFAULT);

    // Insertion du nouvel utilisateur
    $insertion = $bdd->prepare("
        INSERT INTO utilisateurs (nom, email, telephone, mot_de_passe) 
        VALUES (:nom, :email, :telephone, :mot_de_passe)
    ");
    
    $insertion->execute([
        'nom' => $nom,
        'email' => $email,
        'telephone' => $telephone,
        'mot_de_passe' => $mot_de_passe_hache
    ]);

    echo json_encode(["succes" => "Votre compte client a été créé avec succès."]);

} catch (PDOException $erreur) {
    http_response_code(500);
    echo json_encode(["erreur" => "Erreur technique : impossible de finaliser l'inscription."]);
}