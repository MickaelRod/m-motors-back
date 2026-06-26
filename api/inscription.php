<?php
require_once __DIR__ . '/../config/bootstrap.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../src/validation.php';
require_once __DIR__ . '/../src/securite.php';

initialiser_cors_json();
verifier_methode('POST');

$donnees = lire_json_entrant();

$nom          = isset($donnees['nom'])          ? trim($donnees['nom'])    : '';
$email        = isset($donnees['email'])        ? trim($donnees['email'])  : '';
$telephone    = isset($donnees['telephone'])    ? trim($donnees['telephone']) : '';
$mot_de_passe = isset($donnees['mot_de_passe']) ? $donnees['mot_de_passe'] : '';

if (empty($nom) || empty($email) || empty($telephone) || empty($mot_de_passe)) {
    http_response_code(400);
    echo json_encode(["erreur" => "Tous les champs sont obligatoires pour l'inscription."]);
    exit();
}

if (!Validation::email($email)) {
    http_response_code(400);
    echo json_encode(["erreur" => "Le format de l'adresse e-mail n'est pas valide."]);
    exit();
}

if (!Validation::telephone($telephone)) {
    http_response_code(400);
    echo json_encode(["erreur" => "Le format du numéro de téléphone n'est pas valide."]);
    exit();
}

if (!Validation::motDePasse($mot_de_passe)) {
    http_response_code(400);
    echo json_encode(["erreur" => "Le mot de passe doit contenir au moins 8 caractères."]);
    exit();
}

try {
    $verification = $bdd->prepare("SELECT id FROM utilisateurs WHERE email = :email");
    $verification->execute(['email' => $email]);

    if ($verification->fetch()) {
        http_response_code(409);
        echo json_encode(["erreur" => "Cette adresse e-mail est déjà associée à un compte client."]);
        exit();
    }

    $mot_de_passe_hache = Securite::hacherMotDePasse($mot_de_passe);

    $insertion = $bdd->prepare("
        INSERT INTO utilisateurs (nom, email, telephone, mot_de_passe)
        VALUES (:nom, :email, :telephone, :mot_de_passe)
    ");
    $insertion->execute([
        'nom'          => $nom,
        'email'        => $email,
        'telephone'    => $telephone,
        'mot_de_passe' => $mot_de_passe_hache
    ]);

    echo json_encode(["succes" => "Votre compte client a été créé avec succès."]);

} catch (PDOException $erreur) {
    http_response_code(500);
    echo json_encode(["erreur" => "Erreur technique : impossible de finaliser l'inscription."]);
}
