/** =============================================================================
 * Configuration Tailwind CSS
 * =============================================================================
 * Tailwind est un framework CSS utilitaire.
 * Il scanne les fichiers listés dans "content" pour ne générer
 * que les classes CSS réellement utilisées (tree-shaking).
 * ============================================================================= */

/** @type {import('tailwindcss').Config} */
export default {
  // Fichiers à scanner pour trouver les classes Tailwind utilisées
  content: [
    './index.html',
    './src/**/*.{js,jsx}',
  ],
  theme: {
    extend: {
      // Couleurs personnalisées du thème Yapuka
      colors: {
        primary: {
          50: '#eff6ff',
          100: '#dbeafe',
          200: '#bfdbfe',
          300: '#93c5fd',
          400: '#60a5fa',
          500: '#3b82f6',
          600: '#2563eb',
          700: '#1d4ed8',
          800: '#1e40af',
          900: '#1e3a8a',
        },
      },
    },
  },
  plugins: [],
};
