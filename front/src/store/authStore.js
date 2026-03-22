// =============================================================================
// Store d'authentification (Zustand)
// =============================================================================
// Zustand est une bibliothèque de gestion d'état légère pour React.
// Ce store gère :
//   - L'état de connexion de l'utilisateur (token JWT, infos user)
//   - Les actions d'inscription, connexion et déconnexion
//   - La persistance du token dans localStorage
//
// Utilisation dans un composant :
//   const { user, login, logout } = useAuthStore();
// =============================================================================

import { create } from 'zustand';
import { loginUser, registerUser } from '../api/client';

export const useAuthStore = create((set) => ({
  // --- État initial ---
  // On récupère le token et l'utilisateur depuis localStorage (persistance)
  token: localStorage.getItem('yapuka_token') || null,
  user: JSON.parse(localStorage.getItem('yapuka_user') || 'null'),
  isLoading: false,
  error: null,

  // =========================================================================
  // Action : Connexion
  // =========================================================================
  login: async (email, password) => {
    // Mettre l'état en "chargement" et réinitialiser les erreurs
    set({ isLoading: true, error: null });

    try {
      // Appel API pour obtenir le JWT
      const data = await loginUser(email, password);

      // Sauvegarder le token et les infos utilisateur dans localStorage
      localStorage.setItem('yapuka_token', data.token);
      localStorage.setItem('yapuka_user', JSON.stringify(data.user));

      // Mettre à jour le store
      set({
        token: data.token,
        user: data.user,
        isLoading: false,
      });

      return true; // Succès
    } catch (error) {
      set({
        error: error.message,
        isLoading: false,
      });
      return false; // Échec
    }
  },

  // =========================================================================
  // Action : Inscription
  // =========================================================================
  register: async (email, username, password) => {
    set({ isLoading: true, error: null });

    try {
      const data = await registerUser(email, username, password);

      // Après inscription réussie, connecter directement l'utilisateur
      localStorage.setItem('yapuka_token', data.token);
      localStorage.setItem('yapuka_user', JSON.stringify(data.user));

      set({
        token: data.token,
        user: data.user,
        isLoading: false,
      });

      return true;
    } catch (error) {
      set({
        error: error.message,
        isLoading: false,
      });
      return false;
    }
  },

  // =========================================================================
  // Action : Déconnexion
  // =========================================================================
  logout: () => {
    // Supprimer les données de localStorage
    localStorage.removeItem('yapuka_token');
    localStorage.removeItem('yapuka_user');

    // Réinitialiser le store
    set({
      token: null,
      user: null,
      error: null,
    });
  },

  // =========================================================================
  // Action : Effacer les erreurs
  // =========================================================================
  clearError: () => set({ error: null }),
}));
