<?php
require_once __DIR__ . '/../config/bootstrap.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../src/vehicule.php';

initialiser_cors_json();
demarrer_session_admin();
exiger_admin();
verifier_methode('POST');

$donnees     = lire_json_entrant();
$vehicule_id = isset($donnees['id']) ? intval($donnees['id']) : 0;

if ($vehicule_id <= 0) {
    http_response_code(400);
    echo json_encode(["erreur" => "L'identifiant du véhicule est obligatoire."]);
    exit();
}

try {
    $requete_selection = $bdd->prepare("SELECT type_commercial FROM vehicules WHERE id = :id");
    $requete_selection->execute(['id' => $vehicule_id]);
    $vehicule = $requete_selection->fetch(PDO::FETCH_ASSOC);

    if (!$vehicule) {
        http_response_code(404);
        echo json_encode(["erreur" => "Véhicule introuvable."]);
        exit();
    }

    $nouveau_statut = Vehicule::basculerType($vehicule['type_commercial']);

    if ($nouveau_statut === null) {
        http_response_code(400);
        echo json_encode(["erreur" => "Type commercial du véhicule non reconnu."]);
        exit();
    }

    $requete_mise_a_jour = $bdd->prepare("UPDATE vehicules SET type_commercial = :nouveau_statut WHERE id = :id");
    $requete_mise_a_jour->execute(['nouveau_statut' => $nouveau_statut, 'id' => $vehicule_id]);

    echo json_encode(["succes" => "Statut modifié.", "nouveau_statut" => $nouveau_statut]);

} catch (PDOException $erreur) {
    http_response_code(500);
    echo json_encode(["erreur" => "Erreur technique lors de la bascule du statut."]);
}
