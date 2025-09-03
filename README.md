# 🎓 Système de Parrainage BUT Informatique

Une application web complète pour gérer un système de parrainage entre étudiants du BUT Informatique, développée avec **Angular** (frontend) et **PHP** (backend API REST).

## 🚀 Fonctionnalités

### 🏠 Page d'accueil
- Présentation du principe du parrainage
- Statistiques du système
- Design moderne et responsive

### 📝 Système d'inscription
- Formulaire d'inscription complet avec validation
- Champs : Nom, Prénom, Email, Classe (BUT1/BUT2/BUT3), Motivation
- Validation côté client et serveur
- Gestion des erreurs et messages de confirmation

### 🔐 Espace administrateur
- Authentification sécurisée par JWT
- Tableau de bord avec statistiques
- Liste complète des inscriptions avec filtrage
- Export CSV des données
- Interface responsive et intuitive

## 🛠️ Architecture Technique

### Frontend (Angular 16+)
- **Framework**: Angular avec TypeScript
- **UI**: Bootstrap 5 avec design personnalisé
- **Routing**: Angular Router pour la navigation
- **HTTP**: HttpClient pour les appels API
- **Authentification**: JWT avec guards
- **Responsive**: Design mobile-first

### Backend (PHP 8+)
- **Architecture**: MVC avec API REST
- **Base de données**: MySQL 8 avec PDO
- **Authentification**: JWT (Firebase JWT)
- **Sécurité**: CORS, validation des données, protection contre les injections SQL
- **Logging**: Système de logs personnalisé

### Déploiement
- **Conteneurisation**: Docker & Docker Compose
- **Frontend**: Nginx pour servir l'application Angular
- **Backend**: Apache avec PHP 8.2
- **Base de données**: MySQL 8.0 avec données de test

## 📋 Prérequis

- Docker (version 20.10+)
- Docker Compose (version 2.0+)
- 4 GB RAM disponible
- Ports 3306, 4200, et 8080 libres

## 🚀 Installation et Lancement

### 1. Cloner le projet
```bash
git clone <repository-url>
cd parrainage-but
```

### 2. Lancer avec Docker Compose
```bash
# Construire et lancer tous les services
docker-compose up --build

# Ou en arrière-plan
docker-compose up --build -d
```

### 3. Accéder à l'application

Une fois tous les containers démarrés (environ 2-3 minutes) :

| Service | URL | Description |
|---------|-----|-------------|
| **Application principale** | http://localhost:4200 | Interface utilisateur Angular |
| **API Backend** | http://localhost:8080 | API REST PHP |
| **Base de données** | localhost:3306 | MySQL (accès direct si besoin) |

## 🔑 Accès Administrateur

### Identifiants par défaut
- **Nom d'utilisateur** : `admin`
- **Mot de passe** : `admin123`

### Comment accéder ?
1. Aller sur http://localhost:4200
2. Cliquer sur "Admin" dans la navigation
3. Saisir les identifiants ci-dessus
4. Accéder au tableau de bord administrateur

## 📊 Données de Test

L'application est pré-remplie avec des données de démonstration :
- 3 inscriptions d'exemple (une pour chaque niveau BUT)
- 1 compte administrateur
- Statistiques de base pour tester l'interface

## 🧪 Test de l'Application

### Tester l'inscription d'un étudiant
1. Aller sur http://localhost:4200
2. Cliquer sur "Devenir parrain/filleul"
3. Remplir le formulaire avec vos informations
4. Valider l'inscription

### Tester l'espace admin
1. Se connecter avec les identifiants admin
2. Consulter la liste des inscriptions
3. Utiliser les filtres par classe
4. Voir les détails d'une inscription
5. Tester l'export CSV

## 🔧 Structure du Projet

```
parrainage-but/
├── frontend/                  # Application Angular
│   ├── src/app/
│   │   ├── components/       # Composants (Home, Registration, Admin)
│   │   ├── services/         # Services (API, Auth)
│   │   ├── models/           # Modèles TypeScript
│   │   └── guards/           # Guards d'authentification
│   ├── Dockerfile
│   └── nginx.conf
│
├── backend/                   # API PHP
│   ├── public/
│   │   └── index.php         # Point d'entrée API
│   ├── src/
│   │   ├── Controllers/      # Contrôleurs (Inscription, Auth)
│   │   ├── Models/           # Modèles (Inscription, Admin)
│   │   ├── Database/         # Connexion DB
│   │   └── Auth/             # Authentification JWT
│   ├── config/               # Configuration
│   └── Dockerfile
│
├── database/
│   └── init.sql              # Script d'initialisation MySQL
│
├── docker-compose.yml
└── README.md
```

## 🌐 API Endpoints

### Endpoints Publics
- `POST /api/inscriptions` - Créer une inscription
- `POST /api/auth/login` - Connexion admin

### Endpoints Administrateur (JWT requis)
- `GET /api/inscriptions` - Liste des inscriptions
- `GET /api/inscriptions/{id}` - Détails d'une inscription
- `GET /api/inscriptions/stats` - Statistiques
- `GET /api/inscriptions/export` - Export CSV
- `DELETE /api/inscriptions/{id}` - Supprimer une inscription
- `POST /api/auth/logout` - Déconnexion
- `POST /api/auth/verify` - Vérifier le token

## 🔐 Sécurité

### Authentification
- JWT avec expiration (24h)
- Mots de passe hashés avec `password_hash()`
- Protection contre les attaques par force brute

### Base de données
- Requêtes préparées (protection SQL injection)
- Validation stricte des données
- Contraintes d'intégrité

### Frontend
- Guards pour protéger les routes admin
- Validation des formulaires
- Gestion sécurisée des tokens

## 🚨 Dépannage

### Les containers ne démarrent pas
```bash
# Vérifier les logs
docker-compose logs

# Redémarrer proprement
docker-compose down
docker-compose up --build
```

### Erreur de connexion à la base
```bash
# Attendre que MySQL soit complètement initialisé
docker-compose logs database

# La base peut prendre 1-2 minutes à s'initialiser
```

### Port déjà utilisé
```bash
# Vérifier les ports utilisés
netstat -tulpn | grep :4200
netstat -tulpn | grep :8080
netstat -tulpn | grep :3306

# Modifier les ports dans docker-compose.yml si nécessaire
```

### Frontend ne charge pas
```bash
# Vérifier que le build Angular s'est bien passé
docker-compose logs frontend

# Accéder au container pour débugger
docker-compose exec frontend sh
```

## 📈 Développement

### Modifier le frontend
1. Les changements dans `frontend/src` nécessitent un rebuild
2. Utiliser `docker-compose up --build frontend` pour rebuilder uniquement le frontend

### Modifier le backend
1. Les changements PHP sont visibles immédiatement (volume monté)
2. Pour les dépendances Composer : `docker-compose exec backend composer install`

### Base de données
1. Accès direct : `docker-compose exec database mysql -u parrainage_user -p parrainage_db`
2. Mot de passe : `parrainage_pass`

## 🎯 Fonctionnalités Futures

- [ ] Système de matching automatique parrain/filleul
- [ ] Notifications email automatiques
- [ ] Chat intégré entre parrains et filleuls
- [ ] Profils détaillés des étudiants
- [ ] Système d'évaluation du parrainage
- [ ] Interface mobile dédiée
- [ ] Tableau de bord avec analytics avancés

## 🤝 Contribution

1. Fork du projet
2. Créer une branche pour votre fonctionnalité
3. Commiter vos changements
4. Pousser vers la branche
5. Ouvrir une Pull Request

## 📄 Licence

Ce projet est sous licence MIT. Voir le fichier `LICENSE` pour plus de détails.

## 👥 Support

Pour toute question ou problème :
- Créer une issue sur le dépôt Git
- Consulter les logs : `docker-compose logs`
- Vérifier la documentation API à http://localhost:8080

---

**Développé avec ❤️ pour la communauté étudiante du BUT Informatique**