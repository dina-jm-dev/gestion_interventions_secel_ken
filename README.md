# SECEL - Gestion des Interventions Techniques

## Présentation
SECEL est une application web complète de gestion des interventions techniques, permettant le suivi des besoins clients, l'affectation des techniciens et la génération de rapports et factures.

## Fonctionnalités
- **Authentification robuste** : Multi-rôles (Admin, Technicien, Client).
- **Gestion Client** : Émission de besoins et suivi en temps réel.
- **Gestion Admin** : Supervision, affectation des techniciens et facturation.
- **Gestion Technicien** : Consultation des missions et rédaction de rapports.
- **Interface Moderne** : Design épuré, animations GSAP locales et loader fluide.

## Architecture du Projet
- `/config` : Paramètres système et base de données.
- `/includes` : Fragments HTML (Header, Sidebar, Footer) et fonctions globales.
- `/pages` : Pages principales de l'application.
- `/assets` : Ressources CSS, JS (GSAP local) et Images.

## Base de Données
Le projet utilise une base de données MySQL nommée `secel_db`. 
Le schéma est disponible dans le fichier `database.sql`.

### Tables principales :
- `utilisateurs` : Table unifiée (role: admin, technicien, client).
- `specialites` : Liste des domaines d'expertise des techniciens.
- `besoins` : Demandes initiales des clients.
- `interventions` : Actions techniques programmées.
- `affectations` : Lien entre techniciens et interventions.
- `rapports` : Comptes-rendus post-intervention.
- `factures` : Documents financiers liés aux interventions.

## Installation
1. Copier le dossier dans votre serveur web (ex: `www/` ou `htdocs/`).
2. Importer le fichier `database.sql` dans votre gestionnaire MySQL (ex: phpMyAdmin).
3. Configurer `config/db.php` avec vos identifiants.
4. Accéder à l'application via `http://localhost/intervention_ken/index.php`.

## Crédits
Développé avec HTML5, CSS3, JavaScript, PHP Procédural et GSAP.
