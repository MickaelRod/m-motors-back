<?php
/**
 * Règles de validation des données métier de l'application M-Motors.
 * Toutes les méthodes sont statiques et sans dépendance externe (testables unitairement).
 */
class Validation
{
    /**
     * Vérifie le format d'une adresse e-mail.
     */
    public static function email(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Vérifie le format d'un numéro de téléphone.
     * Accepte les formats internationaux : chiffres, espaces, tirets, préfixe +.
     */
    public static function telephone(string $telephone): bool
    {
        return preg_match("/^\+?[0-9\s\-]{8,20}$/", $telephone) === 1;
    }

    /**
     * Vérifie la complexité minimale d'un mot de passe (8 caractères minimum).
     */
    public static function motDePasse(string $mot_de_passe): bool
    {
        return strlen($mot_de_passe) >= 8;
    }

    /**
     * Vérifie que le type commercial d'un véhicule est dans la liste autorisée.
     */
    public static function typeCommercial(string $type): bool
    {
        return in_array($type, ['achat', 'location'], true);
    }

    /**
     * Vérifie que le statut d'un dossier est dans la liste autorisée.
     */
    public static function statutDossier(string $statut): bool
    {
        return in_array($statut, ['en_attente', 'valide', 'refuse'], true);
    }
}
