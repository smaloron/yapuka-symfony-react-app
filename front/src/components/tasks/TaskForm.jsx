// =============================================================================
// Composant TaskForm - Formulaire de création rapide de tâche
// =============================================================================
// Un formulaire simple et compact en haut de la liste des tâches.
// Champs : titre (requis), description, priorité, date d'échéance.
//
// Utilise React Hook Form + Zod pour la validation.
// Après création réussie, le formulaire se réinitialise automatiquement.
// =============================================================================

import { useForm } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import { z } from 'zod';
import { useTaskStore } from '../../store/taskStore';
import { Plus, Loader2 } from 'lucide-react';
import { useState } from 'react';

// Schéma de validation du formulaire de tâche
const taskSchema = z.object({
  title: z.string().min(1, 'Le titre est requis').max(255),
  description: z.string().optional(),
  priority: z.enum(['low', 'medium', 'high']),
  dueDate: z.string().optional(), // Format ISO (YYYY-MM-DD)
});

export default function TaskForm() {
  const addTask = useTaskStore((state) => state.addTask);
  const [isSubmitting, setIsSubmitting] = useState(false);

  const {
    register,
    handleSubmit,
    reset,      // Fonction pour réinitialiser le formulaire
    formState: { errors },
  } = useForm({
    resolver: zodResolver(taskSchema),
    defaultValues: {
      priority: 'medium',
    },
  });

  /**
   * Soumission du formulaire
   */
  const onSubmit = async (data) => {
    setIsSubmitting(true);

    // Nettoyer les champs vides
    const taskData = {
      title: data.title,
      description: data.description || null,
      priority: data.priority,
      dueDate: data.dueDate || null,
    };

    const success = await addTask(taskData);

    if (success) {
      reset(); // Réinitialiser le formulaire après création
    }

    setIsSubmitting(false);
  };

  return (
    <div className="bg-white rounded-xl border border-gray-200 p-6">
      <form onSubmit={handleSubmit(onSubmit)} className="space-y-4">
        {/* --- Ligne principale : titre + bouton --- */}
        <div className="flex gap-3">
          <div className="flex-1">
            <input
              type="text"
              placeholder="Que devez-vous faire ?"
              className={`w-full px-3 py-2.5 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 ${
                errors.title ? 'border-red-400' : 'border-gray-300'
              }`}
              {...register('title')}
            />
            {errors.title && (
              <p className="mt-1 text-sm text-red-500">{errors.title.message}</p>
            )}
          </div>
          <button
            type="submit"
            disabled={isSubmitting}
            className="bg-primary-600 text-white px-5 py-2.5 rounded-lg font-medium hover:bg-primary-700 transition-colors disabled:opacity-50 flex items-center gap-2"
          >
            {isSubmitting ? (
              <Loader2 className="h-4 w-4 animate-spin" />
            ) : (
              <Plus className="h-4 w-4" />
            )}
            Ajouter
          </button>
        </div>

        {/* --- Ligne secondaire : options (description, priorité, date) --- */}
        <div className="flex flex-wrap gap-3">
          {/* Description */}
          <input
            type="text"
            placeholder="Description (optionnelle)"
            className="flex-1 min-w-[200px] px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary-500"
            {...register('description')}
          />

          {/* Sélecteur de priorité */}
          <select
            className="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary-500"
            {...register('priority')}
          >
            <option value="low">🟢 Basse</option>
            <option value="medium">🟡 Moyenne</option>
            <option value="high">🔴 Haute</option>
          </select>

          {/* Date d'échéance */}
          <input
            type="date"
            className="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary-500"
            {...register('dueDate')}
          />
        </div>
      </form>
    </div>
  );
}
