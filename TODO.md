# TODO - Zone de recherche et filtres pour les matériels

## Objectif
Ajouter une barre de recherche professionnelle et des filtres sur la page des matériels :
- Recherche textuelle (nom, référence, n° de série, localisation)
- Filtre par statut
- Filtre par catégorie
- Filtre par fournisseur

## Étapes
- [x] 1. Analyser les fichiers existants (controller, repository, entité, templates, CSS)
- [x] 2. Ajouter la méthode `search()` dans `MaterialsRepository`
- [x] 3. Mettre à jour `MaterialsController::index` pour gérer les filtres GET
- [x] 4. Mettre à jour `templates/materials/index.html.twig` avec la barre de recherche
- [x] 5. Ajouter les styles de la barre de recherche dans `assets/styles/app.css`
- [x] 6. Vérifier la syntaxe PHP et Twig (lint)

---
## Suite (voir AMELIORATIONS.md)
- [x] Recherche etendue aux categories et aux fournisseurs + recherche globale
- [x] Recherche instantanee (Stimulus), pagination, echappement des jokers LIKE
- [x] Authentification, roles, mouvements de stock effectifs, suppressions manquantes
- [ ] Idees restantes (copier-coller dans AMELIORATIONS.md §5) : upload d'images, export CSV, mode sombre
