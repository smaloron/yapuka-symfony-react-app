// =============================================================================
// Page Dashboard - Vue principale de gestion des tâches
// =============================================================================
// Cette page affiche :
//   1. Les statistiques (compteurs et graphique)
//   2. Le formulaire de création rapide
//   3. La liste des tâches avec CRUD complet
//
// Le chargement des données se fait au montage du composant via useEffect.
// =============================================================================

import { useEffect } from 'react';
import { useTaskStore } from '../store/taskStore';
import StatsPanel from '../components/stats/StatsPanel';
import TaskForm from '../components/tasks/TaskForm';
import TaskList from '../components/tasks/TaskList';
import SkeletonLoader from '../components/ui/SkeletonLoader';

export default function DashboardPage() {
  // Récupérer les actions et l'état depuis le store Zustand
  const { loadTasks, loadStats, isLoading } = useTaskStore();

  // Charger les tâches et les stats au montage du composant
  useEffect(() => {
    loadTasks();
    loadStats();
  }, [loadTasks, loadStats]);

  return (
    <div className="space-y-8">
      {/* ================================================================= */}
      {/* Section 1 : Statistiques */}
      {/* ================================================================= */}
      <section>
        <h2 className="text-lg font-semibold text-gray-800 mb-4">
          Tableau de bord
        </h2>
        <StatsPanel />
      </section>

      {/* ================================================================= */}
      {/* Section 2 : Création rapide de tâche */}
      {/* ================================================================= */}
      <section>
        <h2 className="text-lg font-semibold text-gray-800 mb-4">
          Nouvelle tâche
        </h2>
        <TaskForm />
      </section>

      {/* ================================================================= */}
      {/* Section 3 : Liste des tâches */}
      {/* ================================================================= */}
      <section>
        <h2 className="text-lg font-semibold text-gray-800 mb-4">
          Mes tâches
        </h2>
        {/* Afficher un skeleton pendant le chargement, sinon la liste */}
        {isLoading ? <SkeletonLoader count={5} /> : <TaskList />}
      </section>
    </div>
  );
}
