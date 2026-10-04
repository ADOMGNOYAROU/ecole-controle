#!/bin/sh
# Démarrage du conteneur sur Render : configuration, migrations, planificateur, puis Apache.
set -e

# Render fournit le port d'écoute dans $PORT (10000 par défaut).
PORT="${PORT:-10000}"
sed -ri "s/^Listen .*/Listen ${PORT}/" /etc/apache2/ports.conf
sed -ri "s/<VirtualHost \*:[0-9]+>/<VirtualHost *:${PORT}>/" /etc/apache2/sites-available/000-default.conf

# Render génère une clé aléatoire en base64 ; Laravel attend le préfixe « base64: ».
if [ -n "$APP_KEY" ] && [ "${APP_KEY#base64:}" = "$APP_KEY" ]; then
    export APP_KEY="base64:${APP_KEY}"
fi

# Sans APP_URL renseigné, on prend l'adresse publique fournie par Render.
if [ -z "$APP_URL" ] && [ -n "$RENDER_EXTERNAL_URL" ]; then
    export APP_URL="$RENDER_EXTERNAL_URL"
fi

# Variables de base collées depuis un navigateur : on retire espaces, retours à la ligne
# et « _ » parasites en fin de valeur (un « sslmode=require_ » empêche la connexion).
if [ -n "$DB_URL" ]; then
    DB_URL_PROPRE=$(printf '%s' "$DB_URL" | tr -d '[:space:]' | sed 's/_*$//')
    if [ "$DB_URL_PROPRE" != "$DB_URL" ]; then
        echo "DB_URL : caractères parasites retirés en fin de valeur."
    fi
    export DB_URL="$DB_URL_PROPRE"
    # Seule la partie après le dernier « / » est affichée : jamais le mot de passe.
    echo "DB_URL se termine par : ${DB_URL##*/}"
fi
for VAR in DB_HOST DB_PORT DB_DATABASE DB_USERNAME DB_SSLMODE; do
    VALEUR=$(eval "printf '%s' \"\${$VAR:-}\"" | tr -d '[:space:]')
    if [ -n "$VALEUR" ]; then
        export "$VAR=$VALEUR"
    fi
done

php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan storage:link >/dev/null 2>&1 || true

if [ "${RUN_MIGRATIONS:-true}" = "true" ]; then
    php artisan migrate --force
fi

# Les commandes ci-dessus tournent en root : Apache (www-data) doit pouvoir écrire ensuite.
chown -R www-data:www-data storage bootstrap/cache

# Tâches planifiées (rappels de paiement chaque jour à 08:00) dans le même conteneur :
# les cron jobs Render ne sont pas disponibles sur l'offre gratuite.
su -s /bin/sh www-data -c "php artisan schedule:work" &

exec apache2-foreground
