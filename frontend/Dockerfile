# Étape 1: Construction de l'application Angular
FROM node:18-alpine AS build

# Définir le répertoire de travail
WORKDIR /app

# Copier les fichiers de configuration npm
COPY package*.json ./

# Installer les dépendances
RUN npm ci --only=production

# Copier tout le code source
COPY . .

# Construire l'application pour la production
RUN npm run build

# Étape 2: Servir l'application avec Nginx
FROM nginx:alpine

# Copier la configuration Nginx personnalisée
COPY nginx.conf /etc/nginx/conf.d/default.conf

# Copier les fichiers construits depuis l'étape précédente
COPY --from=build /app/dist/parrainage-but /usr/share/nginx/html

# Exposer le port 80
EXPOSE 80

# Commande par défaut
CMD ["nginx", "-g", "daemon off;"]
