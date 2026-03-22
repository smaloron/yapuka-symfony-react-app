// =============================================================================
// Composant StatsPanel - Affichage des statistiques des tâches
// =============================================================================
// Affiche :
//   - 4 widgets compteurs (Total, À faire, En cours, Terminées, En retard)
//   - Un graphique en barres avec Recharts (répartition par statut)
//
// Les données proviennent du store Zustand (endpoint GET /api/tasks/stats).
// =============================================================================

import { useTaskStore } from '../../store/taskStore';
import {
  BarChart, Bar, XAxis, YAxis, CartesianGrid, Tooltip, ResponsiveContainer,
} from 'recharts';
import {
  ListTodo, Clock, CheckCircle2, AlertTriangle,
} from 'lucide-react';

export default function StatsPanel() {
  const { stats, isStatsLoading } = useTaskStore();

  // Pendant le chargement, afficher des placeholders
  if (isStatsLoading || !stats) {
    return (
      <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
        {[...Array(4)].map((_, i) => (
          <div key={i} className="skeleton h-24 rounded-xl" />
        ))}
      </div>
    );
  }

  // Configuration des widgets compteurs
  const counters = [
    {
      label: 'Total',
      value: stats.total,
      icon: ListTodo,
      color: 'text-primary-600',
      bg: 'bg-primary-50',
    },
    {
      label: 'À faire',
      value: stats.todo,
      icon: Clock,
      color: 'text-yellow-600',
      bg: 'bg-yellow-50',
    },
    {
      label: 'Terminées',
      value: stats.done,
      icon: CheckCircle2,
      color: 'text-green-600',
      bg: 'bg-green-50',
    },
    {
      label: 'En retard',
      value: stats.overdue,
      icon: AlertTriangle,
      color: 'text-red-600',
      bg: 'bg-red-50',
    },
  ];

  // Données formatées pour le graphique Recharts
  const chartData = [
    { name: 'À faire', count: stats.todo, fill: '#eab308' },
    { name: 'En cours', count: stats.in_progress, fill: '#3b82f6' },
    { name: 'Terminées', count: stats.done, fill: '#22c55e' },
  ];

  return (
    <div className="space-y-6">
      {/* ================================================================= */}
      {/* Widgets compteurs */}
      {/* ================================================================= */}
      <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
        {counters.map((counter) => {
          const Icon = counter.icon;
          return (
            <div
              key={counter.label}
              className="bg-white rounded-xl border border-gray-200 p-4 flex items-center gap-4"
            >
              {/* Icône avec fond coloré */}
              <div className={`${counter.bg} p-2.5 rounded-lg`}>
                <Icon className={`h-5 w-5 ${counter.color}`} />
              </div>
              {/* Valeur et label */}
              <div>
                <p className="text-2xl font-bold text-gray-900">{counter.value}</p>
                <p className="text-xs text-gray-500">{counter.label}</p>
              </div>
            </div>
          );
        })}
      </div>

      {/* ================================================================= */}
      {/* Graphique en barres (Recharts) */}
      {/* ================================================================= */}
      {stats.total > 0 && (
        <div className="bg-white rounded-xl border border-gray-200 p-6">
          <h3 className="text-sm font-medium text-gray-700 mb-4">
            Répartition par statut
          </h3>
          {/* ResponsiveContainer adapte le graphique à la taille du conteneur */}
          <ResponsiveContainer width="100%" height={200}>
            <BarChart data={chartData}>
              <CartesianGrid strokeDasharray="3 3" stroke="#f3f4f6" />
              <XAxis dataKey="name" tick={{ fontSize: 12 }} />
              <YAxis allowDecimals={false} tick={{ fontSize: 12 }} />
              <Tooltip />
              <Bar dataKey="count" radius={[6, 6, 0, 0]} />
            </BarChart>
          </ResponsiveContainer>
        </div>
      )}
    </div>
  );
}
