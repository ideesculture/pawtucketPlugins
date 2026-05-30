# Glossaire Plugin for Pawtucket

Plugin de gestion de glossaire pour CollectiveAccess Pawtucket.

## Description

Ce plugin permet de créer et gérer un glossaire de termes avec leurs définitions.

## Fonctionnalités

- Affichage public du glossaire
- Interface d'édition pour les rédacteurs
- Ajout, modification et suppression de termes
- Catégorisation des termes (optionnel)

## Installation

1. Copier le dossier `Glossaire` dans `/app/plugins/`
2. Le plugin sera automatiquement détecté par Pawtucket

## Utilisation

### URLs disponibles

- `/index.php/Glossaire/Display/Index` - Vue publique du glossaire
- `/index.php/Glossaire/Display/Details/id/[ID]` - Détails d'un terme
- `/index.php/Glossaire/Editor/Index` - Gestion du glossaire (réservé aux rédacteurs)
- `/index.php/Glossaire/Editor/New` - Nouveau terme (réservé aux rédacteurs)
- `/index.php/Glossaire/Editor/Edit/id/[ID]` - Édition d'un terme (réservé aux rédacteurs)

### Permissions

Le rôle `redactor` est requis pour accéder aux fonctions d'édition.

## TODO

- Implémenter le stockage des termes (base de données ou fichiers JSON)
- Ajouter la recherche dans le glossaire
- Ajouter le tri alphabétique
- Ajouter les filtres par catégorie
- Ajouter la pagination

## Licence

Identique à celle de CollectiveAccess
