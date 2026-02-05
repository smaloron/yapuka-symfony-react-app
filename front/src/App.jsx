// =============================================================================
// Composant App - Racine de l'application et configuration des routes
// =============================================================================
// Définit la structure de navigation de l'application :
//   /login    → Page de connexion
//   /register → Page d'inscription
//   /         → Dashboard des tâches (protégé par authentification)
//
// Le composant ProtectedRoute vérifie la présence d'un JWT valide
// avant d'autoriser l'accès aux pages protégées.
// =============================================================================

import { Routes, Route, Navigate } from 'react-router-dom';
import { useAuthStore } from './store/authStore';
import LoginPage from './pages/LoginPage';
import RegisterPage from './pages/RegisterPage';
import DashboardPage from './pages/DashboardPage';
import Layout from './components/layout/Layout';

/**
 * Composant de protection des routes.
 * Redirige vers /login si l'utilisateur n'est pas authentifié.
 */
function ProtectedRoute({ children }) {
  // Vérifier la présence du token dans le store Zustand
  const token = useAuthStore((state) => state.token);

  if (!token) {
    // Pas de token → redirection vers la page de connexion
    return <Navigate to="/login" replace />;
  }

  // Token présent → afficher le contenu protégé
  return children;
}

/**
 * Composant principal de l'application.
 * Configure toutes les routes et leur protection.
 */
export default function App() {
  return (
    <Routes>
      {/* --- Routes publiques (accessibles sans authentification) --- */}
      <Route path="/login" element={<LoginPage />} />
      <Route path="/register" element={<RegisterPage />} />

      {/* --- Routes protégées (nécessitent un JWT valide) --- */}
      <Route
        path="/"
        element={
          <ProtectedRoute>
            {/* Layout : structure commune (header, sidebar, footer) */}
            <Layout>
              <DashboardPage />
            </Layout>
          </ProtectedRoute>
        }
      />

      {/* --- Route par défaut : redirection vers le dashboard --- */}
      <Route path="*" element={<Navigate to="/" replace />} />
    </Routes>
  );
}
