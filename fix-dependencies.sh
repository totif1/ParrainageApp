#!/bin/bash

echo "🔧 Correction des dépendances Angular standalone..."

# 1. Nettoyer le dossier node_modules et package-lock.json
cd frontend
echo "Nettoyage des anciennes dépendances..."
rm -rf node_modules package-lock.json

# 2. Supprimer les anciens fichiers modules (si ils existent encore)
echo "Suppression des anciens fichiers modules..."
rm -f src/app/app.module.ts 2>/dev/null
rm -f src/app/app-routing.module.ts 2>/dev/null

# 3. Installer les nouvelles dépendances
echo "Installation des nouvelles dépendances Angular 16 standalone..."
npm install --legacy-peer-deps

# 4. Vérifier que tout est en ordre
echo "Vérification de l'installation..."
npm ls @angular/core

# 5. Retourner au dossier racine
cd ..

echo "✅ Configuration terminée !"
echo ""
echo "🚀 Maintenant vous pouvez lancer :"
echo "sudo ./quick-start.sh"