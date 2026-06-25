<?php
// Configuration stricte des cookies de session de manière globale avant tout démarrage
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_use_only_cookies', 1);

// Autorisation du Front-Office local avec support explicite des cookies de session
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

// Alignement sur la session du Front-Office pour lier le message au bon client connecté
session_name('MMOTORS_FRONT_SESSION');
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Inclusion de la connexion à la base de données
require_once __DIR__ . '/../config/db.php';

// Récupération des données depuis la superglobale $_POST (format multipart/form-data)
$nom = isset($_POST['nom']) ? trim($_POST['nom']) : '';
$telephone = isset($_POST['telephone']) ? trim($_POST['telephone']) : '';
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$type_demande = isset($_POST['type_demande']) ? trim($_POST['type_demande']) : '';
$vehicule_id = isset($_POST['vehicule_id']) && $_POST['vehicule_id'] !== '' ? intval($_POST['vehicule_id']) : null;
$vehicule_nom = isset($_POST['vehicule_nom']) ? trim($_POST['vehicule_nom']) : '';
$message = isset($_POST['message']) ? trim($_POST['message']) : '';

// Validation des champs strictement obligatoires
if (empty($nom) || empty($email) || empty($type_demande)) {
    http_response_code(400);
    echo json_encode(["erreur" => "Les champs Nom, Email et Type de demande sont obligatoires."]);
    exit();
}

// Récupération de l'identifiant utilisateur si la session serveur est active
$utilisateur_id = isset($_SESSION['utilisateur_id']) ? $_SESSION['utilisateur_id'] : null;

// Gestion du téléchargement du fichier (pièce jointe)
$chemin_document = null;
if (isset($_FILES['document']) && $_FILES['document']['error'] === UPLOAD_ERR_OK) {
    $dossier_destination = __DIR__ . '/../uploads/';
    
    // Création du dossier s'il n'existe pas
    if (!is_dir($dossier_destination)) {
        mkdir($dossier_destination, 0755, true);
    }

    $nom_fichier_origine = basename($_FILES['document']['name']);
    $extension = strtolower(pathinfo($nom_fichier_origine, PATHINFO_EXTENSION));
    
    // Génération d'un nom de fichier unique pour éviter les écrasements
    $nom_fichier_unique = uniqid('doc_', true) . '.' . $extension;
    $chemin_complet = $dossier_destination . $nom_fichier_unique;

    // Utilisation de la clé globale correcte de l'environnement PHP (tmp_name)
    if (move_uploaded_file($_FILES['document']['tmp_name'], $chemin_complet)) {
        $chemin_document = 'uploads/' . $nom_fichier_unique;
    }
}

try {
    // Préparation de la requête d'insertion respectant scrupuleusement les colonnes de la base de données
    $requete = $bdd->prepare("
        INSERT INTO messages (utilisateur_id, nom, telephone, email, type_demande, vehicule_id, vehicule_nom, message, document_path, statut_dossier, cree_le) 
        VALUES (:utilisateur_id, :nom, :telephone, :email, :type_demande, :vehicule_id, :vehicule_nom, :message, :document_path, 'En cours d\'étude', NOW())
    ");

    // Exécution sécurisée avec liaison de l'intégralité des paramètres
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