<?php
require_once __DIR__ . '/../config/bootstrap.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../src/securite.php';
require_once __DIR__ . '/../src/logger.php';

initialiser_cors_json();
demarrer_session_client();
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

        // Verrou : les comptes admin ne peuvent pas se connecter sur l'espace client
        if ($utilisateur['role'] === 'admin') {
            http_response_code(403);
            echo json_encode(["erreur" => "Accès refusé. Les comptes d'administration doivent utiliser le Back-Office."]);
            exit();
        }

        session_regenerate_id(true);
        $_SESSION['utilisateur_id']        = $utilisateur['id'];
        $_SESSION['utilisateur_nom']       = $utilisateur['nom'];
        $_SESSION['utilisateur_email']     = $utilisateur['email'];
        $_SESSION['utilisateur_telephone'] = $utilisateur['telephone'];
        $_SESSION['utilisateur_role']      = $utilisateur['role'];

        Logger::info('connexion_client.php', "Connexion réussie : " . $email . " (#" . $utilisateur['id'] . ")");

        echo json_encode([
            "succes" => "Authentification réussie.",
            "utilisateur" => [
                "nom"   => $utilisateur['nom'],
                "email" => $utilisateur['email'],
                "role"  => $utilisateur['role']
            ]
        ]);
    } else {
        Logger::warning('connexion_client.php', "Tentative de connexion échouée : " . $email . ($utilisateur ? " (#" . $utilisateur['id'] . ")" : ""));
        http_response_code(401);
        echo json_encode(["erreur" => "Identifiants de connexion incorrects."]);
    }

} catch (PDOException $erreur) {
    Logger::error('connexion_client.php', "Erreur PDO : " . $erreur->getMessage());
    http_response_code(500);
    echo json_encode(["erreur" => "Erreur technique lors de l'authentification."]);
}
