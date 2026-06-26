<?php
/**
 * Journalisation des événements applicatifs de M-Motors.
 * Écrit dans logs/app.log et envoie un e-mail aux admins concernés en cas d'erreur critique (hors environnement local).
 */
class Logger
{
    const FICHIER_LOG = __DIR__ . '/../logs/app.log';

    public static function info(string $contexte, string $message): void
    {
        self::ecrire('INFO', $contexte, $message);

        if (!EST_LOCAL) {
            self::alerterParEmail('INFO', $contexte, $message);
        }
    }

    public static function warning(string $contexte, string $message): void
    {
        self::ecrire('WARNING', $contexte, $message);

        if (!EST_LOCAL) {
            self::alerterParEmail('WARNING', $contexte, $message);
        }
    }

    public static function error(string $contexte, string $message): void
    {
        self::ecrire('ERROR', $contexte, $message);

        if (!EST_LOCAL) {
            self::alerterParEmail('ERROR', $contexte, $message);
        }
    }

    private static function ecrire(string $niveau, string $contexte, string $message): void
    {
        $dossier = dirname(self::FICHIER_LOG);
        if (!is_dir($dossier)) {
            mkdir($dossier, 0755, true);
        }

        $ligne = sprintf(
            "[%s] [%s] %s — %s\n",
            date('Y-m-d H:i:s'),
            $niveau,
            $contexte,
            $message
        );

        file_put_contents(self::FICHIER_LOG, $ligne, FILE_APPEND | LOCK_EX);
    }

    private static function alerterParEmail(string $niveau, string $contexte, string $message): void
    {
        $fichier_config = __DIR__ . '/../config/db.php';
        if (!file_exists($fichier_config)) {
            return;
        }

        require_once $fichier_config;

        // Niveaux déclenchant l'alerte selon la préférence de chaque admin :
        // 'info'    → INFO + WARNING + ERROR
        // 'warning' → WARNING + ERROR
        // 'error'   → ERROR uniquement
        $niveaux_couverts = [
            'info'    => ['INFO', 'WARNING', 'ERROR'],
            'warning' => ['WARNING', 'ERROR'],
            'error'   => ['ERROR'],
        ];

        $destinataires = [];
        try {
            $requete = $bdd->prepare("SELECT email, alertes_email FROM utilisateurs WHERE role = 'admin' AND alertes_email != 'aucune'");
            $requete->execute();
            $admins = $requete->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception) {
            return;
        }

        foreach ($admins as $admin) {
            $preference = $admin['alertes_email'];
            if (isset($niveaux_couverts[$preference]) && in_array($niveau, $niveaux_couverts[$preference], true)) {
                $destinataires[] = $admin['email'];
            }
        }

        if (empty($destinataires)) {
            return;
        }

        $libelles = ['INFO' => 'Information', 'WARNING' => 'Avertissement', 'ERROR' => 'Erreur critique'];
        $sujet    = '[M-Motors] ' . ($libelles[$niveau] ?? $niveau) . ' détecté(e)';
        $corps    = "Un événement de niveau " . $niveau . " a été détecté sur l'application M-Motors.\n\n";
        $corps   .= "Contexte : " . $contexte . "\n";
        $corps   .= "Message  : " . $message . "\n";
        $corps   .= "Date     : " . date('d/m/Y H:i:s') . "\n";
        $entetes  = "From: noreply@m-motors.fr\r\nContent-Type: text/plain; charset=UTF-8";

        foreach ($destinataires as $email) {
            mail($email, $sujet, $corps, $entetes);
        }
    }
}
