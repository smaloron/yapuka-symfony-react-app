# =============================================================================
# Dockerfile - Frontend React (Vite dev server)
# =============================================================================
# En développement : lance le serveur Vite avec Hot Module Replacement (HMR).
# En production : on ferait un build statique et on servirait avec Nginx.
# =============================================================================

FROM node:22-alpine

# --- Répertoire de travail ---
WORKDIR /app

# --- Copier les fichiers de dépendances en premier (cache Docker) ---
# Docker met en cache cette étape tant que package.json ne change pas
COPY package.json package-lock.json* ./

# --- Installer les dépendances ---
RUN npm install

# --- Copier le reste du code source ---
COPY . .

# --- Exposer le port du serveur Vite ---
EXPOSE 5173

# --- Lancer le serveur de développement Vite ---
# --host 0.0.0.0 : écouter sur toutes les interfaces (nécessaire dans Docker)
CMD ["npm", "run", "dev", "--", "--host", "0.0.0.0"]
