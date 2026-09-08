# 🎓 Système de parrainage BUT Informatique

Application web de mise en relation parrains / filleuls entre étudiant·es
(BUT Informatique & GEA, BTS AC). Application **Symfony 7.4** unique, rendue
côté serveur avec **Twig** et **Tailwind CSS**.

## Fonctionnalités

- **Accueil** : présentation du dispositif + statistiques publiques.
- **Inscription** : formulaire public (nom, prénom, email, classe, souhait
  parrain/filleul, motivation, contacts Discord / Instagram) avec validation
  et protection CSRF.
- **Espace admin** (`/admin`) : connexion par identifiant / mot de passe,
  tableau de bord avec liste filtrable (par classe, par email), statistiques
  par classe, suppression d'une inscription, export CSV.

## Stack

| Élément | Choix |
|---|---|
| Framework | Symfony 7.4 (LTS) |
| Vues | Twig + Tailwind CSS (`symfonycasts/tailwind-bundle`, sans Node) |
| ORM | Doctrine ORM 3 + migrations |
| Auth | `security` bundle, `form_login`, fournisseur d'entité `Admin` |
| Base de données | MySQL 8 |
| Exécution | Docker : `php:8.4-apache` + MySQL 8 |

## Démarrage (Docker)

Prérequis : Docker + Docker Compose.

```bash
docker compose up --build
```

Au premier lancement, le conteneur applicatif installe les dépendances,
attend la base, applique les migrations (schéma + données de démo) et
compile le CSS.

| Service | URL / accès |
|---|---|
| Application | http://localhost:8080 |
| MySQL | `127.0.0.1:3307` — base `parrainage_db`, utilisateur `parrainage` / `parrainage` |

### Compte administrateur par défaut

- Utilisateur : `admin`
- Mot de passe : `admin123`

À changer avant toute mise en production.

## Développement

Le code du dépôt est monté dans le conteneur (`.:/var/www/html`), les
modifications sont prises en compte immédiatement.

```bash
# Console Symfony
docker compose exec app php bin/console <commande>

# Recompiler le CSS à la volée pendant le développement
docker compose exec app php bin/console tailwind:build --watch

# Créer une migration après modification d'une entité
docker compose exec app php bin/console make:migration
docker compose exec app php bin/console doctrine:migrations:migrate
```

Pour lancer la console ou les migrations **depuis l'hôte** (hors Docker),
copier `.env` vers `.env.local` et y pointer `DATABASE_URL` sur
`127.0.0.1:3307`.

## Structure

```
src/
  Controller/        Home, Inscription, Admin\Dashboard, Admin\Security
  Entity/            Inscription, Admin
  Enum/              Classe, Preference
  Form/              InscriptionType
  Repository/        InscriptionRepository (filtres, stats), AdminRepository
templates/           base, home, inscription, admin
migrations/          schéma initial + données de démo
docker/              entrypoint du conteneur applicatif
compose.yaml         stack de développement (app + MySQL)
Dockerfile           image applicative
```

## Production

`compose.yaml` cible le développement. Pour la production, prévoir :

- `APP_ENV=prod`, `APP_SECRET` et identifiants MySQL fournis par des
  variables d'environnement réelles (jamais committées) ;
- `composer install --no-dev --optimize-autoloader` + `cache:warmup` dans
  l'image ;
- `php bin/console tailwind:build --minify` au build ;
- un reverse proxy HTTPS devant le conteneur.
