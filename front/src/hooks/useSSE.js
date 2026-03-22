// =============================================================================
// Hook useSSE - Écoute des notifications en temps réel (Server-Sent Events)
// =============================================================================
// Ce hook React ouvre une connexion SSE vers le backend Symfony
// et écoute les événements envoyés par le serveur.
//
// SSE vs WebSocket :
//   - SSE est unidirectionnel (serveur → client uniquement)
//   - SSE se reconnecte automatiquement en cas de déconnexion
//   - SSE fonctionne sur HTTP standard (pas besoin de protocole spécial)
//   - WebSocket est bidirectionnel mais plus complexe à mettre en place
//
// Utilisation :
//   const { isConnected } = useSSE();
// =============================================================================

import { useEffect, useRef, useState } from 'react';
import { toast } from 'sonner';
import { useTaskStore } from '../store/taskStore';

// URL de base de l'API
const API_BASE_URL = import.meta.env.VITE_API_URL || '';

/**
 * Hook personnalisé pour les notifications SSE.
 *
 * @returns {{ isConnected: boolean }} - État de la connexion SSE
 */
export function useSSE() {
  // État de connexion (affiché comme indicateur vert/rouge dans l'UI)
  const [isConnected, setIsConnected] = useState(false);

  // Ref pour stocker l'instance EventSource (persiste entre les re-renders)
  const eventSourceRef = useRef(null);

  // Accès au store pour rafraîchir les données quand une notification arrive
  const loadTasks = useTaskStore((state) => state.loadTasks);
  const loadStats = useTaskStore((state) => state.loadStats);

  useEffect(() => {
    // Récupérer le token JWT pour l'authentification
    const token = localStorage.getItem('yapuka_token');

    // Ne pas ouvrir de connexion SSE sans token valide
    if (!token) {
      return;
    }

    // Construire l'URL du flux SSE avec le token en query parameter
    // (EventSource ne supporte pas les headers Authorization)
    const sseUrl = `${API_BASE_URL}/api/notifications/stream?token=${token}`;

    // Créer la connexion SSE
    const eventSource = new EventSource(sseUrl);
    eventSourceRef.current = eventSource;

    // -----------------------------------------------------------------
    // Événement : connexion établie
    // -----------------------------------------------------------------
    eventSource.addEventListener('connected', (event) => {
      setIsConnected(true);
      console.log('[SSE] Connexion établie', JSON.parse(event.data));
    });

    // -----------------------------------------------------------------
    // Événement : notification reçue
    // -----------------------------------------------------------------
    eventSource.addEventListener('notification', (event) => {
      const data = JSON.parse(event.data);

      // Afficher un toast avec le message de la notification
      toast.info(data.message, {
        description: data.type,
        duration: 5000,
      });

      // Rafraîchir les données en arrière-plan
      loadTasks();
      loadStats();
    });

    // -----------------------------------------------------------------
    // Événement : connexion ouverte (générique)
    // -----------------------------------------------------------------
    eventSource.onopen = () => {
      setIsConnected(true);
    };

    // -----------------------------------------------------------------
    // Événement : erreur de connexion
    // -----------------------------------------------------------------
    eventSource.onerror = () => {
      setIsConnected(false);
      console.warn('[SSE] Connexion perdue. Tentative de reconnexion...');
      // EventSource se reconnecte automatiquement après quelques secondes
    };

    // -----------------------------------------------------------------
    // Nettoyage : fermer la connexion quand le composant est démonté
    // -----------------------------------------------------------------
    return () => {
      eventSource.close();
      eventSourceRef.current = null;
      setIsConnected(false);
    };
  }, []); // Le tableau vide [] signifie : exécuter une seule fois au montage

  return { isConnected };
}
