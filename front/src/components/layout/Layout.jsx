// =============================================================================
// Composant Layout - Structure commune de l'application
// =============================================================================
// Ce composant fournit la structure visuelle partagée par toutes les pages
// protégées : header avec navigation, zone de contenu principal, et footer.
//
// Il intègre également :
//   - Le hook SSE pour les notifications en temps réel
//   - L'indicateur de connexion SSE (point vert/rouge)
//   - Le bouton de déconnexion
// =============================================================================

import { LogOut, CheckSquare, Wifi, WifiOff } from 'lucide-react';
import { useAuthStore } from '../../store/authStore';
import { useSSE } from '../../hooks/useSSE';
import { useNavigate } from 'react-router-dom';

export default function Layout({ children }) {
  // Récupérer les données utilisateur et la fonction de déconnexion
  const { user, logout } = useAuthStore();
  const navigate = useNavigate();

  // Activer l'écoute des notifications SSE
  const { isConnected } = useSSE();

  /**
   * Gère la déconnexion de l'utilisateur
   */
  const handleLogout = () => {
    logout();
    navigate('/login');
  };

  return (
    <div className="min-h-screen bg-gray-50">
      {/* ================================================================= */}
      {/* Header - Barre de navigation principale */}
      {/* ================================================================= */}
      <header className="bg-white shadow-sm border-b border-gray-200">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="flex justify-between items-center h-16">
            {/* --- Logo et nom de l'application --- */}
            <div className="flex items-center gap-3">
              <CheckSquare className="h-8 w-8 text-primary-600" />
              <h1 className="text-xl font-bold text-gray-900">Yapuka</h1>
            </div>

            {/* --- Zone droite : infos utilisateur et actions --- */}
            <div className="flex items-center gap-4">
              {/* Indicateur de connexion SSE */}
              <div className="flex items-center gap-1.5" title={
                isConnected ? 'Notifications actives' : 'Notifications déconnectées'
              }>
                {isConnected ? (
                  <Wifi className="h-4 w-4 text-green-500" />
                ) : (
                  <WifiOff className="h-4 w-4 text-red-400" />
                )}
                <span className={`h-2 w-2 rounded-full ${
                  isConnected ? 'bg-green-500' : 'bg-red-400'
                }`} />
              </div>

              {/* Nom de l'utilisateur connecté */}
              <span className="text-sm text-gray-600">
                {user?.username || user?.email}
              </span>

              {/* Bouton de déconnexion */}
              <button
                onClick={handleLogout}
                className="flex items-center gap-1.5 text-sm text-gray-500 hover:text-red-600 transition-colors"
                title="Se déconnecter"
              >
                <LogOut className="h-4 w-4" />
                <span className="hidden sm:inline">Déconnexion</span>
              </button>
            </div>
          </div>
        </div>
      </header>

      {/* ================================================================= */}
      {/* Contenu principal */}
      {/* ================================================================= */}
      <main className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {children}
      </main>

      {/* ================================================================= */}
      {/* Footer */}
      {/* ================================================================= */}
      <footer className="border-t border-gray-200 bg-white mt-auto">
        <div className="max-w-7xl mx-auto px-4 py-4 text-center text-sm text-gray-400">
          Yapuka — Projet de formation DevOps
        </div>
      </footer>
    </div>
  );
}
