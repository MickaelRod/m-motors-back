<?php
require_once __DIR__ . '/../config/bootstrap.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../src/securite.php';

initialiser_cors_json();
demarrer_session_admin();
verifier_methode('POST');

$donnees      = lire_json_entrant();
$email        = isset($donnees['email'])        ? trim($donnees['email'])  : '';
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

    if ($utilisateur && Securite::verifierMotDePasse($mot_de_passe, $utilisateur['mot_de_passe'])) {

        // Verrou : un client ordinaire ne peut pas accéder au Back-Office
        if ($utilisateur['role'] !== 'admin') {
            http_response_code(403);
            echo json_encode(["erreur" => "Accès interdit. Droits d'administration requis."]);
            exit();
        }

        session_regenerate_id(true);
        $_SESSION['utilisateur_id']        = $utilisateur['id'];
        $_SESSION['utilisateur_nom']       = $utilisateur['nom'];
        $_SESSION['utilisateur_email']     = $utilisateur['email'];
        $_SESSION['utilisateur_telephone'] = $utilisateur['telephone'];
        $_SESSION['utilisateur_role']      = $utilisateur['role'];

        echo json_encode([
            "succes" => "Authentification réussie.",
            "utilisateur" => [
                "nom"   => $utilisateur['nom'],
                "email" => $utilisateur['email'],
                "role"  => $utilisateur['role']
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
