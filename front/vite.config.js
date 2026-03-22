// =============================================================================
// Configuration Vite - Bundler JavaScript ultra-rapide
// =============================================================================
// Vite gère :
//   - Le Hot Module Replacement (HMR) en développement
//   - Le build optimisé pour la production
//   - Le proxy des requêtes API vers le backend Symfony
// =============================================================================

import { defineConfig } from 'vite';
import react from '@vitejs/plugin-react';

export default defineConfig({
  // Plugin React : active le JSX et le Fast Refresh
  plugins: [react()],

  server: {
    // Port du serveur de développement
    port: 5173,
    // Écouter sur toutes les interfaces (nécessaire dans Docker)
    host: '0.0.0.0',
    // Proxy pour rediriger les appels API vers le backend
    // Évite les problèmes de CORS en développement
    proxy: {
      '/api': {
        target: 'http://php:9000',
        changeOrigin: true,
      },
    },
  },

  build: {
    // Répertoire de sortie pour le build de production
    outDir: 'dist',
    // Générer un rapport de taille des bundles
    reportCompressedSize: true,
  },
});
