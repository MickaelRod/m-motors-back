<?php
// Autorise le Front-Office local (port 8000) à interroger cette API
header("Access-Control-Allow-Origin: http://localhost:8000");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["erreur" => "Méthode non autorisée. POST attendu."]);
    exit();
}

// Inclusion du fichier de connexion à la base de données
require_once __DIR__ . '/../config/db.php';

// Récupération et nettoyage des données textuelles du formulaire
$nom = isset($_POST['nom']) ? trim($_POST['nom']) : '';
$telephone = isset($_POST['telephone']) ? trim($_POST['telephone']) : '';
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$type_demande = isset($_POST['type_demande']) ? trim($_POST['type_demande']) : '';
$vehicule_id = isset($_POST['vehicule_id']) && $_POST['vehicule_id'] !== '' ? intval($_POST['vehicule_id']) : null;
$vehicule_nom = isset($_POST['vehicule_nom']) && $_POST['vehicule_nom'] !== '' ? trim($_POST['vehicule_nom']) : null;
$message = isset($_POST['message']) ? trim($_POST['message']) : '';

// Validation des champs obligatoires du formulaire
if (empty($nom) || empty($telephone) || empty($email) || empty($type_demande) || empty($message)) {
    http_response_code(400);
    echo json_encode(["erreur" => "Tous les champs obligatoires du formulaire doivent être remplis."]);
    exit();
}

// Liste des types de demandes autorisés par la structure applicative
$types_autorises = ['achat', 'financement', 'location', 'autre'];
if (!in_array($type_demande, $types_autorises)) {
    http_response_code(400);
    echo json_encode(["erreur" => "Le type de demande spécifié n'est pas valide."]);
    exit();
}

// Gestion du téléversement de la pièce justificative
$document_nom_final = null;

// Vérification des erreurs de téléversement liées aux limites du serveur
if (isset($_FILES['document'])) {
    if ($_FILES['document']['error'] === UPLOAD_ERR_INI_SIZE) {
        http_response_code(400);
        echo json_encode(["erreur" => "Le fichier dépasse la taille maximale autorisée par le serveur informatique."]);
        exit();
    }

    if ($_FILES['document']['error'] === UPLOAD_ERR_OK) {
        $fichier_temporaire = $_FILES['document']['tmp_name'];
        $fichier_nom_origine = $_FILES['document']['name'];
        $fichier_taille = $_FILES['document']['size'];
        
        $extension = strtolower(pathinfo($fichier_nom_origine, PATHINFO_EXTENSION));
        $extensions_autorisees = ['pdf', 'jpg', 'jpeg', 'png'];
        
        if (!in_array($extension, $extensions_autorisees)) {
            http_response_code(400);
            echo json_encode(["erreur" => "Format de fichier non valide (uniquement PDF, JPG, PNG)."]);
            exit();
        }
        
        if ($fichier_taille > 5 * 1024 * 1024) {
            http_response_code(400);
            echo json_encode(["erreur" => "Le fichier est trop volumineux (maximum 5 Mo)."]);
            exit();
        }
        
        $dossier_stockage = __DIR__ . '/../uploads/';
        if (!is_dir($dossier_stockage)) {
            mkdir($dossier_stockage, 0755, true);
        }
        
        $document_nom_final = uniqid('doc_', true) . '.' . $extension;
        $chemin_destination = $dossier_stockage . $document_nom_final;
        
        if (!move_uploaded_file($fichier_temporaire, $chemin_destination)) {
            http_response_code(500);
            echo json_encode(["erreur" => "Erreur technique lors de l'enregistrement de la pièce justificative."]);
            exit();
        }
    }
}

try {
    // Insertion SQL sécurisée via une requête préparée
    $requete = $bdd->prepare("
        INSERT INTO messages (nom, email, telephone, type_demande, vehicule_id, vehicule_nom, message, document_path) 
        VALUES (:nom, :email, :telephone, :type_demande, :vehicule_id, :vehicule_nom, :message, :document_path)
    ");
    
    $requete->execute([
        'nom' => $nom,
        'email' => $email,
        'telephone' => $telephone,
        'type_demande' => $type_demande,
        'vehicule_id' => $vehicule_id,
        'vehicule_nom' => $vehicule_nom,
        'message' => $message,
        'document_path' => $document_nom_final
    ]);
    
    echo json_encode(["succes" => "Votre demande a bien été transmise à notre service commercial."]);

} catch (PDOException $erreur) {
    http_response_code(500);
    echo json_encode(["erreur" => "Erreur technique : impossible d'enregistrer la demande en base de données."]);
}