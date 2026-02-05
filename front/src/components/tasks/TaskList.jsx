// =============================================================================
// Composant TaskList - Liste des tâches avec Empty State
// =============================================================================
// Affiche la liste des tâches sous forme de cartes.
// Si la liste est vide, affiche un message d'encouragement (Empty State).
// =============================================================================

import { useTaskStore } from '../../store/taskStore';
import TaskCard from './TaskCard';
import { ClipboardList } from 'lucide-react';

export default function TaskList() {
  const tasks = useTaskStore((state) => state.tasks);

  // =========================================================================
  // Empty State : aucune tâche à afficher
  // =========================================================================
  if (tasks.length === 0) {
    return (
      <div className="bg-white rounded-xl border border-gray-200 p-12 text-center">
        <ClipboardList className="h-16 w-16 text-gray-300 mx-auto mb-4" />
        <h3 className="text-lg font-medium text-gray-600 mb-2">
          Bravo, vous n'avez rien à faire ! 🎉
        </h3>
        <p className="text-sm text-gray-400">
          Créez votre première tâche avec le formulaire ci-dessus.
        </p>
      </div>
    );
  }

  // =========================================================================
  // Liste des tâches
  // =========================================================================
  return (
    <div className="space-y-3">
      {tasks.map((task) => (
        <TaskCard key={task.id} task={task} />
      ))}
    </div>
  );
}
