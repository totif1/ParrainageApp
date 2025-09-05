FROM node:18-alpine

WORKDIR /app

# Copy package files
COPY package*.json ./

# Install dependencies and Angular CLI globally
RUN npm install && npm install -g @angular/cli

# Copy source code
COPY . .

# Expose port 4200
EXPOSE 4200

# Start the development server with proper host binding
CMD ["ng", "serve", "--host", "0.0.0.0", "--port", "4200", "--poll", "2000"]
