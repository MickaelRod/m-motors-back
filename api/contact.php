<?php
// Autorise le Front-Office local (port 8000) à interroger cette API
header("Access-Control-Allow-Origin: http://localhost:8000");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Content-Type: application/json; charset=UTF-8");

// Si c'est une requête de vérification (OPTIONS), on arrête ici
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

// Vérification stricte de la méthode POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["erreur" => "Méthode non autorisée. POST attendu."]);
    exit();
}

// Inclusion de la connexion BDD
require_once __DIR__ . '/../config/db.php';

// Récupération et nettoyage élémentaire des données textuelles
$nom = isset($_POST['nom']) ? trim($_POST['nom']) : '';
$telephone = isset($_POST['telephone']) ? trim($_POST['telephone']) : '';
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$sujet = isset($_POST['sujet']) ? trim($_POST['sujet']) : '';
$message = isset($_POST['message']) ? trim($_POST['message']) : '';

// Validation des champs obligatoires
if (empty($nom) || empty($telephone) || empty($email) || empty($sujet) || empty($message)) {
    http_response_code(400);
    echo json_encode(["erreur" => "Tous les champs du formulaire sont obligatoires."]);
    exit();
}

// Gestion de la pièce justificative (Upload)
$document_nom_final = null;

if (isset($_FILES['document']) && $_FILES['document']['error'] === UPLOAD_ERR_OK) {
    $fichier_temporaire = $_FILES['document']['tmp_name'];
    $fichier_nom_origine = $_FILES['document']['name'];
    $fichier_taille = $_FILES['document']['size'];
    
    // Validation de l'extension
    $extension = strtolower(pathinfo($fichier_nom_origine, PATHINFO_EXTENSION));
    $extensions_autorisees = ['pdf', 'jpg', 'jpeg', 'png'];
    
    if (!in_array($extension, $extensions_autorisees)) {
        http_response_code(400);
        echo json_encode(["erreur" => "Format de fichier non valide (uniquement PDF, JPG, PNG)."]);
        exit();
    }
    
    // Validation de la taille (Limite à 5 Mo pour le serveur)
    if ($fichier_taille > 5 * 1024 * 1024) {
        http_response_code(400);
        echo json_encode(["erreur" => "Le fichier est trop volumineux (maximum 5 Mo)."]);
        exit();
    }
    
    // Création du dossier de stockage sécurisé s'il n'existe pas
    $dossier_stockage = __DIR__ . '/../uploads/';
    if (!is_dir($dossier_stockage)) {
        mkdir($dossier_stockage, 0755, true);
    }
    
    // Génération d'un nom unique pour éviter les collisions et écrasements
    $document_nom_final = uniqid('doc_', true) . '.' . $extension;
    $chemin_destination = $dossier_stockage . $document_nom_final;
    
    // Déplacement physique du fichier temporaire vers le dossier final
    if (!move_uploaded_file($fichier_temporaire, $chemin_destination)) {
        http_response_code(500);
        echo json_encode(["erreur" => "Erreur technique lors de l'enregistrement de la pièce justificative."]);
        exit();
    }
}

try {
    // Insertion propre des données en base de données avec requête préparée
    $requete = $bdd->prepare("
        INSERT INTO messages (nom, email, telephone, sujet, message, document_path) 
        VALUES (:nom, :email, :telephone, :sujet, :message, :document_path)
    ");
    
    $requete->execute([
        'nom' => $nom,
        'email' => $email,
        'telephone' => $telephone,
        'sujet' => $sujet,
        'message' => $message,
        'document_path' => $document_nom_final
    ]);
    
    echo json_encode(["succes" => "Votre demande a bien été transmise à notre service commercial."]);

} catch (PDOException $erreur) {
    http_response_code(500);
    echo json_encode(["erreur" => "Erreur technique : impossible d'enregistrer la demande en base de données."]);
}