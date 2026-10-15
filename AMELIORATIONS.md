# GP2I — Analyse du code et ameliorations

> Analyse realisee sur l'ensemble du projet (controleurs, entites, repositories,
> formulaires, templates, CSS, configuration securite).
> **Les correctifs des sections 1 a 4 sont deja appliques dans le code.**
> La section 5 contient des blocs optionnels a copier-coller si vous voulez aller plus loin.

---

## A faire en premier apres avoir recupere ces modifications

```bash
composer install
php bin/console cache:clear

# 1. Creer votre premier compte administrateur (le site exige maintenant une connexion)
php bin/console app:create-admin admin@gp2i.local admin

# ... ou charger les donnees de demonstration existantes :
#    compte : admin@gp2i.local / admin123   (A CHANGER, voir section 1)
php bin/console app:load-demo-data

# 2. Verifier que tout est sain
php bin/console lint:twig templates/
php bin/console lint:container
php bin/console debug:router | grep -E "app_login|app_materials|delete"
php bin/phpunit tests/Service/StockManagerTest.php

# 3. Lancer le site
php -S localhost:8000 -t public
# puis ouvrir http://localhost:8000/login
```

**Aucune migration Doctrine n'est necessaire** : ces correctifs ne changent pas le schema de la base.

---

## 1. Securite — problemes critiques trouves

| # | Probleme | Preuve dans le code | Correctif applique |
|---|----------|--------------------|--------------------|
| S1 | **Aucune authentification** : tout le site etait public, y compris la creation d'utilisateurs (`/users`) | `config/packages/security.yaml` : provider `users_in_memory` vide, aucun `form_login`, aucun `access_control` | Provider branche sur l'entite `Users`, formulaire de connexion, `access_control` |
| S2 | L'entite `Users` implementait `UserInterface` mais n'etait jamais utilisee pour se connecter | `src/Entity/Users.php` + ancien `security.yaml` | `app_user_provider` sur la propriete `email` (coherent avec `getUserIdentifier()`) |
| S3 | **Mot de passe ecrase en edition** : le champ `password` etait mappe sur l'entite, donc le formulaire remplacait le hash stocke par le texte saisi — ou hashait une chaine vide si le champ restait vide | `src/Form/UsersType.php`, `UsersController::edit()` | Champ `plainPassword` non-mappe, hashe uniquement s'il est rempli |
| S4 | Aucun controle de role : un simple visiteur pouvait tout modifier/supprimer | aucun `#[IsGranted]` dans le projet | Hierarchie `ROLE_ADMIN > ROLE_MANAGER > ROLE_USER`, ecritures reservees aux gestionnaires, `/users` reserve aux admins |
| S5 | `APP_SECRET` vide et base `root` sans mot de passe dans le `.env` versione | `.env` : `APP_SECRET=` et `DATABASE_URL="mysql://root:@..."` | Voir le bloc copier-coller section 5.1 (`.env.local` non versione) |
| S6 | Mots de passe de demonstration en dur (`admin123`, `demo123`) | `src/Command/LoadDemoDataCommand.php` | A ne jamais utiliser en production ; creez vos comptes avec `app:create-admin` |
| S7 | Aucun en-tete de securite HTTP, aucune protection anti force-brute | — | `SecurityHeadersSubscriber` (nosniff, anti-clickjacking, Referrer-Policy). L'anti force-brute `login_throttling` est pret mais commente dans `security.yaml` : il faut d'abord `composer require symfony/rate-limiter` |

### Les roles, en resume

| Role | Peut consulter | Peut creer/modifier/supprimer le stock | Gere les comptes |
|------|:---:|:---:|:---:|
| `ROLE_USER` | oui | non | non |
| `ROLE_MANAGER` | oui | oui | non |
| `ROLE_ADMIN` | oui | oui | oui |

Fichiers concernes : `config/packages/security.yaml`, `src/Controller/SecurityController.php` (nouveau),
`templates/security/login.html.twig` (nouveau), `src/Command/CreateAdminCommand.php` (nouveau),
`src/Form/UsersType.php`, `src/Controller/UsersController.php`, `src/EventSubscriber/SecurityHeadersSubscriber.php` (nouveau).

---

## 2. Fonctionnalites qui ne marchaient pas

### F1 — La barre de recherche (votre demande, TODO.md)

Elle existait sur les materiels, mais avec 4 defauts :

1. **`%` et `_` non echappes** : chercher `%` renvoyait tout le catalogue (caracteres joker du `LIKE`).
   Corrige avec `addcslashes($q, '%_\\')` dans `MaterialsRepository`.
2. **Perimetre incomplet** : la recherche ne couvrait ni le nom de la categorie, ni celui du
   fournisseur. Jointures ajoutees, `LOWER()` des deux cotes pour la casse.
3. **Statut invalide = liste vide silencieuse** : un `?status=xxx` dans l'URL vidait la liste.
   Le statut est valide contre `Materials::statusChoices()`, sinon ignore.
4. **Aucune pagination** : `search()` chargeait toute la table. Nouvelle methode `searchPaginated()`
   (20 par page) + navigation Precedent/Suivant qui conserve les filtres.
5. **Uniquement sur les materiels** : meme recherche (`?q=`) ajoutee sur **Categories**
   (nom, description) et **Fournisseurs** (nom, contact, email), plus une **recherche globale
   dans l'en-tete** qui redirige vers les materiels filtres.
6. **Recherche instantanee** : nouveau controleur Stimulus `assets/controllers/search_controller.js`
   (soumission automatique apres 400 ms de pause dans la frappe, bouton toujours utilisable en secours).

Piege Doctrine rencontre : en DQL on ecrit le nom de la *propriete PHP*, pas du getter.
Pour les fournisseurs c'est `s.contact_name` (et non `s.contactName`), comme `m.serial_number`.

### F2 — Le fournisseur d'un materiel etait impossible a saisir

Le filtre "Fournisseur" existait dans la liste, l'entite avait la relation, les fixtures la
remplissaient... mais **`MaterialsType` n'avait pas de champ `supplier`** : impossible de l'affecter
depuis l'interface. Champ ajoute au formulaire, colonnes "Fournisseur" et "Emplacement"
ajoutees a la liste, fournisseur affiche sur la fiche materiel.

### F3 — Les mouvements de stock ne servaient a rien

Creer une entree/sortie/transfert ne modifiait **jamais** la quantite, le lieu ni le statut du
materiel : l'inventaire divergeait de l'historique des le premier mouvement.
Nouveau service `src/Service/StockManager.php`, branche sur `InventoryMovementsController::new()` :

- **Entree** : quantite += n, lieu = destination ;
- **Sortie** : quantite -= n, **refusee avec un message d'erreur si le stock est insuffisant** ;
- **Transfert** : lieu = destination (lieu d'origine pre-rempli) ;
- **Maintenance** : statut = "Maintenance" ;
- l'auteur du mouvement = l'utilisateur connecte par defaut ;
- tests unitaires fournis : `tests/Service/StockManagerTest.php` (4 tests, sans base de donnees).
- L'*edition* d'un mouvement ne re-applique pas l'effet (sinon double comptage) :
  pour annuler, creez le mouvement inverse. C'est indique en commentaire dans le controleur.

### F4 — Suppression absente sur 7 controleurs sur 10

Seuls Materiels/Categories/Fournisseurs avaient un `delete`. Actions `delete` POST + CSRF ajoutees
pour Archives, Images, Livraisons, Mouvements, Lignes de commande, Commandes et Utilisateurs,
avec le bouton "Supprimer" sur la page detail (`templates/crud/show.html.twig`, variable `delete_path`).
Protection supplementaire : un administrateur ne peut pas supprimer son propre compte.

### F5 — Total de commande tape a la main

`PurchaseOrders.totalAmount` etait saisi librement et pouvait ne correspondre a aucune ligne.
Nouvelle methode `PurchaseOrders::recalculateTotal()` : le total est recalcule automatiquement
apres chaque creation, modification ou suppression d'une ligne (`PurchaseItemsController`).

### F6 — Ce qui reste incomplet (a faire, blocs en section 5)

- **Images** (`AssetImages`) : le nom de fichier est tape au clavier, aucun envoi de fichier reel,
  aucune image affichee. Bloc complet en section 5.3.
- **Archives** : rien ne cree d'archive automatiquement lors d'une suppression (a brancher sur un
  evenement Doctrine si vous voulez une vraie piste d'audit).
- Pas d'export (CSV/PDF), pas de tri sur les colonnes, requetes `count([])` multiples sur le
  tableau de bord (acceptable a cette echelle).

---

## 3. Design d'interface — ce qui a ete ameliore

Fichiers : `templates/base.html.twig`, `assets/styles/app.css` (section ajoutee en fin de fichier,
l'existant n'est pas casse), templates des listes et du tableau de bord.

| # | Avant | Apres |
|---|-------|-------|
| D1 | Un seul badge bleu pour tous les statuts | **Badge colore par statut** : vert = disponible/recu, orange = emprunte/en attente, bleu = maintenance/commande, rouge = casse/annule, gris = sorti du parc (classes `status--available`, `status--borrowed`, etc.) |
| D2 | Alertes `error`/`danger` sans aucun style | Styles d'erreur/info ajoutes (fond rouge, bordure) : les messages d'echec deviennent visibles |
| D3 | Navigation simple, sans etat | En-tete **fixe** (sticky), lien **actif** surligne, logo, pied de page |
| D4 | Pas d'identite d'utilisateur | Nom de l'utilisateur connecte + bouton Deconnexion ; lien "Utilisateurs" visible uniquement pour les admins |
| D5 | Aucune recherche hors page Materiels | **Recherche globale** dans l'en-tete + compte de resultats |
| D6 | Stock faible invisible | Quantites <= 2 en **rouge gras** avec mention "faible"/"rupture", alerte sur la fiche materiel, panneau **"Alertes stock faible"** et repartition par statut sur le tableau de bord |
| D7 | Grilles rigides (3 colonnes fixes) | `auto-fit/minmax` : les cartes s'adaptent a la largeur, navigation horizontale defilante sur mobile |
| D8 | Aucun retour visuel clavier/souris | `:focus-visible` visible (accessibilite), survol des lignes de tableau, lien "Aller au contenu", `prefers-reduced-motion` respecte |

---

## 4. Recapitulatif des fichiers touches

**Nouveaux fichiers**

- `src/Controller/SecurityController.php` + `templates/security/login.html.twig` — connexion/deconnexion
- `src/Command/CreateAdminCommand.php` — `php bin/console app:create-admin email identifiant`
- `src/Service/StockManager.php` + `tests/Service/StockManagerTest.php` — regles de stock testees
- `src/EventSubscriber/SecurityHeadersSubscriber.php` — en-tetes HTTP de securite
- `assets/controllers/search_controller.js` — recherche instantanee (debounce 400 ms)

**Fichiers modifies**

- `config/packages/security.yaml` — authentification reelle, roles, throttling
- `src/Form/UsersType.php`, `src/Controller/UsersController.php` — mot de passe non-mappe, suppression protegee
- `src/Form/MaterialsType.php` — champ fournisseur manquant
- `src/Repository/MaterialsRepository.php` — recherche durcie + pagination + `countByStatus()` + `findLowStock()`
- `src/Repository/CategoriesRepository.php`, `src/Repository/SuppliersRepository.php` — methode `search()`
- `src/Controller/*` — roles sur les ecritures, suppressions manquantes, recherche, pagination, recalcul des totaux
- `src/Entity/PurchaseOrders.php` — `recalculateTotal()`
- `templates/base.html.twig`, `templates/materials/*`, `templates/dashboard/index.html.twig`,
  `templates/categories/*`, `templates/suppliers/*`, `templates/crud/show.html.twig`
- `assets/styles/app.css` — couche design ajoutee en fin de fichier

---

## 5. Blocs a copier-coller (optionnels, non appliques)

### 5.1 Secret et base de donnees hors du depot (a faire des maintenant)

Creez un fichier `.env.local` (deja ignore par Git grace au `.gitignore` Symfony) :

```dotenv
# .env.local — NE JAMAIS VERSIONNER
APP_SECRET=remplacez-par-une-chaine-aleatoire-longue
DATABASE_URL="mysql://gp2i:VOTRE_MOT_DE_PASSE@127.0.0.1:3306/gp2i?serverVersion=10.4.32-MariaDB&charset=utf8mb4"
```

Generer un secret :

```bash
php -r "echo bin2hex(random_bytes(24)), PHP_EOL;"
```

Et creez un utilisateur MySQL dedie au lieu de `root` :

```sql
CREATE USER 'gp2i'@'localhost' IDENTIFIED BY 'VOTRE_MOT_DE_PASSE';
GRANT ALL PRIVILEGES ON gp2i.* TO 'gp2i'@'localhost';
```

### 5.2 Ne plus utiliser les mots de passe de demonstration

`app:load-demo-data` cree `admin123` / `demo123`. En production : ne lancez pas cette commande,
ou changez les mots de passe immediatement apres via la page Utilisateurs (reservee aux admins).

### 5.3 Vrai envoi d'images pour les materiels

Aujourd'hui `AssetImages.filename` est un texte tape au clavier. Pour un vrai upload,
ajoutez dans `src/Form/AssetImagesType.php` (avec `use Symfony\Component\Form\Extension\Core\Type\FileType;`
et les contraintes `Assert\File`) :

```php
->add('imageFile', FileType::class, [
    'label' => 'Photo (JPG/PNG, 2 Mo max)',
    'mapped' => false,
    'required' => false,
    'constraints' => [
        new \Symfony\Component\Validator\Constraints\File(
            maxSize: '2M',
            mimeTypes: ['image/jpeg', 'image/png', 'image/webp'],
            mimeTypesMessage: 'Merci d\'envoyer une image JPG, PNG ou WebP.',
        ),
    ],
])
```

Puis, dans `AssetImagesController::new()`, avant `$em->persist($image)` :

```php
$file = $form->get('imageFile')->getData();
if ($file) {
    $safeName = bin2hex(random_bytes(8)).'.'.$file->guessExtension();
    $file->move($this->getParameter('kernel.project_dir').'/public/uploads', $safeName);
    $image->setFilename($safeName);
}
```

Et affichez l'image dans la fiche materiel :

```twig
{% for image in material.assetImages %}
    <img src="{{ asset('uploads/' ~ image.filename) }}" alt="{{ image.caption ?? material.name }}" loading="lazy">
{% endfor %}
```

Ajoutez `/public/uploads/` au `.gitignore`.

### 5.4 Export CSV des materiels filtres

Import a ajouter dans `MaterialsController` : `use Symfony\Component\HttpFoundation\StreamedResponse;`
puis cette route :

```php
#[Route('/export', name: 'app_materials_export', methods: ['GET'])]
#[IsGranted('ROLE_MANAGER')]
public function export(Request $request, MaterialsRepository $repository): Response
{
    $materials = $repository->search(
        $request->query->get('q'),
        $request->query->get('status'),
        $request->query->getInt('category') ?: null,
        $request->query->getInt('supplier') ?: null,
    );

    $response = new StreamedResponse(function () use ($materials) {
        $handle = fopen('php://output', 'w');
        fputcsv($handle, ['Nom', 'Reference', 'Serie', 'Quantite', 'Statut', 'Categorie', 'Fournisseur', 'Emplacement'], ';');
        foreach ($materials as $m) {
            fputcsv($handle, [$m->getName(), $m->getReference(), $m->getSerialNumber(), $m->getQuantity(),
                $m->getStatusLabel(), $m->getCategory()?->getName(), $m->getSupplier()?->getName(), $m->getLocation()], ';');
        }
        fclose($handle);
    });
    $response->headers->set('Content-Type', 'text/csv; charset=UTF-8');
    $response->headers->set('Content-Disposition', 'attachment; filename="materiels.csv"');

    return $response;
}
```

### 5.5 Mode sombre (CSS uniquement)

Ajoutez a la fin de `assets/styles/app.css` :

```css
@media (prefers-color-scheme: dark) {
    :root {
        --bg: #0f172a; --surface: #1e293b; --text: #e2e8f0;
        --muted: #94a3b8; --border: #334155;
    }
    tbody tr:hover { background: #273549; }
    .nav-links a:hover, .nav-links a.active { background: #334155; color: #e2e8f0; }
    .global-search input { background: #0f172a; color: var(--text); }
}
```

### 5.6 Verifications a lancer apres chaque gros changement

```bash
php bin/console lint:twig templates/ assets/
php bin/console lint:container
php bin/console debug:router
php bin/phpunit
```

---

## 6. Limites de cette analyse

- L'environnement d'analyse ne disposait pas de PHP : les correctifs ont ete verifies
  statiquement (routes referencees/existantes croisees, equilibre syntaxique, YAML valide).
  Lancez les commandes de la section 5.6 chez vous pour la validation finale.
- Pour annuler toutes ces modifications : `git checkout -- .` sur la branche concernee
  (les nouveaux fichiers non suivis devront etre supprimes a la main).

---

## 7. Lot 2 — tout le reste des suggestions (appliqué)

- **Présentation** : accents français dans toute l'interface, `README.md` complet,
  bouton **Exporter CSV** sur la liste des matériels.
- **Emprunts** (nouveau menu) : matériel, emprunteur, dates, statut calculé
  (En cours / En retard / Retourné), sortie et réintégration du stock automatiques,
  retards affichés sur le tableau de bord. Table créée par la migration
  `Version20261015120000` (qui ajoute aussi le seuil d'alerte par matériel).
- **Réception automatique** : créer une livraison liée à une commande réceptionne
  les lignes, augmente le stock des matériels reconnus par leur référence,
  crée les mouvements d'entrée et passe la commande en "Reçue"
  (`src/Service/ReceptionManager.php`).
- **Photos réelles** : envoi de fichier image (JPG/PNG/WebP, 2 Mo) dans l'écran
  Images, fichiers dans `public/uploads/`, galerie sur la fiche matériel.
- **Graphiques** du tableau de bord en pur CSS (répartition par statut,
  mouvements des 6 derniers mois), **mode sombre** automatique.
- **Filtres + pagination** sur Commandes et Mouvements, **page Mon profil**
  avec changement de mot de passe, **étiquette QR code** imprimable par matériel
  (bouton sur la fiche), **seuil d'alerte stock** réglable par matériel,
  **archivage automatique** des suppressions (`ArchiveService`),
  tests unitaires (stock, emprunts) et **CI GitHub Actions**.

Après récupération de ce lot :

```bash
git pull origin arena/6e9ac7cb-gp2i-main
php bin/console doctrine:migrations:migrate --no-interaction
php bin/console cache:clear
php bin/console asset-map:compile
php bin/phpunit tests/Service tests/Entity
```
