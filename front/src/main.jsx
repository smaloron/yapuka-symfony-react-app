// =============================================================================
// Point d'entrée React - Initialisation de l'application
// =============================================================================
// Ce fichier monte l'application React dans le DOM.
// Il configure :
//   - Le routeur (React Router)
//   - Le système de notifications (Sonner)
//   - Le mode strict de React (détection des problèmes en développement)
// =============================================================================

import React from 'react';
import ReactDOM from 'react-dom/client';
import { BrowserRouter } from 'react-router-dom';
import { Toaster } from 'sonner';
import App from './App';
import './index.css';

// Récupérer l'élément DOM racine
const rootElement = document.getElementById('root');

// Créer la racine React et monter l'application
ReactDOM.createRoot(rootElement).render(
  <React.StrictMode>
    {/* BrowserRouter : active le routage côté client (SPA) */}
    <BrowserRouter>
      {/* Composant principal de l'application */}
      <App />
      {/* Toaster : affiche les notifications éphémères (toasts) */}
      <Toaster
        position="top-right"
        richColors
        closeButton
      />
    </BrowserRouter>
  </React.StrictMode>
);
