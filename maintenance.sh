#!/bin/bash

# Script de maintenance pour l'application Parrainage BUT
echo "🔧 Script de maintenance - Parrainage BUT Informatique"
echo "====================================================="

# Couleurs
GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m'

# Détecter Docker Compose
if command -v docker-compose &> /dev/null; then
    COMPOSE_CMD="docker-compose"
else
    COMPOSE_CMD="docker compose"
fi

# Menu principal
show_menu() {
    echo -e "\n${BLUE}Que souhaitez-vous faire ?${NC}"
    echo "1. 📊 Voir l'état des services"
    echo "2. 📋 Voir les logs"
    echo "3. 🔄 Redémarrer l'application"
    echo "4. 🛑 Arrêter l'application"
    echo "5. 🚀 Démarrer l'application"
    echo "6. 🗃️  Sauvegarder la base de données"
    echo "7. 📤 Exporter les inscriptions en CSV"
    echo "8. 🧹 Nettoyer les containers et images"
    echo "9. 🔍 Diagnostiquer les problèmes"
    echo "10. 📈 Voir les statistiques"
    echo "0. ❌ Quitter"
    echo ""
    read -p "Votre choix (0-10): " choice
}

# Fonction 1: État des services
check_status() {
    echo -e "${BLUE}📊 État des services${NC}"
    echo "===================="
    $COMPOSE_CMD ps
    echo ""
    echo -e "${BLUE}🌐 Tests de connectivité${NC}"
    echo "========================"

    # Test Frontend
    if curl -s http://localhost:4200 > /dev/null; then
        echo -e "Frontend: ${GREEN}✅ Accessible${NC}"
    else
        echo -e "Frontend: ${RED}❌ Non accessible${NC}"
    fi

    # Test Backend
    if curl -s http://localhost:8080/api > /dev/null; then
        echo -e "Backend:  ${GREEN}✅ Accessible${NC}"
    else
        echo -e "Backend:  ${RED}❌ Non accessible${NC}"
    fi

    # Test Database
    if $COMPOSE_CMD exec -T database mysqladmin ping -h localhost --silent 2>/dev/null; then
        echo -e "Database: ${GREEN}✅ Accessible${NC}"
    else
        echo -e "Database: ${RED}❌ Non accessible${NC}"
    fi
}

# Fonction 2: Voir les logs
show_logs() {
    echo -e "${BLUE}📋 Sélection des logs à afficher${NC}"
    echo "1. Tous les services"
    echo "2. Frontend uniquement"
    echo "3. Backend uniquement"
    echo "4. Base de données uniquement"
    echo "5. Logs en temps réel (tail)"
    read -p "Votre choix (1-5): " log_choice

    case $log_choice in
        1) $COMPOSE_CMD logs --tail=50 ;;
        2) $COMPOSE_CMD logs --tail=50 frontend ;;
        3) $COMPOSE_CMD logs --tail=50 backend ;;
        4) $COMPOSE_CMD logs --tail=50 database ;;
        5) echo "Appuyez sur Ctrl+C pour arrêter"; $COMPOSE_CMD logs -f ;;
        *) echo "Choix invalide" ;;
    esac
}

# Fonction 3: Redémarrer
restart_app() {
    echo -e "${YELLOW}🔄 Redémarrage de l'application...${NC}"
    $COMPOSE_CMD restart
    echo -e "${GREEN}✅ Application redémarrée${NC}"
}

# Fonction 4: Arrêter
stop_app() {
    echo -e "${YELLOW}🛑 Arrêt de l'application...${NC}"
    $COMPOSE_CMD down
    echo -e "${GREEN}✅ Application arrêtée${NC}"
}

# Fonction 5: Démarrer
start_app() {
    echo -e "${YELLOW}🚀 Démarrage de l'application...${NC}"
    $COMPOSE_CMD up -d
    echo -e "${GREEN}✅ Application démarrée${NC}"
}

# Fonction 6: Sauvegarder la DB
backup_database() {
    echo -e "${BLUE}🗃️  Sauvegarde de la base de données${NC}"

    # Créer le dossier de sauvegarde
    mkdir -p ./database/backup

    # Nom du fichier de sauvegarde
    backup_file="backup_$(date +%Y%m%d_%H%M%S).sql"

    # Effectuer la sauvegarde
    if $COMPOSE_CMD exec -T database mysqldump -u parrainage_user -pparrainage_pass parrainage_db > "./database/backup/$backup_file"; then
        echo -e "${GREEN}✅ Sauvegarde créée: ./database/backup/$backup_file${NC}"

        # Afficher la taille du fichier
        size=$(du -h "./database/backup/$backup_file" | cut -f1)
        echo "   Taille: $size"
    else
        echo -e "${RED}❌ Erreur lors de la sauvegarde${NC}"
    fi
}

# Fonction 7: Exporter les inscriptions
export_inscriptions() {
    echo -e "${BLUE}📤 Export des inscriptions${NC}"

    # Créer le dossier d'export
    mkdir -p ./exports

    # Nom du fichier d'export
    export_file="inscriptions_$(date +%Y%m%d_%H%M%S).csv"

    # Utiliser l'API pour exporter (nécessite un token admin)
    echo "Pour l'export via API, vous devez être connecté en tant qu'admin."
    echo "Ou utilisez cette requête SQL directe..."

    # Export SQL direct
    if $COMPOSE_CMD exec -T database mysql -u parrainage_user -pparrainage_pass parrainage_db -e "
        SELECT 'ID','Nom','Prénom','Email','Classe','Motivation','Date inscription'
        UNION ALL
        SELECT id,nom,prenom,email,classe,COALESCE(motivation,''),date_inscription
        FROM inscriptions
        ORDER BY date_inscription DESC
    " | sed 's/\t/;/g' > "./exports/$export_file"; then
        echo -e "${GREEN}✅ Export créé: ./exports/$export_file${NC}"
    else
        echo -e "${RED}❌ Erreur lors de l'export${NC}"
    fi
}

# Fonction 8: Nettoyer
cleanup() {
    echo -e "${YELLOW}🧹 Nettoyage des containers et images...${NC}"

    echo "1. Nettoyer uniquement les containers arrêtés"
    echo "2. Nettoyer tout (containers, images, volumes)"
    echo "3. Nettoyer et reconstruire l'application"
    read -p "Votre choix (1-3): " clean_choice

    case $clean_choice in
        1)
            docker container prune -f
            echo -e "${GREEN}✅ Containers arrêtés supprimés${NC}"
            ;;
        2)
            $COMPOSE_CMD down -v --remove-orphans
            docker system prune -a -f
            echo -e "${GREEN}✅ Nettoyage complet effectué${NC}"
            ;;
        3)
            $COMPOSE_CMD down -v --remove-orphans
            docker system prune -a -f
            echo -e "${YELLOW}🔨 Reconstruction...${NC}"
            $COMPOSE_CMD up --build -d
            echo -e "${GREEN}✅ Application reconstruite et démarrée${NC}"
            ;;
        *)
            echo "Choix invalide"
            ;;
    esac
}

# Fonction 9: Diagnostique
diagnose() {
    echo -e "${BLUE}🔍 Diagnostic du système${NC}"
    echo "=========================="

    echo -e "\n${BLUE}📊 Informations système${NC}"
    echo "Docker version: $(docker --version)"
    echo "Docker Compose: $($COMPOSE_CMD --version)"
    echo "Espace disque disponible:"
    df -h | grep -E "(Filesystem|/dev/)"

    echo -e "\n${BLUE}🐳 Containers actifs${NC}"
    docker ps --format "table {{.Names}}\t{{.Status}}\t{{.Ports}}"

    echo -e "\n${BLUE}💾 Utilisation des resources${NC}"
    docker stats --no-stream --format "table {{.Container}}\t{{.CPUPerc}}\t{{.MemUsage}}"

    echo -e "\n${BLUE}🌐 Tests de connectivité${NC}"
    for port in 4200 8080 3306; do
        if lsof -Pi :$port -sTCP:LISTEN -t >/dev/null; then
            echo -e "Port $port: ${GREEN}✅ En écoute${NC}"
        else
            echo -e "Port $port: ${RED}❌ Libre/Non accessible${NC}"
        fi
    done

    echo -e "\n${BLUE}📁 Taille des volumes${NC}"
    docker volume ls -q | grep parrainage | while read volume; do
        size=$(docker system df -v | grep "$volume" | awk '{print $3}' 2>/dev/null || echo "N/A")
        echo "$volume: $size"
    done
}

# Fonction 10: Statistiques
show_statistics() {
    echo -e "${BLUE}📈 Statistiques de l'application${NC}"
    echo "================================="

    # Statistiques de la base de données
    echo -e "\n${BLUE}📊 Statistiques des inscriptions${NC}"
    $COMPOSE_CMD exec -T database mysql -u parrainage_user -pparrainage_pass parrainage_db -e "
        SELECT 'Total inscriptions' as Statistique, COUNT(*) as Valeur FROM inscriptions
        UNION ALL
        SELECT 'BUT1 (Filleuls)', COUNT(*) FROM inscriptions WHERE classe='BUT1'
        UNION ALL
        SELECT 'BUT2 (Parrains)', COUNT(*) FROM inscriptions WHERE classe='BUT2'
        UNION ALL
        SELECT 'BUT3 (Parrains)', COUNT(*) FROM inscriptions WHERE classe='BUT3'
        UNION ALL
        SELECT 'Inscriptions aujourd\\'hui', COUNT(*) FROM inscriptions WHERE DATE(date_inscription) = CURDATE()
        UNION ALL
        SELECT 'Inscriptions cette semaine', COUNT(*) FROM inscriptions WHERE YEARWEEK(date_inscription) = YEARWEEK(CURDATE())
    " 2>/dev/null || echo "Impossible de récupérer les statistiques de la DB"

    # Statistiques des containers
    echo -e "\n${BLUE}🐳 Statistiques des containers${NC}"
    echo "Uptime des services:"
    docker ps --format "table {{.Names}}\t{{.Status}}" | grep parrainage
}

# Boucle principale
while true; do
    show_menu

    case $choice in
        1) check_status ;;
        2) show_logs ;;
        3) restart_app ;;
        4) stop_app ;;
        5) start_app ;;
        6) backup_database ;;
        7) export_inscriptions ;;
        8) cleanup ;;
        9) diagnose ;;
        10) show_statistics ;;
        0) echo -e "${GREEN}Au revoir ! 👋${NC}"; exit 0 ;;
        *) echo -e "${RED}Choix invalide. Veuillez sélectionner 0-10.${NC}" ;;
    esac

    echo ""
    read -p "Appuyez sur Entrée pour continuer..."
done