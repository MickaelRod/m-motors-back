<?php
require_once __DIR__ . '/../config/bootstrap.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../src/validation.php';

initialiser_cors_json();

$type = isset($_GET['type']) ? trim($_GET['type']) : '';

try {
    if (Validation::typeCommercial($type)) {
        $requete = $bdd->prepare("SELECT id, marque, modele, prix_achat, prix_location, type_commercial, options_incluses FROM vehicules WHERE type_commercial = :type");
        $requete->execute(['type' => $type]);
    } else {
        $requete = $bdd->prepare("SELECT id, marque, modele, prix_achat, prix_location, type_commercial, options_incluses FROM vehicules");
        $requete->execute();
    }
    echo json_encode($requete->fetchAll(PDO::FETCH_ASSOC));

} catch (PDOException $erreur) {
    http_response_code(500);
    echo json_encode(["erreur" => "Erreur lors de la récupération du catalogue."]);
}
