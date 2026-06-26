<?php
require_once __DIR__ . '/../config/bootstrap.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../src/validation.php';

initialiser_cors_json();
demarrer_session_admin();
exiger_admin();
verifier_methode('POST');

$donnees          = lire_json_entrant();
$vehicule_id      = isset($donnees['id'])               ? intval($donnees['id'])              : 0;
$marque           = isset($donnees['marque'])            ? trim($donnees['marque'])            : '';
$modele           = isset($donnees['modele'])            ? trim($donnees['modele'])            : '';
$type_commercial  = isset($donnees['type_commercial'])   ? trim($donnees['type_commercial'])   : '';
$prix_achat       = (isset($donnees['prix_achat'])       && $donnees['prix_achat'] !== '')     ? intval($donnees['prix_achat'])    : null;
$prix_location    = (isset($donnees['prix_location'])    && $donnees['prix_location'] !== '')  ? intval($donnees['prix_location']) : null;
$options_incluses = isset($donnees['options_incluses'])  ? trim($donnees['options_incluses'])  : null;

if ($vehicule_id <= 0 || empty($marque) || empty($modele) || empty($type_commercial)) {
    http_response_code(400);
    echo json_encode(["erreur" => "Tous les champs obligatoires doivent être valides."]);
    exit();
}

if (!Validation::typeCommercial($type_commercial)) {
    http_response_code(400);
    echo json_encode(["erreur" => "Le type commercial doit être 'achat' ou 'location'."]);
    exit();
}

try {
    $requete = $bdd->prepare("
        UPDATE vehicules
        SET marque = :marque,
            modele = :modele,
            type_commercial = :type_commercial,
            prix_achat = :prix_achat,
            prix_location = :prix_location,
            options_incluses = :options_incluses
        WHERE id = :id
    ");
    $requete->execute([
        'marque'           => $marque,
        'modele'           => $modele,
        'type_commercial'  => $type_commercial,
        'prix_achat'       => $prix_achat,
        'prix_location'    => $prix_location,
        'options_incluses' => ($type_commercial === 'location') ? $options_incluses : null,
        'id'               => $vehicule_id
    ]);

    echo json_encode(["succes" => "La fiche du véhicule a été mise à jour avec succès."]);

} catch (PDOException $erreur) {
    http_response_code(500);
    echo json_encode(["erreur" => "Erreur technique : modification impossible."]);
}
