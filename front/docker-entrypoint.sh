#!/bin/sh

# arrêt dès la première erreur
set -e

cat <<EOF > /usr/share/nginx/html/config.js
window.__CONFIG__ = {
  API_URL: "${API_URL:-}"
};
EOF

echo "Configuration générée :"
cat /usr/share/nginx/html/config.js

exec nginx -g 'daemon off;'