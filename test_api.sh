#!/bin/bash

# Script de test de l'API Parrainage BUT
echo "🧪 Test de l'API Parrainage BUT Informatique"
echo "=========================================="

API_URL="http://localhost:8080/api"

# Couleurs pour les messages
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Fonction pour tester un endpoint
test_endpoint() {
    local method=$1
    local endpoint=$2
    local data=$3
    local description=$4
    local auth_header=$5

    echo -e "\n${YELLOW}Test: $description${NC}"
    echo "Endpoint: $method $endpoint"

    if [ "$method" = "POST" ] && [ ! -z "$data" ]; then
        if [ ! -z "$auth_header" ]; then
            response=$(curl -s -w "HTTP_CODE:%{http_code}" -X $method \
                -H "Content-Type: application/json" \
                -H "Authorization: Bearer $auth_header" \
                -d "$data" \
                "$API_URL$endpoint")
        else
            response=$(curl -s -w "HTTP_CODE:%{http_code}" -X $method \
                -H "Content-Type: application/json" \
                -d "$data" \
                "$API_URL$endpoint")
        fi
    else
        if [ ! -z "$auth_header" ]; then
            response=$(curl -s -w "HTTP_CODE:%{http_code}" -X $method \
                -H "Authorization: Bearer $auth_header" \
                "$API_URL$endpoint")
        else
            response=$(curl -s -w "HTTP_CODE:%{http_code}" -X $method \
                "$API_URL$endpoint")
        fi
    fi

    http_code=$(echo "$response" | grep -o "HTTP_CODE:[0-9]*" | cut -d: -f2)
    response_body=$(echo "$response" | sed 's/HTTP_CODE:[0-9]*$//')

    if [ "$http_code" -ge 200 ] && [ "$http_code" -lt 300 ]; then
        echo -e "${GREEN}✅ SUCCESS ($http_code)${NC}"
        echo "$response_body" | python3 -m json.tool 2>/dev/null || echo "$response_body"
    else
        echo -e "${RED}❌ FAILED ($http_code)${NC}"
        echo "$response_body"
    fi
}

# Attendre que l'API soit prête
echo "⏳ Attente que l'API soit prête..."
for i in {1..30}; do
    if curl -s "$API_URL/" > /dev/null 2>&1; then
        echo -e "${GREEN}✅ API prête !${NC}"
        break
    fi
    echo "Tentative $i/30..."
    sleep 2
done

# Test 1: Info API
test_endpoint "GET" "/" "" "Informations de l'API"

# Test 2: Création d'inscription
test_endpoint "POST" "/inscriptions" '{
    "nom": "Test",
    "prenom": "Utilisateur",
    "email": "test.api@example.com",
    "classe": "BUT2",
    "motivation": "Test automatique de l API"
}' "Création d'une nouvelle inscription"

# Test 3: Connexion admin
echo -e "\n${YELLOW}Test: Connexion administrateur${NC}"
login_response=$(curl -s -X POST \
    -H "Content-Type: application/json" \
    -d '{"username": "admin", "password": "admin123"}' \
    "$API_URL/auth/login")

echo "$login_response" | python3 -m json.tool 2>/dev/null || echo "$login_response"

# Extraire le token JWT
token=$(echo "$login_response" | python3 -c "import sys, json; data=json.load(sys.stdin); print(data.get('token', ''))" 2>/dev/null)

if [ ! -z "$token" ]; then
    echo -e "${GREEN}✅ Token JWT récupéré${NC}"

    # Test 4: Récupération des inscriptions (avec auth)
    test_endpoint "GET" "/inscriptions" "" "Récupération des inscriptions (admin)" "$token"

    # Test 5: Statistiques (avec auth)
    test_endpoint "GET" "/inscriptions/stats" "" "Récupération des statistiques (admin)" "$token"

    # Test 6: Vérification du token
    test_endpoint "POST" "/auth/verify" "" "Vérification du token JWT" "$token"

else
    echo -e "${RED}❌ Impossible de récupérer le token JWT${NC}"
fi

# Test 7: Endpoint non existant
test_endpoint "GET" "/nonexistent" "" "Test endpoint non existant (devrait retourner 404)"

# Test 8: Inscription avec données invalides
test_endpoint "POST" "/inscriptions" '{
    "nom": "",
    "email": "invalid-email",
    "classe": "INVALID"
}' "Inscription avec données invalides (devrait échouer)"

echo -e "\n🏁 ${GREEN}Tests terminés !${NC}"
echo "Pour tester manuellement :"
echo "- Frontend: http://localhost:4200"
echo "- API: http://localhost:8080/api"
echo "- Admin: admin / admin123"