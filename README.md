# GP2I — Gestion du parc matériel

Application web de gestion d'un parc informatique et matériel : inventaire,
catégories, fournisseurs, commandes, livraisons, mouvements de stock,
emprunts et utilisateurs.

Développée avec **Symfony 7.4**, **Doctrine ORM**, **Twig** et **AssetMapper** (sans Node.js).

## Fonctionnalités

- **Connexion sécurisée** avec rôles : Administrateur > Gestionnaire > Lecture seule
- **Inventaire des matériels** : recherche instantanée, filtres (statut, catégorie,
  fournisseur), pagination, export CSV, seuil d'alerte de stock par matériel
- **Mouvements de stock** qui mettent réellement à jour quantités, lieux et statuts
- **Emprunts** : qui a pris quoi, retour prévu, retards signalés, retour en un clic
- **Commandes et livraisons** : réception d'une livraison = stock mis à jour automatiquement
- **Photos** des matériels (envoi de fichiers) et **étiquettes QR code** imprimables
- **Tableau de bord** : compteurs, graphiques de répartition et de mouvements,
  alertes de stock faible et d'emprunts en retard
- **Archivage automatique** de toute suppression (qui, quoi, quand)

## Installation (Windows + Laragon, ou serveur PHP intégré)

Prérequis : PHP 8.2+, Composer, MySQL/MariaDB démarré.

```bash
git clone https://github.com/Kiff07/GP2I-main.git
cd GP2I-main
composer install
```

Créez un fichier `.env.local` à la racine (à côté du `.env`) :

```dotenv
APP_SECRET=generez-une-chaine-aleatoire (voir ci-dessous)
DATABASE_URL="mysql://root:@127.0.0.1:3306/gp2i?serverVersion=10.4.32-MariaDB&charset=utf8mb4"
```

```bash
php -r "echo bin2hex(random_bytes(24)), PHP_EOL;"   # génère le secret à coller ci-dessus
php bin/console doctrine:database:create --if-not-exists
php bin/console doctrine:migrations:migrate --no-interaction
php bin/console app:create-admin admin@gp2i.local admin   # crée VOTRE compte administrateur
php bin/console asset-map:compile   # nécessaire avec le serveur PHP intégré (CSS/JS)
php -S localhost:8000 -t public
```

Ouvrez ensuite http://localhost:8000/login et connectez-vous avec le compte créé.

> Compte de démonstration (données de test uniquement, à ne pas utiliser en production) :
> `php bin/console app:load-demo-data` crée `admin@gp2i.local` / `admin123`.

## Les rôles

| Rôle | Consulter | Gérer le stock | Gérer les comptes |
|---|:---:|:---:|:---:|
| `ROLE_USER` | oui | non | non |
| `ROLE_MANAGER` | oui | oui | non |
| `ROLE_ADMIN` | oui | oui | oui |

## Tests

```bash
php bin/phpunit tests/Service tests/Entity
php bin/console lint:twig templates/
php bin/console lint:container
```

Une intégration continue GitHub Actions (`.github/workflows/ci.yml`) relance
ces vérifications à chaque envoi sur GitHub.

## Structure du projet

- `src/Controller/` — écrans (tableau de bord, matériels, emprunts, commandes…)
- `src/Entity/` — modèle de données Doctrine (Materials, Loans, PurchaseOrders…)
- `src/Service/` — règles métier (`StockManager`, `ReceptionManager`)
- `src/EventListener/ArchiveListener.php` — journal des suppressions
- `templates/` — vues Twig ; `assets/styles/app.css` — design (clair + sombre)
- `AMELIORATIONS.md` — historique des analyses et idées d'évolution
