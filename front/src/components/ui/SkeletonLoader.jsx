// =============================================================================
// Composant SkeletonLoader - Écrans de chargement animés
// =============================================================================
// Les "Skeleton Screens" remplacent un spinner classique par des zones grises
// animées qui imitent la forme du contenu final.
//
// Avantage UX : l'utilisateur perçoit le chargement comme plus rapide
// car il voit déjà la structure de la page.
//
// La classe CSS "skeleton" est définie dans index.css.
// =============================================================================

/**
 * @param {{ count: number }} props - Nombre de lignes skeleton à afficher
 */
export default function SkeletonLoader({ count = 3 }) {
  return (
    <div className="space-y-3">
      {/* Générer "count" lignes skeleton */}
      {[...Array(count)].map((_, index) => (
        <div
          key={index}
          className="bg-white rounded-xl border border-gray-200 p-4"
        >
          <div className="flex items-start gap-3">
            {/* Placeholder pour la checkbox */}
            <div className="skeleton h-5 w-5 rounded flex-shrink-0" />

            <div className="flex-1 space-y-2">
              {/* Placeholder pour le titre (largeur variable pour plus de réalisme) */}
              <div
                className="skeleton h-4"
                style={{ width: `${60 + Math.random() * 30}%` }}
              />
              {/* Placeholder pour la description */}
              <div
                className="skeleton h-3"
                style={{ width: `${40 + Math.random() * 40}%` }}
              />
              {/* Placeholder pour les badges */}
              <div className="flex gap-2 mt-2">
                <div className="skeleton h-5 w-16 rounded-full" />
                <div className="skeleton h-5 w-20 rounded" />
              </div>
            </div>
          </div>
        </div>
      ))}
    </div>
  );
}
