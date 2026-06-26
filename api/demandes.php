<?php
require_once __DIR__ . '/../config/bootstrap.php';
require_once __DIR__ . '/../config/db.php';

initialiser_cors_json();
demarrer_session_client();
exiger_client();

$utilisateur_id    = $_SESSION['utilisateur_id'];
$utilisateur_email = $_SESSION['utilisateur_email'];

try {
    $requete = $bdd->prepare("
        SELECT id, type_demande, vehicule_nom, cree_le, statut_dossier AS statut
        FROM messages
        WHERE utilisateur_id = :utilisateur_id OR email = :utilisateur_email
        ORDER BY cree_le DESC
    ");
    $requete->execute([
        'utilisateur_id'    => $utilisateur_id,
        'utilisateur_email' => $utilisateur_email
    ]);
    echo json_encode(["demandes" => $requete->fetchAll(PDO::FETCH_ASSOC)]);

} catch (PDOException $erreur) {
    http_response_code(500);
    echo json_encode(["erreur" => "Erreur technique lors de la récupération des dossiers."]);
}