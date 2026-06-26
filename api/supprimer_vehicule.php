<?php
require_once __DIR__ . '/../config/bootstrap.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../src/logger.php';

initialiser_cors_json();
demarrer_session_admin();
exiger_admin();
verifier_methode('POST');

$donnees     = lire_json_entrant();
$vehicule_id = isset($donnees['id']) ? intval($donnees['id']) : 0;

if ($vehicule_id <= 0) {
    http_response_code(400);
    echo json_encode(["erreur" => "L'identifiant unique du véhicule est obligatoire."]);
    exit();
}

try {
    $verification = $bdd->prepare("SELECT id FROM vehicules WHERE id = :id");
    $verification->execute(['id' => $vehicule_id]);

    if (!$verification->fetch()) {
        http_response_code(404);
        echo json_encode(["erreur" => "Aucun véhicule trouvé avec l'identifiant fourni."]);
        exit();
    }

    $requete = $bdd->prepare("DELETE FROM vehicules WHERE id = :id");
    $requete->execute(['id' => $vehicule_id]);

    Logger::info('supprimer_vehicule.php', "Véhicule #" . $vehicule_id . " supprimé du catalogue.");
    echo json_encode(["succes" => "Le véhicule a été supprimé du catalogue avec succès."]);

} catch (PDOException $erreur) {
    Logger::error('supprimer_vehicule.php', "Erreur PDO : " . $erreur->getMessage());
    http_response_code(500);
    echo json_encode(["erreur" => "Erreur technique : suppression du véhicule impossible."]);
}
