<?php
/**
 * Amorçage commun à tous les endpoints de l'API.
 * Centralise les headers CORS, la configuration des sessions et les gardes d'accès.
 */

// --- CORS et Content-Type ---

/**
 * Émet les headers CORS nécessaires et gère les requêtes preflight OPTIONS.
 * Doit être appelée en tout premier dans chaque endpoint.
 */
function initialiser_cors_json(): void
{
    $origine = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';
    if ($origine !== '') {
        header("Access-Control-Allow-Origin: " . $origine);
    }
    header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
    header("Access-Control-Allow-Credentials: true");
    header("Content-Type: application/json; charset=UTF-8");

    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        exit(0);
    }
}

// --- Vérification de la méthode HTTP ---

/**
 * Interrompt la requête avec une erreur 405 si la méthode HTTP ne correspond pas.
 */
function verifier_methode(string $methode_attendue): void
{
    if ($_SERVER['REQUEST_METHOD'] !== strtoupper($methode_attendue)) {
        http_response_code(405);
        echo json_encode(["erreur" => "Méthode non autorisée. " . strtoupper($methode_attendue) . " attendu."]);
        exit();
    }
}

// --- Lecture du corps de la requête ---

/**
 * Lit et décode le corps JSON de la requête entrante.
 * Retourne un tableau associatif (vide si le corps est absent ou invalide).
 */
function lire_json_entrant(): array
{
    $brut = file_get_contents("php://input");
    $donnees = json_decode($brut, true);
    return is_array($donnees) ? $donnees : [];
}

// --- Gestion des sessions ---

/**
 * Configure et démarre la session réservée au Back-Office (administrateurs).
 */
function demarrer_session_admin(): void
{
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_samesite', 'Lax');
    ini_set('session.cookie_use_only_cookies', 1);
    session_name('MMOTORS_BACK_SESSION');
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

/**
 * Configure et démarre la session réservée au Front-Office (clients).
 */
function demarrer_session_client(): void
{
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_samesite', 'Lax');
    ini_set('session.cookie_use_only_cookies', 1);
    session_name('MMOTORS_FRONT_SESSION');
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

// --- Gardes d'accès ---

/**
 * Vérifie que la session admin est active.
 * Interrompt la requête avec une erreur 403 si ce n'est pas le cas.
 */
function exiger_admin(): void
{
    if (!isset($_SESSION['utilisateur_role']) || $_SESSION['utilisateur_role'] !== 'admin') {
        http_response_code(403);
        echo json_encode(["erreur" => "Accès interdit. Droits d'administration requis."]);
        exit();
    }
}

/**
 * Vérifie que la session client est active.
 * Interrompt la requête avec une erreur 401 si ce n'est pas le cas.
 */
function exiger_client(): void
{
    if (!isset($_SESSION['utilisateur_id'])) {
        http_response_code(401);
        echo json_encode(["erreur" => "Accès non autorisé. Veuillez vous connecter."]);
        exit();
    }
}
