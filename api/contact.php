<?php
require_once __DIR__ . '/../config/bootstrap.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../src/securite.php';

initialiser_cors_json();
demarrer_session_client();
verifier_methode('POST');

$nom          = isset($_POST['nom'])          ? trim($_POST['nom'])          : '';
$telephone    = isset($_POST['telephone'])    ? trim($_POST['telephone'])    : '';
$email        = isset($_POST['email'])        ? trim($_POST['email'])        : '';
$type_demande = isset($_POST['type_demande']) ? trim($_POST['type_demande']) : '';
$vehicule_id  = (isset($_POST['vehicule_id']) && $_POST['vehicule_id'] !== '') ? intval($_POST['vehicule_id']) : null;
$vehicule_nom = isset($_POST['vehicule_nom']) ? trim($_POST['vehicule_nom']) : '';
$message      = isset($_POST['message'])      ? trim($_POST['message'])      : '';

if (empty($nom) || empty($email) || empty($type_demande)) {
    http_response_code(400);
    echo json_encode(["erreur" => "Les champs Nom, Email et Type de demande sont obligatoires."]);
    exit();
}

$utilisateur_id = isset($_SESSION['utilisateur_id']) ? $_SESSION['utilisateur_id'] : null;

// Gestion du téléversement de la pièce jointe
$chemin_document = null;
if (isset($_FILES['document']) && $_FILES['document']['error'] === UPLOAD_ERR_OK) {
    $dossier_destination = __DIR__ . '/../uploads/';

    if (!is_dir($dossier_destination)) {
        mkdir($dossier_destination, 0755, true);
    }

    $nom_fichier_unique = Securite::genererNomFichier($_FILES['document']['name']);

    if ($nom_fichier_unique === null) {
        http_response_code(400);
        echo json_encode(["erreur" => "Format de fichier non autorisé. Formats acceptés : PDF, JPG, JPEG, PNG."]);
        exit();
    }

    if (move_uploaded_file($_FILES['document']['tmp_name'], $dossier_destination . $nom_fichier_unique)) {
        $chemin_document = 'uploads/' . $nom_fichier_unique;
    }
}

try {
    $requete = $bdd->prepare("
        INSERT INTO messages (utilisateur_id, nom, telephone, email, type_demande, vehicule_id, vehicule_nom, message, document_path, statut_dossier, cree_le)
        VALUES (:utilisateur_id, :nom, :telephone, :email, :type_demande, :vehicule_id, :vehicule_nom, :message, :document_path, 'en_attente', NOW())
    ");
    $requete->execute([
        'utilisateur_id' => $utilisateur_id,
        'nom'            => $nom,
        'telephone'      => $telephone,
        'email'          => $email,
        'type_demande'   => $type_demande,
        'vehicule_id'    => $vehicule_id,
        'vehicule_nom'   => !empty($vehicule_nom) ? $vehicule_nom : 'Non spécifié',
        'message'        => $message,
        'document_path'  => $chemin_document
    ]);

    echo json_encode(["succes" => "Votre demande de dossier a été enregistrée avec succès."]);

} catch (PDOException $erreur) {
    http_response_code(500);
    echo json_encode(["erreur" => "Erreur technique lors de l'enregistrement de votre demande."]);
}