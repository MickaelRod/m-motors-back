<?php
require_once __DIR__ . '/../config/bootstrap.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../src/validation.php';
require_once __DIR__ . '/../src/logger.php';

initialiser_cors_json();
demarrer_session_admin();
exiger_admin();
verifier_methode('POST');

$donnees          = lire_json_entrant();
$marque           = isset($donnees['marque'])           ? trim($donnees['marque'])           : '';
$modele           = isset($donnees['modele'])           ? trim($donnees['modele'])           : '';
$type_commercial  = isset($donnees['type_commercial'])  ? trim($donnees['type_commercial'])  : '';
$prix_achat       = (isset($donnees['prix_achat'])      && $donnees['prix_achat'] !== '')    ? intval($donnees['prix_achat'])    : null;
$prix_location    = (isset($donnees['prix_location'])   && $donnees['prix_location'] !== '') ? intval($donnees['prix_location']) : null;
$options_incluses = isset($donnees['options_incluses']) ? trim($donnees['options_incluses']) : null;

if (empty($marque) || empty($modele) || empty($type_commercial)) {
    http_response_code(400);
    echo json_encode(["erreur" => "Les champs Marque, Modèle et Type commercial sont obligatoires."]);
    exit();
}

if (!Validation::typeCommercial($type_commercial)) {
    http_response_code(400);
    echo json_encode(["erreur" => "Le type commercial doit être 'achat' ou 'location'."]);
    exit();
}

try {
    $requete = $bdd->prepare("
        INSERT INTO vehicules (marque, modele, type_commercial, prix_achat, prix_location, options_incluses)
        VALUES (:marque, :modele, :type_commercial, :prix_achat, :prix_location, :options_incluses)
    ");
    $requete->execute([
        'marque'           => $marque,
        'modele'           => $modele,
        'type_commercial'  => $type_commercial,
        'prix_achat'       => $prix_achat,
        'prix_location'    => $prix_location,
        'options_incluses' => ($type_commercial === 'location') ? $options_incluses : null
    ]);

    $id_vehicule = $bdd->lastInsertId();
    Logger::info('ajouter_vehicule.php', "Véhicule #" . $id_vehicule . " ajouté : " . $marque . " " . $modele . " (" . $type_commercial . ")");
    echo json_encode(["succes" => "Le nouveau véhicule a été ajouté avec succès au catalogue.", "id" => $id_vehicule]);

} catch (PDOException $erreur) {
    Logger::error('ajouter_vehicule.php', "Erreur PDO : " . $erreur->getMessage());
    http_response_code(500);
    echo json_encode(["erreur" => "Erreur technique : ajout du véhicule impossible."]);
}
