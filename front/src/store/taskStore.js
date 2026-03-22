// =============================================================================
// Store des tâches (Zustand) - Gestion d'état avec Optimistic UI
// =============================================================================
// Ce store gère :
//   - La liste des tâches et leur chargement depuis l'API
//   - Les opérations CRUD (Create, Read, Update, Delete)
//   - L'Optimistic UI : mise à jour immédiate de l'interface AVANT
//     la réponse du serveur, puis correction si l'API échoue
//   - Les statistiques des tâches
// =============================================================================

import { create } from 'zustand';
import {
  fetchTasks,
  createTask,
  updateTask,
  deleteTask,
  fetchStats,
} from '../api/client';
import { toast } from 'sonner';

export const useTaskStore = create((set, get) => ({
  // --- État ---
  tasks: [],
  stats: null,
  isLoading: false,
  isStatsLoading: false,

  // =========================================================================
  // Action : Charger toutes les tâches depuis l'API
  // =========================================================================
  loadTasks: async () => {
    set({ isLoading: true });
    try {
      const tasks = await fetchTasks();
      set({ tasks, isLoading: false });
    } catch (error) {
      toast.error('Impossible de charger les tâches : ' + error.message);
      set({ isLoading: false });
    }
  },

  // =========================================================================
  // Action : Créer une nouvelle tâche
  // =========================================================================
  addTask: async (taskData) => {
    try {
      const newTask = await createTask(taskData);
      // Ajouter la tâche en tête de liste
      set((state) => ({ tasks: [newTask, ...state.tasks] }));
      toast.success('Tâche créée avec succès !');
      // Rafraîchir les statistiques
      get().loadStats();
      return true;
    } catch (error) {
      toast.error('Erreur lors de la création : ' + error.message);
      return false;
    }
  },

  // =========================================================================
  // Action : Modifier une tâche (avec Optimistic UI)
  // =========================================================================
  // L'Optimistic UI met à jour l'interface IMMÉDIATEMENT, sans attendre
  // la réponse du serveur. Si l'API échoue, on revient à l'état précédent.
  // =========================================================================
  editTask: async (taskId, taskData) => {
    // Sauvegarder l'état actuel pour pouvoir revenir en arrière (rollback)
    const previousTasks = get().tasks;

    // Mise à jour optimiste : modifier la tâche localement tout de suite
    set((state) => ({
      tasks: state.tasks.map((task) =>
        task.id === taskId ? { ...task, ...taskData } : task
      ),
    }));

    try {
      // Envoyer la modification au serveur
      const updatedTask = await updateTask(taskId, taskData);
      // Remplacer la version optimiste par la version serveur (source de vérité)
      set((state) => ({
        tasks: state.tasks.map((task) =>
          task.id === taskId ? updatedTask : task
        ),
      }));
      toast.success('Tâche mise à jour !');
      // Rafraîchir les stats
      get().loadStats();
    } catch (error) {
      // ROLLBACK : revenir à l'état précédent en cas d'erreur
      set({ tasks: previousTasks });
      toast.error('Erreur lors de la modification : ' + error.message);
    }
  },

  // =========================================================================
  // Action : Supprimer une tâche (avec Optimistic UI)
  // =========================================================================
  removeTask: async (taskId) => {
    // Sauvegarder pour le rollback
    const previousTasks = get().tasks;

    // Supprimer optimistiquement
    set((state) => ({
      tasks: state.tasks.filter((task) => task.id !== taskId),
    }));

    try {
      await deleteTask(taskId);
      toast.success('Tâche supprimée !');
      get().loadStats();
    } catch (error) {
      // ROLLBACK en cas d'erreur
      set({ tasks: previousTasks });
      toast.error('Erreur lors de la suppression : ' + error.message);
    }
  },

  // =========================================================================
  // Action : Charger les statistiques
  // =========================================================================
  loadStats: async () => {
    set({ isStatsLoading: true });
    try {
      const stats = await fetchStats();
      set({ stats, isStatsLoading: false });
    } catch (error) {
      toast.error('Impossible de charger les statistiques.');
      set({ isStatsLoading: false });
    }
  },
}));
