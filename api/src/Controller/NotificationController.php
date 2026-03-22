<?php

// =============================================================================
// Contrôleur de notifications - Server-Sent Events (SSE)
// =============================================================================
// SSE permet au serveur d'envoyer des événements en temps réel au frontend
// via une connexion HTTP persistante (unidirectionnelle : serveur → client).
//
// Le frontend se connecte à GET /api/notifications/stream et reçoit
// les notifications au format SSE (text/event-stream).
//
// Ce contrôleur utilise Redis comme pub/sub pour distribuer les notifications.
// =============================================================================

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/notifications')]
class NotificationController extends AbstractController
{
    // =========================================================================
    // GET /api/notifications/stream - Flux SSE de notifications
    // =========================================================================
    // Le client ouvre une connexion persistante.
    // Le serveur envoie un "heartbeat" toutes les 15 secondes pour maintenir
    // la connexion active, et des notifications quand il y en a.
    //
    // Format SSE :
    //   event: notification
    //   data: {"type": "task_created", "message": "Nouvelle tâche créée"}
    //
    // Note : en production, on utiliserait Redis pub/sub ou Mercure.
    // Ici, on envoie un heartbeat simple pour illustrer le mécanisme SSE.
    // =========================================================================
    #[Route('/stream', name: 'api_notifications_stream', methods: ['GET'])]
    public function stream(string $view, array $parameters = [], ?StreamedResponse $response = null): StreamedResponse
    {
        $user = $this->getUser();

        $response = new StreamedResponse(function () use ($user) {
            // Désactiver le buffering PHP pour envoyer les données immédiatement
            if (ob_get_level()) {
                ob_end_clean();
            }

            // Envoyer un événement de connexion réussie
            echo "event: connected\n";
            echo 'data: '.json_encode([
                'type' => 'connected',
                'message' => 'Connexion SSE établie.',
                'userId' => $user->getId(),
            ])."\n\n";
            flush();

            // Boucle principale : maintenir la connexion ouverte
            $iteration = 0;
            while (true) {
                // Vérifier si la connexion client est toujours active
                if (connection_aborted()) {
                    break;
                }

                // Envoyer un heartbeat toutes les 15 secondes
                // Le heartbeat empêche le navigateur de fermer la connexion
                echo ": heartbeat\n\n";
                flush();

                // Simuler une notification périodique (toutes les 30 secondes)
                // En production, on écouterait Redis pub/sub ici
                if ($iteration > 0 && 0 === $iteration % 2) {
                    echo "event: notification\n";
                    echo 'data: '.json_encode([
                        'type' => 'reminder',
                        'message' => 'N\'oubliez pas de vérifier vos tâches en cours !',
                        'timestamp' => (new \DateTimeImmutable())->format('c'),
                    ])."\n\n";
                    flush();
                }

                ++$iteration;
                // Attendre 15 secondes avant le prochain heartbeat
                sleep(15);
            }
        });

        // En-têtes HTTP requis pour SSE
        $response->headers->set('Content-Type', 'text/event-stream');
        $response->headers->set('Cache-Control', 'no-cache');
        $response->headers->set('Connection', 'keep-alive');
        // Désactiver le buffering de Nginx (important en production)
        $response->headers->set('X-Accel-Buffering', 'no');

        return $response;
    }
}
