<?php
require_once __DIR__ . '/../config/bootstrap.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../src/validation.php';
require_once __DIR__ . '/../src/securite.php';

initialiser_cors_json();
demarrer_session_admin();
exiger_admin();

$methode = $_SERVER['REQUEST_METHOD'];

// --- CAS 1 : LISTE DE TOUS LES UTILISATEURS (GET) ---
if ($methode === 'GET') {
    try {
        $requete = $bdd->prepare("SELECT id, nom, email, telephone, role, cree_le FROM utilisateurs ORDER BY id DESC");
        $requete->execute();
        echo json_encode($requete->fetchAll(PDO::FETCH_ASSOC));
    } catch (PDOException $erreur) {
        http_response_code(500);
        echo json_encode(["erreur" => "Erreur lors de la récupération des utilisateurs."]);
    }
    exit();
}

// --- CAS 2 : AJOUT D'UN UTILISATEUR (POST + action=ajouter) ---
if ($methode === 'POST') {
    $donnees = lire_json_entrant();
    $action  = isset($donnees['action']) ? $donnees['action'] : '';

    if ($action === 'ajouter') {
        $nom          = isset($donnees['nom'])          ? trim($donnees['nom'])          : '';
        $email        = isset($donnees['email'])        ? trim($donnees['email'])        : '';
        $telephone    = isset($donnees['telephone'])    ? trim($donnees['telephone'])    : '';
        $mot_de_passe = isset($donnees['mot_de_passe']) ? $donnees['mot_de_passe']       : '';
        $role         = isset($donnees['role'])         ? trim($donnees['role'])         : 'client';

        if (empty($nom) || empty($email) || empty($telephone) || empty($mot_de_passe)) {
            http_response_code(400);
            echo json_encode(["erreur" => "Tous les champs sont obligatoires."]);
            exit();
        }
        if (!Validation::email($email)) {
            http_response_code(400);
            echo json_encode(["erreur" => "Format d'adresse e-mail invalide."]);
            exit();
        }
        if (!Validation::telephone($telephone)) {
            http_response_code(400);
            echo json_encode(["erreur" => "Format de numéro de téléphone invalide."]);
            exit();
        }
        if (!Validation::motDePasse($mot_de_passe)) {
            http_response_code(400);
            echo json_encode(["erreur" => "Le mot de passe doit contenir au moins 8 caractères."]);
            exit();
        }
        if (!in_array($role, ['client', 'admin'], true)) {
            http_response_code(400);
            echo json_encode(["erreur" => "Rôle invalide."]);
            exit();
        }

        try {
            $verification = $bdd->prepare("SELECT id FROM utilisateurs WHERE email = :email");
            $verification->execute(['email' => $email]);
            if ($verification->fetch()) {
                http_response_code(409);
                echo json_encode(["erreur" => "Cette adresse e-mail est déjà utilisée."]);
                exit();
            }

            $requete = $bdd->prepare("
                INSERT INTO utilisateurs (nom, email, telephone, mot_de_passe, role)
                VALUES (:nom, :email, :telephone, :mot_de_passe, :role)
            ");
            $requete->execute([
                'nom'          => $nom,
                'email'        => $email,
                'telephone'    => $telephone,
                'mot_de_passe' => Securite::hacherMotDePasse($mot_de_passe),
                'role'         => $role
            ]);
            echo json_encode(["succes" => "Utilisateur créé avec succès.", "id" => $bdd->lastInsertId()]);
        } catch (PDOException $erreur) {
            http_response_code(500);
            echo json_encode(["erreur" => "Erreur technique lors de la création de l'utilisateur."]);
        }
        exit();
    }

    if ($action === 'modifier') {
        $id           = isset($donnees['id'])           ? intval($donnees['id'])          : 0;
        $nom          = isset($donnees['nom'])          ? trim($donnees['nom'])           : '';
        $email        = isset($donnees['email'])        ? trim($donnees['email'])         : '';
        $telephone    = isset($donnees['telephone'])    ? trim($donnees['telephone'])     : '';
        $role         = isset($donnees['role'])         ? trim($donnees['role'])          : '';
        $mot_de_passe = isset($donnees['mot_de_passe']) ? $donnees['mot_de_passe']        : '';

        if ($id <= 0 || empty($nom) || empty($email) || empty($telephone) || empty($role)) {
            http_response_code(400);
            echo json_encode(["erreur" => "Tous les champs sont obligatoires."]);
            exit();
        }
        if (!Validation::email($email)) {
            http_response_code(400);
            echo json_encode(["erreur" => "Format d'adresse e-mail invalide."]);
            exit();
        }
        if (!Validation::telephone($telephone)) {
            http_response_code(400);
            echo json_encode(["erreur" => "Format de numéro de téléphone invalide."]);
            exit();
        }
        if (!in_array($role, ['client', 'admin'], true)) {
            http_response_code(400);
            echo json_encode(["erreur" => "Rôle invalide."]);
            exit();
        }
        if (!empty($mot_de_passe) && !Validation::motDePasse($mot_de_passe)) {
            http_response_code(400);
            echo json_encode(["erreur" => "Le mot de passe doit contenir au moins 8 caractères."]);
            exit();
        }

        try {
            // Vérification d'unicité de l'email (hors l'utilisateur lui-même)
            $verification = $bdd->prepare("SELECT id FROM utilisateurs WHERE email = :email AND id != :id");
            $verification->execute(['email' => $email, 'id' => $id]);
            if ($verification->fetch()) {
                http_response_code(409);
                echo json_encode(["erreur" => "Cette adresse e-mail est déjà utilisée par un autre compte."]);
                exit();
            }

            if (!empty($mot_de_passe)) {
                // Mise à jour avec nouveau mot de passe rehâché
                $requete = $bdd->prepare("UPDATE utilisateurs SET nom=:nom, email=:email, telephone=:telephone, role=:role, mot_de_passe=:mot_de_passe WHERE id=:id");
                $requete->execute([
                    'nom' => $nom, 'email' => $email, 'telephone' => $telephone,
                    'role' => $role, 'mot_de_passe' => Securite::hacherMotDePasse($mot_de_passe), 'id' => $id
                ]);
            } else {
                // Mise à jour sans toucher au mot de passe
                $requete = $bdd->prepare("UPDATE utilisateurs SET nom=:nom, email=:email, telephone=:telephone, role=:role WHERE id=:id");
                $requete->execute(['nom' => $nom, 'email' => $email, 'telephone' => $telephone, 'role' => $role, 'id' => $id]);
            }
            echo json_encode(["succes" => "Utilisateur mis à jour avec succès."]);
        } catch (PDOException $erreur) {
            http_response_code(500);
            echo json_encode(["erreur" => "Erreur technique lors de la modification."]);
        }
        exit();
    }

    if ($action === 'supprimer') {
        $id = isset($donnees['id']) ? intval($donnees['id']) : 0;
        if ($id <= 0) {
            http_response_code(400);
            echo json_encode(["erreur" => "Identifiant invalide."]);
            exit();
        }
        try {
            $verification = $bdd->prepare("SELECT id FROM utilisateurs WHERE id = :id");
            $verification->execute(['id' => $id]);
            if (!$verification->fetch()) {
                http_response_code(404);
                echo json_encode(["erreur" => "Utilisateur introuvable."]);
                exit();
            }
            $requete = $bdd->prepare("DELETE FROM utilisateurs WHERE id = :id");
            $requete->execute(['id' => $id]);
            echo json_encode(["succes" => "Utilisateur supprimé avec succès."]);
        } catch (PDOException $erreur) {
            http_response_code(500);
            echo json_encode(["erreur" => "Erreur technique lors de la suppression."]);
        }
        exit();
    }

    http_response_code(400);
    echo json_encode(["erreur" => "Action non reconnue."]);
    exit();
}

http_response_code(405);
echo json_encode(["erreur" => "Méthode non autorisée."]);
