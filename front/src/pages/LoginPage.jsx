// =============================================================================
// Page de connexion - Formulaire avec validation Zod + React Hook Form
// =============================================================================
// Cette page affiche un formulaire de connexion avec :
//   - Validation côté client (email valide, mot de passe requis)
//   - Gestion des erreurs API (identifiants invalides)
//   - Redirection vers le dashboard après connexion réussie
//   - Lien vers la page d'inscription
// =============================================================================

import { useForm } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import { z } from 'zod';
import { useNavigate, Link } from 'react-router-dom';
import { useAuthStore } from '../store/authStore';
import { CheckSquare, Loader2 } from 'lucide-react';
import { toast } from 'sonner';

// Schéma de validation Zod pour le formulaire de connexion
const loginSchema = z.object({
  email: z
    .string()
    .min(1, "L'email est requis")
    .email("Format d'email invalide"),
  password: z
    .string()
    .min(1, 'Le mot de passe est requis'),
});

export default function LoginPage() {
  const navigate = useNavigate();
  const { login, isLoading, error } = useAuthStore();

  // Initialiser React Hook Form avec le resolver Zod
  const {
    register,      // Fonction pour "enregistrer" un champ dans le formulaire
    handleSubmit,  // Fonction qui valide les données avant de les soumettre
    formState: { errors },  // Erreurs de validation
  } = useForm({
    resolver: zodResolver(loginSchema),
  });

  /**
   * Traitement de la soumission du formulaire
   * Appelé uniquement si la validation Zod passe
   */
  const onSubmit = async (data) => {
    const success = await login(data.email, data.password);

    if (success) {
      toast.success('Connexion réussie !');
      navigate('/');  // Rediriger vers le dashboard
    } else {
      toast.error('Identifiants invalides');
    }
  };

  return (
    <div className="min-h-screen flex items-center justify-center bg-gray-50 px-4">
      <div className="w-full max-w-md">
        {/* --- En-tête avec logo --- */}
        <div className="text-center mb-8">
          <div className="flex justify-center mb-4">
            <CheckSquare className="h-12 w-12 text-primary-600" />
          </div>
          <h2 className="text-3xl font-bold text-gray-900">Yapuka</h2>
          <p className="mt-2 text-gray-500">Connectez-vous à votre compte</p>
        </div>

        {/* --- Formulaire de connexion --- */}
        <div className="bg-white rounded-xl shadow-sm border border-gray-200 p-8">
          <form onSubmit={handleSubmit(onSubmit)} className="space-y-5">
            {/* Champ Email */}
            <div>
              <label htmlFor="email" className="block text-sm font-medium text-gray-700 mb-1">
                Email
              </label>
              <input
                id="email"
                type="email"
                placeholder="demo@yapuka.dev"
                className={`w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 ${
                  errors.email ? 'border-red-400' : 'border-gray-300'
                }`}
                // register('email') connecte ce champ au formulaire React Hook Form
                {...register('email')}
              />
              {/* Message d'erreur de validation */}
              {errors.email && (
                <p className="mt-1 text-sm text-red-500">{errors.email.message}</p>
              )}
            </div>

            {/* Champ Mot de passe */}
            <div>
              <label htmlFor="password" className="block text-sm font-medium text-gray-700 mb-1">
                Mot de passe
              </label>
              <input
                id="password"
                type="password"
                placeholder="••••••••"
                className={`w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 ${
                  errors.password ? 'border-red-400' : 'border-gray-300'
                }`}
                {...register('password')}
              />
              {errors.password && (
                <p className="mt-1 text-sm text-red-500">{errors.password.message}</p>
              )}
            </div>

            {/* Erreur de l'API (identifiants invalides, etc.) */}
            {error && (
              <div className="p-3 bg-red-50 border border-red-200 rounded-lg">
                <p className="text-sm text-red-600">{error}</p>
              </div>
            )}

            {/* Bouton de soumission */}
            <button
              type="submit"
              disabled={isLoading}
              className="w-full bg-primary-600 text-white py-2.5 rounded-lg font-medium hover:bg-primary-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2"
            >
              {isLoading ? (
                <>
                  <Loader2 className="h-4 w-4 animate-spin" />
                  Connexion...
                </>
              ) : (
                'Se connecter'
              )}
            </button>
          </form>

          {/* Lien vers l'inscription */}
          <p className="mt-6 text-center text-sm text-gray-500">
            Pas encore de compte ?{' '}
            <Link to="/register" className="text-primary-600 hover:underline font-medium">
              Créer un compte
            </Link>
          </p>
        </div>
      </div>
    </div>
  );
}
