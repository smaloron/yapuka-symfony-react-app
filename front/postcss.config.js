// =============================================================================
// Configuration PostCSS
// =============================================================================
// PostCSS transforme le CSS avec des plugins.
// Tailwind CSS et Autoprefixer sont les deux plugins essentiels.
// =============================================================================

export default {
  plugins: {
    // Tailwind CSS : génère les classes utilitaires
    tailwindcss: {},
    // Autoprefixer : ajoute les préfixes vendeur (-webkit, -moz, etc.)
    autoprefixer: {},
  },
};
