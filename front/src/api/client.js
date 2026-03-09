// =============================================================================
// Client API - Gestion centralisée des requêtes HTTP
// =============================================================================
// Ce module encapsule toutes les communications avec le backend Symfony.
// Il gère automatiquement :
//   - L'ajout du header Authorization (JWT)
//   - La sérialisation/désérialisation JSON
//   - La gestion des erreurs HTTP
//   - La redirection vers /login en cas de token expiré (401)
// =============================================================================

// URL de base de l'API (définie dans les variables d'environnement Vite)
const API_BASE_URL = import.meta.env.VITE_API_URL || '';

/**
 * Fonction utilitaire pour effectuer des requêtes HTTP vers l'API.
 *
 * @param {string} endpoint - Le chemin de l'endpoint (ex: '/api/tasks')
 * @param {object} options - Options de la requête (method, body, etc.)
 * @returns {Promise<any>} - Les données JSON de la réponse
 * @throws {Error} - En cas d'erreur HTTP
 */
async function apiRequest(endpoint, options = {}) {
  // Récupérer le token JWT depuis le localStorage
  const token = localStorage.getItem('yapuka_token');

  // Construire les headers de la requête
  const headers = {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
    ...options.headers,
  };

  // Ajouter le header d'authentification si un token est disponible
  if (token) {
    headers['Authorization'] = `Bearer ${token}`;
  }

  // Effectuer la requête HTTP avec fetch
  const response = await fetch(`${API_BASE_URL}${endpoint}`, {
    ...options,
    headers,
  });


  // Gérer le cas du token expiré (401 Unauthorized)
  if (response.status === 401) {

    // Nettoyer le token invalide
    localStorage.removeItem('yapuka_token');
    localStorage.removeItem('yapuka_user');
    // Rediriger vers la page de connexion
    window.location.href = '/login';
    throw new Error('Session expirée. Veuillez vous reconnecter.');

  }

  // Gérer les autres erreurs HTTP
  if (!response.ok) {
    const errorData = await response.json().catch(() => ({}));
    throw new Error(errorData.error || errorData.message || `Erreur ${response.status}`);
  }

  // Retourner les données JSON (ou null si pas de contenu)
  if (response.status === 204) {
    return null;
  }

  return response.json();
}

// =============================================================================
// Endpoints d'authentification
// =============================================================================

/**
 * Inscription d'un nouvel utilisateur
 */
export async function registerUser(email, username, password) {
  return apiRequest('/api/auth/register', {
    method: 'POST',
    body: JSON.stringify({ email, username, password }),
  });
}

/**
 * Connexion et obtention du JWT
 */
export async function loginUser(email, password) {
  return apiRequest('/api/auth/login', {
    method: 'POST',
    body: JSON.stringify({ email, password }),
  });
}

// =============================================================================
// Endpoints des tâches (CRUD)
// =============================================================================

/**
 * Récupérer la liste des tâches de l'utilisateur connecté
 */
export async function fetchTasks() {
  return apiRequest('/api/tasks');
}

/**
 * Créer une nouvelle tâche
 */
export async function createTask(taskData) {
  return apiRequest('/api/tasks', {
    method: 'POST',
    body: JSON.stringify(taskData),
  });
}

/**
 * Modifier une tâche existante
 */
export async function updateTask(taskId, taskData) {
  return apiRequest(`/api/tasks/${taskId}`, {
    method: 'PUT',
    body: JSON.stringify(taskData),
  });
}

/**
 * Supprimer une tâche
 */
export async function deleteTask(taskId) {
  return apiRequest(`/api/tasks/${taskId}`, {
    method: 'DELETE',
  });
}

/**
 * Récupérer les statistiques des tâches
 */
export async function fetchStats() {
  return apiRequest('/api/tasks/stats');
}
