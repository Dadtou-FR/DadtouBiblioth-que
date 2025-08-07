# Système de Gestion de Bibliothèque

Un système de gestion de bibliothèque complet développé en PHP, permettant la gestion des livres, des réservations et des utilisateurs.

## Fonctionnalités

- 📚 Gestion complète des livres (ajout, modification, suppression)
- 👥 Gestion des utilisateurs et authentification
- 🔖 Système de réservation de livres
- 👨‍💼 Interface d'administration
- 📱 Design responsive

## Technologies Utilisées

- PHP
- MySQL
- HTML5
- CSS3
- JavaScript
- Font Awesome pour les icônes

## Installation

1. Clonez ce dépôt :
```bash
git clone [URL_DU_REPO]
```

2. Importez la base de données :
- Créez une base de données nommée 'bibliotheque'
- Importez le fichier SQL fourni dans le dossier 'MySQL'

3. Configurez la connexion à la base de données :
- Copiez `db.php.example` vers `db.php`
- Modifiez les paramètres de connexion dans `db.php`

## Structure du Projet

```
bibliotheque/
├── admin/              # Interface d'administration
├── CSS/               # Fichiers de style
├── MySQL/             # Fichiers de base de données
├── auth.php           # Gestion de l'authentification
├── db.php            # Configuration de la base de données
├── index.php         # Page d'accueil
└── ...
```

## Rôles Utilisateurs

- **Administrateur** : Gestion complète du système
- **Utilisateur** : Consultation et réservation de livres

## Sécurité

- Protection contre les injections SQL
- Hachage des mots de passe
- Validation des entrées utilisateur
- Gestion des sessions sécurisée

## Auteurs

- [Votre Nom]

## Licence

Ce projet est sous licence MIT. Voir le fichier `LICENSE` pour plus de détails.
