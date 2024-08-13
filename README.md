# Application de Gestion des Utilisateurs pour un Club

## Description

Cette mini-application développée avec **Laravel 11** permet de gérer les utilisateurs d'un club, y compris la création de rôles et de permissions, et l'attribution de ces rôles aux utilisateurs et restrictions.

## Prérequis

Avant de commencer, assurez-vous d'avoir installé les outils suivants sur votre machine :

- **PHP** ^8
- **Composer**
- **Node.js** 20
- **npm** 10

## Installation

### 1. Cloner le dépôt

```bash
git clone https://github.com/votre-utilisateur/votre-repo.git
cd votre-repo
```

### 2. Installer les dépendances PHP
```bash
composer install
```

### 3. Configurer l'environnement
###### Copiez le fichier .env.example en .env et générez la clé de l'application
```bash
cp .env.example .env
php artisan key:generate
```

### 4. Créer les clés pour Passport
###### Générez les clés pour Laravel Passport, nécessaires pour l'authentification des API
```bash
php artisan passport:keys
php artisan passport:client --personal
```

### 5. Déployer 'localement' l'application
###### Utilisez la commande maison pour installer tout le nécessaire au lancement de l'application :)
```bash
php artisan deploy
```

### 6. Lancer les tests
```bash
php artisan test
```
###### Cette commande exécutera les actions suivantes :
- Migration de la base de données
- Seed de la base de données avec des données de base
- Création des rôles et des permissions
- Attribution des rôles aux utilisateurs
- Installation d'autre dépendances nécessaires

### 7. Configurer l'envoi de mails
###### Remplissez les éléments nécessaires pour l'envoi de mails dans le fichier `.env`
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.example.com
MAIL_PORT=587
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your_email@example.com
MAIL_FROM_NAME="${APP_NAME}"

APP_TOKEN_SECRET_KEY=your_secure_token
```

### 8. Documentation API (Swagger)
###### La documentation de l'API est générée avec Swagger. Pour y accéder, démarrez l'application et rendez-vous à l'URL suivante
```bash
http://localhost:8000
```
