# Framwork  <img width="512" height="512" alt="image" src="https://github.com/user-attachments/assets/9165c78a-f4ae-4e07-8a0f-7582e560b136" />


Un framework PHP léger et dockerisé pour démarrer rapidement un site web sécurisé avec authentification.

## 🎯 Objectif

Framework minimaliste offrant les fonctionnalités essentielles pour créer un site web sécurisé :

- ✅ Page de connexion/déconnexion
- ✅ Sécurisation des pages (authentification requise)
- ✅ Base de données SQLite (pas de serveur DB externe)
- ✅ Gestion des utilisateurs
- ✅ Interface d'administration
- ✅ Mots de passe hashés (bcrypt)
- ✅ Déploiement Docker en une commande

## 🚀 Démarrage rapide

### Prérequis

- Docker et Docker Compose installés
- Port 443 (HTTPS) disponible

### Installation

```bash
# Cloner le projet
git clone https://github.com/votre-username/framework-php.git
cd framework-php

# Démarrer l'application
docker compose up -d --build

# L'application est accessible sur https://localhost
```

### Compte par défaut

| Identifiant | Valeur |
|-------------|--------|
| Utilisateur | `admin` |
| Mot de passe | `admin` |

⚠️ **Important** : Changez le mot de passe admin dès la première connexion !

## 📋 Fonctionnalités

### Authentification
- Système de connexion sécurisé avec sessions PHP
- Hashage des mots de passe avec bcrypt
- Redirection automatique vers login si non authentifié

### Gestion des utilisateurs
- Création de nouveaux utilisateurs
- Attribution de droits (utilisateur/administrateur)
- Liste et gestion des comptes

### Base de données
- SQLite embarqué (aucune configuration requise)
- Fichier unique `UserData.db`
- Gestion automatique des tables

### Sécurité des pages

Pour protéger une page, ajoutez simplement en première ligne :

```php
<?php
require 'auth.php';
?>
```

**Exemple :**

```php
<?php
require 'auth.php'; // Page protégée
?>
<!DOCTYPE html>
<html>
<head>
    <title>Dashboard</title>
</head>
<body>
    <h1>Tableau de bord privé</h1>
    <p>Contenu accessible uniquement aux utilisateurs connectés.</p>
</body>
</html>
```


## 🛠️ Commandes Docker

### Démarrer l'application
```bash
docker compose up -d
```

### Arrêter l'application
```bash
docker compose down
```

### Voir les logs
```bash
docker compose logs -f
```

### Reconstruire complètement
```bash
docker compose down
docker compose build --no-cache
docker compose up -d
```

### Accéder au conteneur
```bash
docker compose exec web bash
```

## 💾 Sauvegarde et restauration

### Sauvegarder la base de données

```bash
docker compose cp web:/var/www/html/UserData.db ./backup/UserData_$(date +%Y%m%d).db
```

### Restaurer une sauvegarde

```bash
docker compose cp ./backup/UserData.db web:/var/www/html/UserData.db
```

### L'application ne démarre pas

```bash
# Vérifier les logs
docker compose logs

# Supprimer et reconstruire
docker compose down -v
docker compose up -d --build
```

### Erreur 502 Bad Gateway

Vérifiez que PHP-FPM fonctionne :

```bash
docker compose exec web php-fpm -t
```

### Base de données corrompue

Restaurez une sauvegarde ou supprimez `UserData.db` pour recréer une base vierge.

### Port déjà utilisé

Si le port 443 est occupé :

```bash
# Voir ce qui utilise le port
sudo lsof -i :443

# Ou changez le port dans docker-compose.yml
```


### Intégration avec frontend moderne

Le framework peut servir de backend pour React, Vue ou Angular :

```bash
# Frontend sur port 3000
# Backend (ce framework) sur port 443
# Configurez CORS dans nginx.conf si nécessaire
```


## 📄 Licence

Ce projet est sous licence MIT. Voir le fichier `LICENSE` pour plus de détails.

### Images Docker tierces

Ce projet utilise les images Docker officielles suivantes :

- **PHP** : [php:8.2-fpm](https://hub.docker.com/_/php) - PHP License 3.01
- **Nginx** : [nginx:latest](https://hub.docker.com/_/nginx) - BSD 2-Clause License

Les images sont automatiquement téléchargées depuis Docker Hub lors du build.



**Made with ❤️ using PHP, Nginx, SQLite and Docker**
