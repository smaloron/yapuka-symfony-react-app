// =============================================================================
// Composant TaskCard - Carte individuelle d'une tâche
// =============================================================================
// Chaque tâche est affichée dans une carte avec :
//   - Checkbox pour cocher/décocher (Optimistic UI)
//   - Titre et description (éditable en cliquant)
//   - Badge de priorité et de statut
//   - Date d'échéance (en rouge si dépassée)
//   - Boutons d'action : modifier, supprimer
//
// La suppression nécessite une confirmation via ConfirmDialog.
// =============================================================================

import { useState } from 'react';
import { useTaskStore } from '../../store/taskStore';
import ConfirmDialog from '../ui/ConfirmDialog';
import {
  Trash2, Pencil, Check, X, Calendar, AlertTriangle,
} from 'lucide-react';

/**
 * Mappe les priorités vers des classes CSS de couleur
 */
const PRIORITY_STYLES = {
  low: 'bg-green-100 text-green-700',
  medium: 'bg-yellow-100 text-yellow-700',
  high: 'bg-red-100 text-red-700',
};

/**
 * Mappe les priorités vers des labels lisibles
 */
const PRIORITY_LABELS = {
  low: 'Basse',
  medium: 'Moyenne',
  high: 'Haute',
};

/**
 * Mappe les statuts vers des labels lisibles
 */
const STATUS_LABELS = {
  todo: 'À faire',
  in_progress: 'En cours',
  done: 'Terminée',
};

export default function TaskCard({ task }) {
  const { editTask, removeTask } = useTaskStore();

  // État local pour l'édition inline
  const [isEditing, setIsEditing] = useState(false);
  const [editTitle, setEditTitle] = useState(task.title);
  const [editDescription, setEditDescription] = useState(task.description || '');

  // État local pour la modale de confirmation de suppression
  const [showConfirm, setShowConfirm] = useState(false);

  // Vérifier si la tâche est en retard
  const isOverdue = task.dueDate &&
    task.status !== 'done' &&
    new Date(task.dueDate) < new Date();

  // =========================================================================
  // Action : basculer le statut (todo ↔ done)
  // =========================================================================
  const handleToggleStatus = () => {
    const newStatus = task.status === 'done' ? 'todo' : 'done';
    editTask(task.id, { status: newStatus });
  };

  // =========================================================================
  // Action : changer le statut via le sélecteur
  // =========================================================================
  const handleStatusChange = (event) => {
    editTask(task.id, { status: event.target.value });
  };

  // =========================================================================
  // Action : sauvegarder les modifications inline
  // =========================================================================
  const handleSaveEdit = () => {
    if (editTitle.trim()) {
      editTask(task.id, {
        title: editTitle.trim(),
        description: editDescription.trim() || null,
      });
      setIsEditing(false);
    }
  };

  // =========================================================================
  // Action : annuler l'édition
  // =========================================================================
  const handleCancelEdit = () => {
    setEditTitle(task.title);
    setEditDescription(task.description || '');
    setIsEditing(false);
  };

  // =========================================================================
  // Action : supprimer avec confirmation
  // =========================================================================
  const handleDelete = () => {
    removeTask(task.id);
    setShowConfirm(false);
  };

  return (
    <>
      <div className={`bg-white rounded-xl border p-4 transition-all ${
        task.status === 'done'
          ? 'border-green-200 bg-green-50/50'
          : isOverdue
            ? 'border-red-200'
            : 'border-gray-200'
      }`}>
        <div className="flex items-start gap-3">
          {/* --- Checkbox pour cocher/décocher la tâche --- */}
          <button
            onClick={handleToggleStatus}
            className={`mt-0.5 h-5 w-5 rounded border-2 flex items-center justify-center transition-colors flex-shrink-0 ${
              task.status === 'done'
                ? 'bg-green-500 border-green-500 text-white'
                : 'border-gray-300 hover:border-primary-500'
            }`}
            title={task.status === 'done' ? 'Rouvrir la tâche' : 'Marquer comme terminée'}
          >
            {task.status === 'done' && <Check className="h-3 w-3" />}
          </button>

          {/* --- Contenu principal --- */}
          <div className="flex-1 min-w-0">
            {isEditing ? (
              // ===============================================================
              // Mode édition
              // ===============================================================
              <div className="space-y-2">
                <input
                  type="text"
                  value={editTitle}
                  onChange={(e) => setEditTitle(e.target.value)}
                  className="w-full px-2 py-1 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-primary-500 text-sm"
                  autoFocus
                />
                <input
                  type="text"
                  value={editDescription}
                  onChange={(e) => setEditDescription(e.target.value)}
                  placeholder="Description..."
                  className="w-full px-2 py-1 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-primary-500 text-sm"
                />
                <div className="flex gap-2">
                  <button
                    onClick={handleSaveEdit}
                    className="text-xs bg-primary-600 text-white px-3 py-1 rounded hover:bg-primary-700 flex items-center gap-1"
                  >
                    <Check className="h-3 w-3" /> Sauvegarder
                  </button>
                  <button
                    onClick={handleCancelEdit}
                    className="text-xs bg-gray-100 text-gray-600 px-3 py-1 rounded hover:bg-gray-200 flex items-center gap-1"
                  >
                    <X className="h-3 w-3" /> Annuler
                  </button>
                </div>
              </div>
            ) : (
              // ===============================================================
              // Mode affichage
              // ===============================================================
              <>
                {/* Titre (barré si terminé) */}
                <p className={`text-sm font-medium ${
                  task.status === 'done'
                    ? 'line-through text-gray-400'
                    : 'text-gray-900'
                }`}>
                  {task.title}
                </p>
                {/* Description */}
                {task.description && (
                  <p className="text-xs text-gray-500 mt-0.5">{task.description}</p>
                )}
                {/* Métadonnées : priorité, statut, date */}
                <div className="flex flex-wrap items-center gap-2 mt-2">
                  {/* Badge de priorité */}
                  <span className={`text-xs px-2 py-0.5 rounded-full font-medium ${
                    PRIORITY_STYLES[task.priority]
                  }`}>
                    {PRIORITY_LABELS[task.priority]}
                  </span>

                  {/* Sélecteur de statut */}
                  <select
                    value={task.status}
                    onChange={handleStatusChange}
                    className="text-xs border border-gray-200 rounded px-1.5 py-0.5 focus:outline-none focus:ring-1 focus:ring-primary-500"
                  >
                    <option value="todo">À faire</option>
                    <option value="in_progress">En cours</option>
                    <option value="done">Terminée</option>
                  </select>

                  {/* Date d'échéance */}
                  {task.dueDate && (
                    <span className={`text-xs flex items-center gap-1 ${
                      isOverdue ? 'text-red-500 font-medium' : 'text-gray-400'
                    }`}>
                      {isOverdue && <AlertTriangle className="h-3 w-3" />}
                      <Calendar className="h-3 w-3" />
                      {new Date(task.dueDate).toLocaleDateString('fr-FR')}
                    </span>
                  )}
                </div>
              </>
            )}
          </div>

          {/* --- Boutons d'action (visibles uniquement en mode affichage) --- */}
          {!isEditing && (
            <div className="flex items-center gap-1 flex-shrink-0">
              {/* Bouton modifier */}
              <button
                onClick={() => setIsEditing(true)}
                className="p-1.5 text-gray-400 hover:text-primary-600 hover:bg-primary-50 rounded-lg transition-colors"
                title="Modifier"
              >
                <Pencil className="h-4 w-4" />
              </button>
              {/* Bouton supprimer */}
              <button
                onClick={() => setShowConfirm(true)}
                className="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors"
                title="Supprimer"
              >
                <Trash2 className="h-4 w-4" />
              </button>
            </div>
          )}
        </div>
      </div>

      {/* Modale de confirmation de suppression */}
      {showConfirm && (
        <ConfirmDialog
          title="Supprimer la tâche"
          message={`Voulez-vous vraiment supprimer "${task.title}" ? Cette action est irréversible.`}
          onConfirm={handleDelete}
          onCancel={() => setShowConfirm(false)}
        />
      )}
    </>
  );
}
