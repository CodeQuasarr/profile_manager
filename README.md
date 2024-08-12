# Mon Application

## Description
Cette application est développée en PHP et JavaScript. Elle utilise npm pour la gestion des dépendances JavaScript et Composer pour les dépendances PHP.

## Prérequis
- PHP ^8
- Composer
- node.js 20
- npm 10

## Installation

### Cloner le dépôt
```bash
git clone https://github.com/votre-utilisateur/votre-repo.git
cd votre-repo
```

### Installer les dépendances PHP
```bash
composer install
cp .env.example .env or copy .env.example .env
php artisan key:generate
```

### Commande maison pour installer le necessaire au lencement de l'application
```bash
php artisan deploy
```
Celui-ci va lancer : 
- migration
- seed
- les roles et chaque permissions qui sera assigné à chaque role
- Chaque role sera assigné à un utilisateur

### Documentation API (Swagger)
La documentation de l'API est générée avec Swagger. Pour y accéder, démarrez l'application et rendez-vous à l'URL suivante :
```bash
http://localhost:8000/api/documentation
```

### Commandes Utiles
```bash
php artisan test
