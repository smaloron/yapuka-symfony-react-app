// =============================================================================
// Composant ConfirmDialog - Modale de confirmation
// =============================================================================
// Affiche une modale avec un message et deux boutons (Confirmer / Annuler).
// Utilisé principalement pour confirmer la suppression d'une tâche.
//
// Le fond semi-transparent (overlay) ferme la modale si on clique dessus.
// =============================================================================

import { AlertTriangle } from 'lucide-react';

export default function ConfirmDialog({ title, message, onConfirm, onCancel }) {
  return (
    // Overlay : fond semi-transparent couvrant tout l'écran
    <div
      className="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4"
      onClick={onCancel} // Fermer en cliquant sur le fond
    >
      {/* Contenu de la modale (arrête la propagation du clic) */}
      <div
        className="bg-white rounded-xl shadow-xl max-w-sm w-full p-6"
        onClick={(e) => e.stopPropagation()}
      >
        {/* Icône d'avertissement */}
        <div className="flex items-center gap-3 mb-4">
          <div className="bg-red-100 p-2 rounded-lg">
            <AlertTriangle className="h-5 w-5 text-red-600" />
          </div>
          <h3 className="text-lg font-semibold text-gray-900">{title}</h3>
        </div>

        {/* Message de confirmation */}
        <p className="text-sm text-gray-600 mb-6">{message}</p>

        {/* Boutons d'action */}
        <div className="flex justify-end gap-3">
          <button
            onClick={onCancel}
            className="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors"
          >
            Annuler
          </button>
          <button
            onClick={onConfirm}
            className="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 transition-colors"
          >
            Supprimer
          </button>
        </div>
      </div>
    </div>
  );
}
