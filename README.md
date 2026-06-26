# M-Motors : Back-Office & API REST

Application de gestion back-office pour une entreprise fictive de ventes et de location de véhicules (M-Motors). Expose une API PHP REST utilisée par le front-office client ainsi qu'une interface d'administration monopage.

## Fonctionnalités

- **Gestion du catalogue** : ajout, modification, suppression et bascule achat/location des véhicules
- **Gestion des demandes clients** : consultation et mise à jour du statut (en attente / validé / refusé) avec réconciliation automatique des visiteurs anonymes (double JOIN sur e-mail)
- **Gestion des utilisateurs** : création, modification, suppression ; rôles `client` / `admin`
- **Journalisation applicative** : classe `Logger` (INFO / WARNING / ERROR) écrivant dans `logs/app.log`, consultable depuis l'onglet Logs du back-office
- **Alerting e-mail par admin** : chaque administrateur configure son niveau d'alerte (aucune / ERROR / WARNING+ERROR / INFO+WARNING+ERROR) ; les e-mails sont envoyés automatiquement sur les événements correspondants (désactivé en environnement local)
- **Tests unitaires** : 4 suites PHPUnit 10 couvrant les classes de service métier (`Validation`, `Securite`, `Vehicule`, `Dossier`)

## Stack technique

- PHP 8+ (API REST, sessions séparées admin/client, PDO/MySQL)
- MySQL (InnoDB, utf8mb4)
- PHPUnit 10 + Xdebug (tests unitaires et couverture)
- GitHub Actions (CI/CD FTP vers hébergement)

## Architecture

```text
api/          Endpoints REST (un fichier par ressource)
config/       Bootstrap CORS/sessions, connexion PDO, structure SQL
src/          Classes de service métier (Validation, Securite, Vehicule, Dossier, Logger)
tests/        Suites PHPUnit
uploads/      Pièces jointes des dossiers clients (gitignored)
logs/         Fichier app.log (gitignored)
```

## Sécurité

- Sessions PHP nommées et séparées (admin vs client), cookie `HttpOnly` + `SameSite=Lax`
- Mots de passe hachés bcrypt via `password_hash` / `password_verify`
- Requêtes SQL préparées (PDO, aucune concaténation dynamique)
- Upload : liste blanche d'extensions (`pdf`, `jpg`, `jpeg`, `png`), nom de fichier regénéré aléatoirement
- Gardes d'accès centralisés dans `bootstrap.php` (`exiger_admin`, `exiger_client`)

## Déploiement

Push sur `main` → déploiement automatique via GitHub Actions FTP vers hébergement (`public_html/back/`).  
Les répertoires `vendor/`, `coverage/`, `tests/`, `logs/` et les fichiers de configuration de développement sont exclus du déploiement.

## Configuration locale

1. Dupliquer `config/db.php.dist` → `config/db.php` et renseigner les paramètres MySQL locaux.
2. `composer install` pour installer PHPUnit.
3. `./vendor/bin/phpunit --coverage-html coverage/` pour lancer les tests et générer le rapport de couverture.
