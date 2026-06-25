# M-Motors - Service d'arrière-plan (Back-Office API)

Ce dépôt centralise la logique métier, les APIs PHP asynchrones et la gestion de la persistance des données pour l'application M-Motors.

## Architecture & Déploiement
* **Branche `main`** : Code de production déployé automatiquement via GitHub Actions sur l'hébergement Hostinger.
* **Branche `dev`** : Centralisation des fonctionnalités en cours d'intégration.

## Configuration locale
1. Dupliquez le fichier `config/db.php.dist` et renommez-le en `db.php`.
2. Ajustez les variables de connexion en fonction de votre infrastructure MySQL locale.