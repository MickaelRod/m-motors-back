<?php
// Autorise le Front-Office local à interroger cette API avec le support des cookies
header("Access-Control-Allow-Origin: http://localhost:8000");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["erreur" => "Méthode non autorisée. POST attendu."]);
    exit();
}

// Inclusion de la connexion à la base de données
require_once __DIR__ . '/../config/db.php';

// Récupération des données brutes JSON
$donnees_brutes = file_get_contents("php://input");
$donnees = json_decode($donnees_brutes, true);

$vehicule_id = isset($donnees['id']) ? intval($donnees['id']) : 0;

if ($vehicule_id <= 0) {
    http_response_code(400);
    echo json_encode(["erreur" => "L'identifiant unique du véhicule est obligatoire et doit être valide."]);
    exit();
}

try {
    // Récupération du type commercial actuel du véhicule
    $requete_selection = $bdd->prepare("SELECT type_commercial FROM vehicules WHERE id = :id");
    $requete_selection->execute(['id' => $vehicule_id]);
    $vehicule = $requete_selection->fetch(PDO::FETCH_ASSOC);

    if (!$vehicule) {
        http_response_code(404);
        echo json_encode(["erreur" => "Aucun véhicule trouvé avec l'identifiant fourni."]);
        exit();
    }

    // Détermination du nouveau statut commercial
    $nouveau_statut = ($vehicule['type_commercial'] === 'achat') ? 'location' : 'achat';

    // Mise à jour du statut commercial en base de données
    $requete_mise_a_jour = $bdd->prepare("UPDATE vehicules SET type_commercial = :nouveau_statut WHERE id = :id");
    $requete_mise_a_jour->execute([
        'nouveau_statut' => $nouveau_statut,
        'id'             => $vehicule_id
    ]);

    echo json_encode([
        "succes" => "Le statut commercial du véhicule a été modifié avec succès.",
        "nouveau_statut" => $nouveau_statut
    ]);

} catch (PDOException $erreur) {
    http_response_code(500);
    echo json_encode(["erreur" => "Erreur technique : mise à jour du catalogue impossible."]);
}