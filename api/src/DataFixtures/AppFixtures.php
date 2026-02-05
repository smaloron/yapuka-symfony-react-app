<?php

// =============================================================================
// Fixtures - Données de démonstration
// =============================================================================
// Les fixtures chargent des données initiales en base pour le développement.
//
// Exécution : php bin/console doctrine:fixtures:load
//
// Données créées :
//   - 1 utilisateur de démo (demo@yapuka.dev / password)
//   - 2 utilisateurs supplémentaires
//   - 15 tâches réparties entre les utilisateurs
// =============================================================================

namespace App\DataFixtures;

use App\Factory\TaskFactory;
use App\Factory\UserFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // =====================================================================
        // 1. Créer l'utilisateur de démo (identifiants connus pour les tests)
        // =====================================================================
        $demoUser = UserFactory::createOne([
            'email' => 'demo@yapuka.dev',
            'username' => 'Démo User',
            'password' => 'password',
        ]);

        // =====================================================================
        // 2. Créer des utilisateurs supplémentaires
        // =====================================================================
        $otherUsers = UserFactory::createMany(2);

        // =====================================================================
        // 3. Créer des tâches pour l'utilisateur de démo
        // =====================================================================
        // 10 tâches avec des statuts et priorités variés
        TaskFactory::createMany(10, [
            'owner' => $demoUser,
        ]);

        // =====================================================================
        // 4. Créer des tâches pour les autres utilisateurs
        // =====================================================================
        foreach ($otherUsers as $user) {
            TaskFactory::createMany(3, [
                'owner' => $user,
            ]);
        }
    }
}
