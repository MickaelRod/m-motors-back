<?php
/**
 * Logique métier relative aux dossiers clients (demandes d'achat et de location).
 * Toutes les méthodes sont statiques et sans dépendance externe (testables unitairement).
 */
class Dossier
{
    /** Correspondance entre les codes de statut et leurs libellés affichables. */
    const STATUTS = [
        'en_attente' => 'En attente',
        'valide'     => 'Validé',
        'refuse'     => 'Refusé',
    ];

    /** Correspondance entre les codes de type de demande et leurs libellés affichables. */
    const TYPES_DEMANDE = [
        'achat'       => 'Achat comptant',
        'financement' => 'Achat avec financement',
        'location'    => 'Location (LLD)',
        'autre'       => 'Demande générale',
    ];

    /**
     * Vérifie qu'un statut de dossier est dans la liste autorisée.
     */
    public static function estStatutValide(string $statut): bool
    {
        return array_key_exists($statut, self::STATUTS);
    }

    /**
     * Retourne le libellé affichable d'un type de demande.
     * Retourne le code brut si le type n'est pas reconnu.
     */
    public static function traduireType(string $type): string
    {
        return self::TYPES_DEMANDE[$type] ?? $type;
    }

    /**
     * Retourne le libellé affichable d'un statut de dossier.
     * Retourne le code brut si le statut n'est pas reconnu.
     */
    public static function traduireStatut(string $statut): string
    {
        return self::STATUTS[$statut] ?? $statut;
    }
}
