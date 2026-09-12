# 🎓 Système de parrainage BUT Informatique

[![CI](https://github.com/totif1/ParrainageApp/actions/workflows/ci.yml/badge.svg)](https://github.com/totif1/ParrainageApp/actions/workflows/ci.yml)
[![Docker image](https://github.com/totif1/ParrainageApp/actions/workflows/docker.yml/badge.svg)](https://github.com/totif1/ParrainageApp/actions/workflows/docker.yml)

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
- **Gestion des comptes admin** (`/admin/comptes/nouveau`, accessible depuis
  le bouton « Nouveau compte » du tableau de bord) : un admin déjà connecté
  peut créer un accès pour une autre personne du bureau.

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

### Qualité (mêmes commandes qu'en CI)

```bash
# Analyse statique
docker compose exec app vendor/bin/phpstan analyse

# Tests : base dédiée "parrainage_test" + PHPUnit
# (pas de .env.test committé : on passe DATABASE_URL explicitement)
docker compose exec database mysql -uroot -proot \
  -e "CREATE DATABASE IF NOT EXISTS parrainage_test; GRANT ALL ON parrainage_test.* TO 'parrainage'@'%';"

TESTDB="mysql://parrainage:parrainage@database:3306/parrainage_test?serverVersion=8.0.37&charset=utf8mb4"
docker compose exec -e APP_ENV=test -e DATABASE_URL="$TESTDB" app php bin/console doctrine:migrations:migrate --no-interaction
docker compose exec -e APP_ENV=test -e DATABASE_URL="$TESTDB" app php bin/phpunit
```

## CI / CD

Deux workflows GitHub Actions dans [`.github/workflows/`](.github/workflows/) :

| Fichier | Déclencheur | Ce qu'il fait |
|---|---|---|
| `ci.yml` | chaque push + PR vers `main` | **lint** (`composer validate`, lints YAML / Twig / conteneur) · **analyse statique** PHPStan · **tests** : build Tailwind, migrations et PHPUnit sur un service MySQL 8 jetable |
| `docker.yml` | push sur `main` et tags `v*` | build de l'image `--target prod` et publication sur `ghcr.io/<owner>/<repo>` (tags `latest`, `main`, `sha-…`, `X.Y.Z`) |

Récupérer l'image publiée :

```bash
docker pull ghcr.io/totif1/parrainageapp:latest
```

Un déploiement automatique (SSH vers un serveur, PaaS…) serait un job
supplémentaire dans `docker.yml`, après la publication de l'image.

## Structure

```
src/
  Controller/        Home, Inscription, Admin\Dashboard, Admin\Security
  Entity/            Inscription, Admin
  Enum/              Classe, Preference
  Form/              InscriptionType
  Repository/        InscriptionRepository (filtres, stats), AdminRepository
templates/           base, home, inscription, admin
tests/Functional/    pages publiques, inscription, espace admin
migrations/          schéma initial + données de démo
docker/              entrypoints des conteneurs (dev / prod)
compose.yaml         stack de développement (app + MySQL)
Dockerfile           multi-stage : cible `dev` (compose) et `prod` (image publiée)
phpstan.dist.neon    configuration de l'analyse statique
.github/workflows/   pipelines CI et image Docker
```

## Production

L'image `--target prod` du `Dockerfile` est autoportante : elle contient le
code, les dépendances `--no-dev`, l'autoload optimisé, le cache chauffé et le
CSS compilé. Son entrypoint attend la base puis applique les migrations avant
de lancer Apache.

À fournir au runtime (jamais dans l'image) :

- `APP_ENV=prod`, un vrai `APP_SECRET` ;
- `DATABASE_URL` vers la base de production ;
- un reverse proxy HTTPS devant le conteneur.

```bash
docker run -d -p 80:80 \
  -e APP_SECRET=... \
  -e DATABASE_URL="mysql://user:pass@db-host:3306/parrainage?serverVersion=8.0.37&charset=utf8mb4" \
  ghcr.io/totif1/parrainageapp:latest
```
