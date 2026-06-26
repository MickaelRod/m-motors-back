<?php
/**
 * Logique métier relative aux véhicules du catalogue.
 * Toutes les méthodes sont statiques et sans dépendance externe (testables unitairement).
 */
class Vehicule
{
    /**
     * Retourne le type commercial opposé pour la bascule achat ↔ location.
     * Retourne null si le type fourni n'est pas reconnu.
     */
    public static function basculerType(string $type_actuel): ?string
    {
        if ($type_actuel === 'achat') {
            return 'location';
        }
        if ($type_actuel === 'location') {
            return 'achat';
        }
        return null;
    }

    /**
     * Vérifie qu'un type commercial est valide.
     */
    public static function estTypeValide(string $type): bool
    {
        return in_array($type, ['achat', 'location'], true);
    }
}
