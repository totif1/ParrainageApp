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
    echo "11. 🔧 Reconstruire le backend"
    echo "12. 🩺 Test de santé complet"
    echo "0. ❌ Quitter"
    echo ""
    read -p "Votre choix (0-12): " choice
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
    if curl -s --max-time 5 http://localhost:4200 > /dev/null; then
        echo -e "Frontend: ${GREEN}✅ Accessible${NC}"
    else
        echo -e "Frontend: ${RED}❌ Non accessible${NC}"
    fi

    # Test Backend - route principale
    if curl -s --max-time 5 http://localhost:8080/ > /dev/null; then
        echo -e "Backend:  ${GREEN}✅ Accessible${NC}"

        # Test spécifique de l'API
        api_response=$(curl -s --max-time 5 http://localhost:8080/ 2>/dev/null)
        if echo "$api_response" | grep -q "success.*true"; then
            echo -e "API:      ${GREEN}✅ Fonctionnelle${NC}"
        else
            echo -e "API:      ${YELLOW}⚠️  Réponse inattendue${NC}"
        fi
    else
        echo -e "Backend:  ${RED}❌ Non accessible${NC}"
        echo -e "API:      ${RED}❌ Non accessible${NC}"
    fi

    # Test Health check
    health_response=$(curl -s --max-time 5 http://localhost:8080/health 2>/dev/null)
    if echo "$health_response" | grep -q "healthy"; then
        echo -e "Health:   ${GREEN}✅ Healthy${NC}"
    else
        echo -e "Health:   ${YELLOW}⚠️  Non disponible${NC}"
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
    echo "6. Erreurs PHP uniquement"
    read -p "Votre choix (1-6): " log_choice

    case $log_choice in
        1) $COMPOSE_CMD logs --tail=50 ;;
        2) $COMPOSE_CMD logs --tail=50 frontend ;;
        3) $COMPOSE_CMD logs --tail=50 backend ;;
        4) $COMPOSE_CMD logs --tail=50 database ;;
        5) echo "Appuyez sur Ctrl+C pour arrêter"; $COMPOSE_CMD logs -f ;;
        6)
            echo "Logs d'erreurs PHP:"
            if [ -f "./backend/logs/php_errors.log" ]; then
                tail -50 ./backend/logs/php_errors.log
            else
                echo "Aucun fichier d'erreur PHP trouvé"
            fi
            ;;
        *) echo "Choix invalide" ;;
    esac
}

# Fonction 3: Redémarrer
restart_app() {
    echo -e "${YELLOW}🔄 Redémarrage de l'application...${NC}"
    $COMPOSE_CMD restart
    echo -e "${GREEN}✅ Application redémarrée${NC}"

    # Attendre un peu puis vérifier l'état
    sleep 10
    check_status
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

    # Attendre un peu puis vérifier l'état
    sleep 15
    check_status
}

# Fonction 6: Sauvegarder la DB
backup_database() {
    echo -e "${BLUE}🗃️  Sauvegarde de la base de données${NC}"

    # Créer le dossier de sauvegarde
    mkdir -p ./database/backup

    # Nom du fichier de sauvegarde
    backup_file="backup_$(date +%Y%m%d_%H%M%S).sql"

    # Effectuer la sauvegarde
    if $COMPOSE_CMD exec -T database mysqldump -u parrainage_user -pparrainage_password parrainage_db > "./database/backup/$backup_file"; then
        echo -e "${GREEN}✅ Sauvegarde créée: ./database/backup/$backup_file${NC}"

        # Afficher la taille du fichier
        size=$(du -h "./database/backup/$backup_file" | cut -f1)
        echo "   Taille: $size"

        # Nettoyer les anciennes sauvegardes (garder les 10 plus récentes)
        find "./database/backup" -name "backup_*.sql" -type f | sort -r | tail -n +11 | xargs -r rm
        echo "   Anciennes sauvegardes nettoyées"
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

    # Export SQL direct avec en-têtes CSV
    if $COMPOSE_CMD exec -T database mysql -u parrainage_user -pparrainage_password parrainage_db -e "
        SELECT 'ID','Nom','Prénom','Email','Classe','Motivation','Date inscription'
        UNION ALL
        SELECT id,nom,prenom,email,classe,COALESCE(motivation,''),date_inscription
        FROM inscriptions
        ORDER BY date_inscription DESC
    " | sed 's/\t/;/g' > "./exports/$export_file"; then
        echo -e "${GREEN}✅ Export créé: ./exports/$export_file${NC}"

        # Compter les lignes (moins l'en-tête)
        lines=$(wc -l < "./exports/$export_file")
        records=$((lines - 1))
        echo "   Nombre d'inscriptions: $records"
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
    echo "4. Reset complet (ATTENTION: supprime toutes les données)"
    read -p "Votre choix (1-4): " clean_choice

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
            echo -e "${YELLOW}⚠️  Arrêt et suppression des services...${NC}"
            $COMPOSE_CMD down -v --remove-orphans
            docker system prune -a -f
            echo -e "${YELLOW}🔨 Reconstruction...${NC}"
            $COMPOSE_CMD build --no-cache
            $COMPOSE_CMD up -d
            echo -e "${GREEN}✅ Application reconstruite et démarrée${NC}"
            ;;
        4)
            echo -e "${RED}⚠️  ATTENTION: Ceci supprimera TOUTES les données !${NC}"
            read -p "Êtes-vous sûr ? Tapez 'CONFIRMER' pour continuer: " confirmation
            if [ "$confirmation" = "CONFIRMER" ]; then
                $COMPOSE_CMD down -v --remove-orphans
                docker system prune -a -f --volumes
                rm -rf ./database/backup/* ./exports/* ./backend/logs/*
                echo -e "${GREEN}✅ Reset complet effectué${NC}"
            else
                echo "Operation annulée"
            fi
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
    df -h | grep -E "(Filesystem|/dev/)" | head -3

    echo -e "\n${BLUE}🐳 Containers actifs${NC}"
    docker ps --format "table {{.Names}}\t{{.Status}}\t{{.Ports}}"

    echo -e "\n${BLUE}💾 Utilisation des resources${NC}"
    docker stats --no-stream --format "table {{.Container}}\t{{.CPUPerc}}\t{{.MemUsage}}" 2>/dev/null || echo "Docker stats non disponible"

    echo -e "\n${BLUE}🌐 Tests de connectivité${NC}"
    for port in 4200 8080 3306; do
        if command -v lsof >/dev/null && lsof -Pi :$port -sTCP:LISTEN -t >/dev/null; then
            echo -e "Port $port: ${GREEN}✅ En écoute${NC}"
        else
            echo -e "Port $port: ${RED}❌ Libre/Non accessible${NC}"
        fi
    done

    echo -e "\n${BLUE}📁 Taille des volumes Docker${NC}"
    docker volume ls -q | grep parrainage 2>/dev/null | while read volume; do
        size=$(docker system df -v 2>/dev/null | grep "$volume" | awk '{print $3}' || echo "N/A")
        echo "$volume: $size"
    done

    echo -e "\n${BLUE}🗂️  Fichiers de l'application${NC}"
    files_to_check=(
        "./docker-compose.yml"
        "./backend/Dockerfile"
        "./backend/composer.json"
        "./backend/public/index.php"
        "./backend/vendor/autoload.php"
        "./frontend/Dockerfile"
    )

    for file in "${files_to_check[@]}"; do
        if [ -f "$file" ] || [ -d "$file" ]; then
            echo -e "$file: ${GREEN}✅ Présent${NC}"
        else
            echo -e "$file: ${RED}❌ Manquant${NC}"
        fi
    done

    # Vérifier les logs d'erreur
    echo -e "\n${BLUE}🚨 Dernières erreurs${NC}"
    if [ -f "./backend/logs/php_errors.log" ]; then
        echo "Dernières erreurs PHP:"
        tail -5 "./backend/logs/php_errors.log" 2>/dev/null || echo "Aucune erreur récente"
    else
        echo "Fichier d'erreur PHP non trouvé"
    fi
}

# Fonction 10: Statistiques
show_statistics() {
    echo -e "${BLUE}📈 Statistiques de l'application${NC}"
    echo "================================="

    # Statistiques de la base de données
    echo -e "\n${BLUE}📊 Statistiques des inscriptions${NC}"
    if $COMPOSE_CMD exec -T database mysql -u parrainage_user -pparrainage_password parrainage_db -e "
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
    " 2>/dev/null; then
        echo -e "${GREEN}✅ Statistiques récupérées${NC}"
    else
        echo -e "${RED}❌ Impossible de récupérer les statistiques de la DB${NC}"
    fi

    # Statistiques des containers
    echo -e "\n${BLUE}🐳 Statistiques des containers${NC}"
    echo "Uptime des services:"
    docker ps --format "table {{.Names}}\t{{.Status}}" | grep parrainage || echo "Aucun container parrainage actif"

    # Statistiques des fichiers
    echo -e "\n${BLUE}📁 Taille des dossiers${NC}"
    if [ -d "./database/backup" ]; then
        backup_size=$(du -sh "./database/backup" 2>/dev/null | cut -f1)
        backup_count=$(find "./database/backup" -name "*.sql" | wc -l)
        echo "Sauvegardes: $backup_size ($backup_count fichiers)"
    fi

    if [ -d "./exports" ]; then
        export_size=$(du -sh "./exports" 2>/dev/null | cut -f1)
        export_count=$(find "./exports" -name "*.csv" | wc -l)
        echo "Exports: $export_size ($export_count fichiers)"
    fi
}

# Fonction 11: Reconstruire le backend
rebuild_backend() {
    echo -e "${BLUE}🔧 Reconstruction du backend${NC}"
    echo "============================"

    echo -e "${YELLOW}⏸️  Arrêt du backend...${NC}"
    $COMPOSE_CMD stop backend

    echo -e "${YELLOW}🗑️  Suppression de l'ancienne image...${NC}"
    docker rmi parrainfilleul-backend 2>/dev/null || true

    echo -e "${YELLOW}🔨 Reconstruction...${NC}"
    if $COMPOSE_CMD build --no-cache backend; then
        echo -e "${GREEN}✅ Backend reconstruit avec succès${NC}"

        echo -e "${YELLOW}▶️  Redémarrage...${NC}"
        $COMPOSE_CMD up -d backend

        # Attendre et tester
        sleep 10
        if curl -s --max-time 10 http://localhost:8080/ >/dev/null; then
            echo -e "${GREEN}✅ Backend fonctionnel${NC}"
        else
            echo -e "${RED}❌ Backend ne répond pas${NC}"
            echo "Logs du backend:"
            $COMPOSE_CMD logs --tail=10 backend
        fi
    else
        echo -e "${RED}❌ Erreur lors de la reconstruction${NC}"
    fi
}

# Fonction 12: Test de santé complet
health_check() {
    echo -e "${BLUE}🩺 Test de santé complet${NC}"
    echo "========================"

    # Test 1: Containers
    echo -e "\n${BLUE}1. État des containers${NC}"
    containers_ok=true
    for container in parrainage_db parrainage_backend parrainage_frontend; do
        if docker ps --format '{{.Names}}' | grep -q "^$container$"; then
            status=$(docker ps --format '{{.Names}} {{.Status}}' | grep "^$container" | cut -d' ' -f2-)
            echo -e "$container: ${GREEN}✅ Running ($status)${NC}"
        else
            echo -e "$container: ${RED}❌ Arrêté${NC}"
            containers_ok=false
        fi
    done

    # Test 2: Connectivité réseau
    echo -e "\n${BLUE}2. Tests de connectivité${NC}"
    services=(
        "http://localhost:4200|Frontend"
        "http://localhost:8080/|Backend"
        "http://localhost:8080/health|Health Check"
    )

    connectivity_ok=true
    for service in "${services[@]}"; do
        IFS='|' read -r url name <<< "$service"
        if curl -s --max-time 5 "$url" >/dev/null 2>&1; then
            echo -e "$name: ${GREEN}✅ Accessible${NC}"
        else
            echo -e "$name: ${RED}❌ Non accessible${NC}"
            connectivity_ok=false
        fi
    done

    # Test 3: Base de données
    echo -e "\n${BLUE}3. Test de la base de données${NC}"
    if $COMPOSE_CMD exec -T database mysql -u parrainage_user -pparrainage_password -e "SELECT 1" parrainage_db >/dev/null 2>&1; then
        echo -e "Connexion DB: ${GREEN}✅ OK${NC}"

        # Test des tables
        table_count=$($COMPOSE_CMD exec -T database mysql -u parrainage_user -pparrainage_password parrainage_db -e "SHOW TABLES" 2>/dev/null | wc -l)
        if [ "$table_count" -gt 1 ]; then
            echo -e "Tables DB: ${GREEN}✅ $((table_count-1)) tables trouvées${NC}"
        else
            echo -e "Tables DB: ${YELLOW}⚠️  Aucune table trouvée${NC}"
        fi
    else
        echo -e "Base de données: ${RED}❌ Erreur de connexion${NC}"
    fi

    # Test 4: API fonctionnelle
    echo -e "\n${BLUE}4. Test de l'API${NC}"
    api_response=$(curl -s --max-time 10 http://localhost:8080/ 2>/dev/null)
    if echo "$api_response" | grep -q "success.*true"; then
        echo -e "API Response: ${GREEN}✅ JSON valide${NC}"

        if echo "$api_response" | grep -q "autoloader.*OK"; then
            echo -e "Autoloader: ${GREEN}✅ OK${NC}"
        else
            echo -e "Autoloader: ${YELLOW}⚠️  Status inconnu${NC}"
        fi
    else
        echo -e "API Response: ${RED}❌ Réponse invalide${NC}"
        echo "Réponse reçue: $api_response"
    fi

    # Résumé final
    echo -e "\n${BLUE}📋 Résumé${NC}"
    echo "========="

    if $containers_ok && $connectivity_ok; then
        echo -e "${GREEN}🎉 Système en bonne santé !${NC}"
    else
        echo -e "${RED}⚠️  Problèmes détectés${NC}"
        echo ""
        echo "Actions recommandées:"
        if ! $containers_ok; then
            echo "- Redémarrer les containers: $COMPOSE_CMD restart"
        fi
        if ! $connectivity_ok; then
            echo "- Vérifier les ports et les services"
            echo "- Reconstruire le backend: option 11"
        fi
    fi
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
        11) rebuild_backend ;;
        12) health_check ;;
        0) echo -e "${GREEN}Au revoir ! 👋${NC}"; exit 0 ;;
        *) echo -e "${RED}Choix invalide. Veuillez sélectionner 0-12.${NC}" ;;
    esac

    echo ""
    read -p "Appuyez sur Entrée pour continuer..."
done