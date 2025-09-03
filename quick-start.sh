#!/bin/bash

# Script de démarrage rapide pour l'application Parrainage BUT
echo "🚀 Démarrage de l'application Parrainage BUT Informatique"
echo "======================================================="

# Couleurs
GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m'

# Vérifier si Docker est installé
if ! command -v docker &> /dev/null; then
    echo -e "${RED}❌ Docker n'est pas installé. Veuillez l'installer d'abord.${NC}"
    exit 1
fi

# Vérifier si Docker Compose est disponible
if ! command -v docker-compose &> /dev/null && ! docker compose version &> /dev/null; then
    echo -e "${RED}❌ Docker Compose n'est pas disponible. Veuillez l'installer d'abord.${NC}"
    exit 1
fi

# Utiliser docker-compose ou docker compose selon la disponibilité
if command -v docker-compose &> /dev/null; then
    COMPOSE_CMD="docker-compose"
else
    COMPOSE_CMD="docker compose"
fi

echo -e "${BLUE}🔍 Vérification des prérequis...${NC}"

# Vérifier que les ports sont libres
check_port() {
    local port=$1
    local service=$2
    if lsof -Pi :$port -sTCP:LISTEN -t >/dev/null 2>&1; then
        echo -e "${RED}❌ Le port $port est déjà utilisé (requis pour $service)${NC}"
        echo "   Arrêtez le service utilisant ce port ou modifiez docker-compose.yml"
        return 1
    fi
    return 0
}

ports_ok=true
check_port 4200 "Frontend Angular" || ports_ok=false
check_port 8080 "Backend PHP" || ports_ok=false
check_port 3306 "Base de données MySQL" || ports_ok=false

if [ "$ports_ok" = false ]; then
    echo -e "${RED}❌ Certains ports requis sont occupés. Résolvez les conflits avant de continuer.${NC}"
    exit 1
fi

echo -e "${GREEN}✅ Tous les ports requis sont disponibles${NC}"

# Nettoyer les anciens containers si ils existent
echo -e "${YELLOW}🧹 Nettoyage des anciens containers...${NC}"
$COMPOSE_CMD down --remove-orphans 2>/dev/null

# Construire et démarrer les services
echo -e "${BLUE}🔨 Construction et démarrage des services...${NC}"
echo "   Cela peut prendre quelques minutes la première fois..."

if $COMPOSE_CMD up --build -d; then
    echo -e "${GREEN}✅ Services démarrés avec succès !${NC}"
else
    echo -e "${RED}❌ Erreur lors du démarrage des services${NC}"
    exit 1
fi

# Attendre que les services soient prêts
echo -e "${YELLOW}⏳ Attente que les services soient prêts...${NC}"

# Fonction pour vérifier qu'un service répond
wait_for_service() {
    local url=$1
    local name=$2
    local max_attempts=30
    local attempt=1

    while [ $attempt -le $max_attempts ]; do
        if curl -s "$url" > /dev/null 2>&1; then
            echo -e "${GREEN}✅ $name est prêt !${NC}"
            return 0
        fi
        echo "   Tentative $attempt/$max_attempts pour $name..."
        sleep 2
        attempt=$((attempt + 1))
    done

    echo -e "${RED}❌ $name n'a pas répondu après $max_attempts tentatives${NC}"
    return 1
}

# Attendre les services
wait_for_service "http://localhost:8080/api" "API Backend"
wait_for_service "http://localhost:4200" "Frontend Angular"

# Exécuter les tests API
echo -e "${BLUE}🧪 Exécution des tests de l'API...${NC}"
if [ -f "./test_api.sh" ]; then
    chmod +x ./test_api.sh
    ./test_api.sh
else
    echo -e "${YELLOW}⚠️  Script de test non trouvé, tests ignorés${NC}"
fi

# Afficher les informations finales
echo ""
echo "🎉 ${GREEN}Application démarrée avec succès !${NC}"
echo "================================================"
echo ""
echo "📱 ${BLUE}Accès à l'application :${NC}"
echo "   Frontend (Interface utilisateur) : http://localhost:4200"
echo "   Backend (API)                    : http://localhost:8080/api"
echo ""
echo "🔐 ${BLUE}Accès administrateur :${NC}"
echo "   URL        : http://localhost:4200 -> Admin"
echo "   Utilisateur: admin"
echo "   Mot de passe: admin123"
echo ""
echo "🛠️  ${BLUE}Gestion des services :${NC}"
echo "   Voir les logs        : $COMPOSE_CMD logs"
echo "   Arrêter l'application: $COMPOSE_CMD down"
echo "   Redémarrer           : $COMPOSE_CMD restart"
echo ""
echo "📊 ${BLUE}État des containers :${NC}"
$COMPOSE_CMD ps

echo ""
echo "🚀 ${GREEN}Prêt à utiliser ! Rendez-vous sur http://localhost:4200${NC}"