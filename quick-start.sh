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
        echo -e "${YELLOW}⚠️  Le port $port est déjà utilisé (requis pour $service)${NC}"
        echo "   Tentative d'arrêt des services conflictuels..."

        # Tenter d'arrêter les containers Docker utilisant ce port
        docker ps --format "table {{.Names}}\t{{.Ports}}" | grep ":$port->" | awk '{print $1}' | xargs -r docker stop 2>/dev/null

        # Vérifier à nouveau
        if lsof -Pi :$port -sTCP:LISTEN -t >/dev/null 2>&1; then
            echo -e "${RED}   ❌ Port $port toujours occupé après tentative d'arrêt${NC}"
            echo "   Services utilisant le port $port:"
            lsof -Pi :$port -sTCP:LISTEN 2>/dev/null || echo "   Impossible de déterminer le service"
            return 1
        else
            echo -e "${GREEN}   ✅ Port $port libéré avec succès${NC}"
        fi
    fi
    return 0
}

ports_ok=true
check_port 4200 "Frontend Angular" || ports_ok=false
check_port 8080 "Backend PHP" || ports_ok=false
check_port 3306 "Base de données MySQL" || ports_ok=false

if [ "$ports_ok" = false ]; then
    echo -e "${RED}❌ Certains ports requis sont encore occupés.${NC}"
    echo -e "${YELLOW}💡 Solutions possibles:${NC}"
    echo "   1. Arrêtez manuellement les services utilisant ces ports"
    echo "   2. Modifiez les ports dans docker-compose.yml"
    echo "   3. Utilisez docker-compose.dev.yml (ports différents)"
    echo ""
    read -p "Voulez-vous continuer quand même ? (y/N): " continue_anyway
    if [[ ! "$continue_anyway" =~ ^[Yy]$ ]]; then
        exit 1
    fi
fi

echo -e "${GREEN}✅ Vérification des ports terminée${NC}"

# Nettoyer les anciens containers si ils existent
echo -e "${YELLOW}🧹 Nettoyage des anciens containers...${NC}"
$COMPOSE_CMD down --remove-orphans 2>/dev/null || true

# Vérifier si les fichiers nécessaires existent
echo -e "${BLUE}📋 Vérification des fichiers...${NC}"
required_files=(
    "docker-compose.yml"
    "backend/Dockerfile"
    "frontend/Dockerfile"
    "backend/composer.json"
    "backend/public/index.php"
)

missing_files=()
for file in "${required_files[@]}"; do
    if [[ ! -f "$file" ]]; then
        missing_files+=("$file")
    fi
done

if [[ ${#missing_files[@]} -gt 0 ]]; then
    echo -e "${RED}❌ Fichiers manquants:${NC}"
    printf '%s\n' "${missing_files[@]}"
    exit 1
fi

# Construire et démarrer les services
echo -e "${BLUE}🔨 Construction et démarrage des services...${NC}"
echo "   Cela peut prendre quelques minutes la première fois..."

# Construire d'abord avec rebuild forcé pour le backend
echo -e "${YELLOW}🔧 Reconstruction du backend...${NC}"
if ! $COMPOSE_CMD build --no-cache backend; then
    echo -e "${RED}❌ Erreur lors de la construction du backend${NC}"
    exit 1
fi

# Démarrer tous les services
echo -e "${YELLOW}▶️  Démarrage des services...${NC}"
if $COMPOSE_CMD up -d; then
    echo -e "${GREEN}✅ Services démarrés avec succès !${NC}"
else
    echo -e "${RED}❌ Erreur lors du démarrage des services${NC}"
    echo -e "${YELLOW}📋 Logs d'erreur:${NC}"
    $COMPOSE_CMD logs --tail=20
    exit 1
fi

# Attendre que les services soient prêts avec timeout amélioré
echo -e "${YELLOW}⏳ Attente que les services soient prêts...${NC}"

# Fonction pour vérifier qu'un service répond
wait_for_service() {
    local url=$1
    local name=$2
    local max_attempts=60
    local attempt=1

    echo -e "${BLUE}🔍 Vérification de $name...${NC}"

    while [ $attempt -le $max_attempts ]; do
        if curl -s --max-time 5 "$url" > /dev/null 2>&1; then
            echo -e "${GREEN}✅ $name est prêt !${NC}"
            return 0
        fi

        if [ $((attempt % 10)) -eq 0 ]; then
            echo "   Tentative $attempt/$max_attempts pour $name..."
            # Afficher les logs si ça prend trop de temps
            if [ $attempt -ge 30 ]; then
                echo "   Logs récents:"
                $COMPOSE_CMD logs --tail=5 backend 2>/dev/null || true
            fi
        fi

        sleep 2
        attempt=$((attempt + 1))
    done

    echo -e "${RED}❌ $name n'a pas répondu après $max_attempts tentatives${NC}"
    echo -e "${YELLOW}📋 Logs détaillés:${NC}"
    $COMPOSE_CMD logs --tail=10 2>/dev/null || true
    return 1
}

# Attendre les services dans l'ordre logique
if ! wait_for_service "http://localhost:8080/" "API Backend"; then
    echo -e "${RED}💔 Le backend ne répond pas${NC}"
    echo -e "${YELLOW}🔧 Tentative de diagnostic...${NC}"

    echo "Status des containers:"
    $COMPOSE_CMD ps

    echo -e "\nLogs du backend:"
    $COMPOSE_CMD logs backend --tail=20

    exit 1
fi

if ! wait_for_service "http://localhost:4200" "Frontend Angular"; then
    echo -e "${YELLOW}⚠️  Le frontend ne répond pas mais le backend fonctionne${NC}"
    echo "Vous pouvez continuer et tester l'API directement"
fi

# Test simple de l'API
echo -e "${BLUE}🧪 Test de l'API...${NC}"
api_response=$(curl -s http://localhost:8080/ 2>/dev/null)
if echo "$api_response" | grep -q "success.*true"; then
    echo -e "${GREEN}✅ API fonctionne correctement${NC}"
else
    echo -e "${YELLOW}⚠️  Réponse API inattendue:${NC}"
    echo "$api_response"
fi

# Afficher les informations finales
echo ""
echo "🎉 ${GREEN}Application démarrée avec succès !${NC}"
echo "================================================"
echo ""
echo "📱 ${BLUE}Accès à l'application :${NC}"
echo "   Frontend (Interface utilisateur) : http://localhost:4200"
echo "   Backend (API)                    : http://localhost:8080"
echo "   API Health Check                 : http://localhost:8080/health"
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
echo "   Maintenance          : ./maintenance.sh"
echo ""
echo "📊 ${BLUE}État des containers :${NC}"
$COMPOSE_CMD ps

# Vérifier l'état de santé final
echo ""
echo "🏥 ${BLUE}Vérification de l'état de santé...${NC}"
sleep 5  # Laisser le temps aux healthchecks de s'exécuter

healthy_services=0
total_services=3

container_status=$($COMPOSE_CMD ps --format json 2>/dev/null | jq -r '.[] | "\(.Name) \(.Health)"' 2>/dev/null || $COMPOSE_CMD ps)

if echo "$container_status" | grep -q "healthy"; then
    echo -e "${GREEN}✅ Services en bonne santé détectés${NC}"
else
    echo -e "${YELLOW}ℹ️  État de santé en cours de vérification...${NC}"
fi

echo ""
echo "🚀 ${GREEN}Prêt à utiliser ! Rendez-vous sur http://localhost:4200${NC}"

# Proposer d'ouvrir le navigateur
if command -v xdg-open &> /dev/null; then
    read -p "Voulez-vous ouvrir l'application dans le navigateur ? (y/N): " open_browser
    if [[ "$open_browser" =~ ^[Yy]$ ]]; then
        xdg-open http://localhost:4200 >/dev/null 2>&1 &
    fi
fi