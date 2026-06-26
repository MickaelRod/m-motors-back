<?php
/**
 * Opérations de sécurité : hachage de mots de passe et génération de noms de fichiers.
 * Toutes les méthodes sont statiques et sans dépendance externe (testables unitairement).
 */
class Securite
{
    /** Extensions de fichiers autorisées pour les pièces jointes des dossiers. */
    const EXTENSIONS_AUTORISEES = ['pdf', 'jpg', 'jpeg', 'png'];

    /**
     * Hache un mot de passe en clair avec l'algorithme bcrypt.
     */
    public static function hacherMotDePasse(string $mot_de_passe): string
    {
        return password_hash($mot_de_passe, PASSWORD_DEFAULT);
    }

    /**
     * Vérifie qu'un mot de passe en clair correspond à un hachage bcrypt.
     */
    public static function verifierMotDePasse(string $mot_de_passe, string $hachage): bool
    {
        return password_verify($mot_de_passe, $hachage);
    }

    /**
     * Génère un nom de fichier unique non prédictible pour un téléversement.
     * Retourne null si l'extension du fichier n'est pas dans la liste autorisée.
     */
    public static function genererNomFichier(string $nom_origine): ?string
    {
        $extension = strtolower(pathinfo($nom_origine, PATHINFO_EXTENSION));
        if (!in_array($extension, self::EXTENSIONS_AUTORISEES, true)) {
            return null;
        }
        return uniqid('doc_', true) . '.' . $extension;
    }
}
