# Yapuka - Application de gestion de tâches

> Projet de formation DevOps — Backend Symfony + Frontend React

## Structure du projet

```
yapuka/
├── api/                  # Backend Symfony (PHP 8.4, API Platform)
│   ├── src/
│   │   ├── Controller/   # Contrôleurs (Auth, Task, Notification)
│   │   ├── Entity/       # Entités Doctrine (User, Task)
│   │   ├── Repository/   # Requêtes personnalisées
│   │   ├── Factory/      # Factories Foundry (données de test)
│   │   └── EventListener/# Listeners Doctrine (notifications)
│   ├── config/           # Configuration Symfony
│   ├── tests/            # Tests unitaires et d'intégration
│   ├── http/             # Fichiers HTTP pour PHPStorm
│   └── Dockerfile
├── front/                # Frontend React (Vite, Tailwind CSS)
│   ├── src/
│   │   ├── api/          # Client HTTP centralisé
│   │   ├── store/        # Stores Zustand (auth, tasks)
│   │   ├── hooks/        # Hooks personnalisés (SSE)
│   │   ├── components/   # Composants React
│   │   └── pages/        # Pages de l'application
│   └── Dockerfile
├── docker/               # Configuration Docker partagée
│   └── nginx/
│       └── default.conf
└── docker-compose.yml    # Orchestration de tous les services
```

## Stack technique

### Backend
- **Symfony 7.2** avec **API Platform 4**
- **PostgreSQL 16** (base de données)
- **Redis 7** (cache)
- **JWT** via Lexik JWT Authentication
- **CORS** via Nelmio CORS Bundle
- **Foundry** (fixtures et factories)
- **PHPUnit** (tests unitaires et d'intégration)
- **Xdebug** (débogage et couverture de code)

### Frontend
- **React 18** avec **Vite 6**
- **JavaScript** (pas de TypeScript)
- **Tailwind CSS** (styles utilitaires)
- **Zustand** (gestion d'état)
- **React Hook Form** + **Zod** (formulaires et validation)
- **Recharts** (graphiques)
- **Sonner** (notifications toast)
- **Lucide React** (icônes)

## Démarrage rapide

### Avec Docker (recommandé)

```bash
# Cloner le projet
git clone <url-du-repo>
cd yapuka

# Démarrer tous les services
docker compose up -d

# Installer les dépendances PHP
docker compose exec php composer install

# Générer les clés JWT
docker compose exec php php bin/console lexik:jwt:generate-keypair

# Créer la base de données et les tables
docker compose exec php php bin/console doctrine:database:create
docker compose exec php php bin/console doctrine:migrations:migrate

# Charger les données de démonstration
docker compose exec php php bin/console doctrine:fixtures:load
```

### Accès

| Service      | URL                          |
|-------------|------------------------------|
| Application | http://localhost:8080         |
| API Docs    | http://localhost:8080/api/docs|
| Vite (dev)  | http://localhost:5173         |

### Compte de démonstration

- **Email** : `demo@yapuka.dev`
- **Mot de passe** : `password`

## Tests

```bash
# Tests unitaires
docker compose exec php php bin/phpunit tests/Unit

# Tests d'intégration
docker compose exec php php bin/phpunit tests/Integration

# Tous les tests
docker compose exec php php bin/phpunit
```

## Endpoints API

| Méthode | Endpoint                     | Description              | Auth |
|---------|------------------------------|--------------------------|------|
| POST    | `/api/auth/register`         | Inscription              | ❌    |
| POST    | `/api/auth/login`            | Connexion (obtenir JWT)  | ❌    |
| GET     | `/api/tasks`                 | Liste des tâches         | ✅    |
| POST    | `/api/tasks`                 | Créer une tâche          | ✅    |
| PUT     | `/api/tasks/{id}`            | Modifier une tâche       | ✅    |
| DELETE  | `/api/tasks/{id}`            | Supprimer une tâche      | ✅    |
| GET     | `/api/tasks/stats`           | Statistiques             | ✅    |
| GET     | `/api/notifications/stream`  | Notifications SSE        | ✅    |
