#!/bin/bash

# Uscita immediata se un comando fallisce
set -e

# CONFIGURA QUI IL TUO UTENTE E PASSWORD
PMA_USER="utente_phpmyadmin"
PMA_PASS="Password1!"
BLOWFISH_SECRET="qwertyuiopasdfghjklzxcvbnmqwerty"
WORKOUT_USER="admin"
WORKOUT_PASS="admin123"

echo "🛠️  Aggiornamento pacchetti e installazione Apache, PHP, MariaDB..."
sudo apt update
sudo apt install -y apache2 php libapache2-mod-php php-mysql mariadb-server wget unzip

echo "🚀 Avvio di MariaDB..."
sudo service mariadb start

echo "🔒 Esecuzione configurazione sicura MariaDB (automatica con expect)..."
sudo mariadb <<EOF
DELETE FROM mysql.user WHERE User='';
DROP DATABASE IF EXISTS test;
DELETE FROM mysql.db WHERE Db='test' OR Db='test\\_%';
FLUSH PRIVILEGES;
EOF

echo "📦 Creazione database Home Workout..."
sudo mariadb < /workspaces/codespaces-blank/schema.sql

echo "📂 Setup directory Home Workout..."
sudo mkdir -p /var/www/html/home-workout/api
sudo cp /workspaces/codespaces-blank/*.php /var/www/html/home-workout/
sudo cp /workspaces/codespaces-blank/api/*.php /var/www/html/home-workout/api/ 2>/dev/null || true
sudo chown -R www-data:www-data /var/www/html/home-workout

echo "🌐 Configurazione Apache per Home Workout..."
cat <<EOCONF | sudo tee /etc/apache2/sites-available/home-workout.conf
<VirtualHost *:80>
    ServerName localhost
    DocumentRoot /var/www/html/home-workout
    
    <Directory /var/www/html/home-workout>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog \${APACHE_LOG_DIR}/home-workout-error.log
    CustomLog \${APACHE_LOG_DIR}/home-workout-access.log combined
</VirtualHost>
EOCONF

echo "📂 Installazione phpMyAdmin..."
cd /var/www/html
sudo wget https://www.phpmyadmin.net/downloads/phpMyAdmin-latest-all-languages.zip
sudo unzip phpMyAdmin-latest-all-languages.zip
sudo mv phpMyAdmin-*-all-languages phpmyadmin
sudo rm phpMyAdmin-latest-all-languages.zip

echo "⚙️  Configurazione phpMyAdmin..."
cd phpmyadmin
sudo cp config.sample.inc.php config.inc.php
sudo sed -i "s/\(\$cfg\['blowfish_secret'\] = \).*/\1'$BLOWFISH_SECRET';/" config.inc.php

echo "🌐 Configurazione Apache per phpMyAdmin..."
cat <<EOCONF | sudo tee /etc/apache2/conf-available/phpmyadmin.conf
Alias /phpmyadmin /var/www/html/phpmyadmin

<Directory /var/www/html/phpmyadmin>
    Options Indexes FollowSymLinks
    DirectoryIndex index.php
    AllowOverride All
    Require all granted
</Directory>
EOCONF

echo "👤 Creazione utente MariaDB per phpMyAdmin..."
sudo mariadb <<EOF
CREATE USER IF NOT EXISTS '$PMA_USER'@'localhost' IDENTIFIED BY '$PMA_PASS';
GRANT ALL PRIVILEGES ON *.* TO '$PMA_USER'@'localhost' WITH GRANT OPTION;
FLUSH PRIVILEGES;
EOF

echo "🔄 Attivazione site e moduli Apache..."
sudo a2ensite home-workout
sudo a2enconf phpmyadmin
sudo a2enmod rewrite
sudo service apache2 restart

echo ""
echo "✅ Installazione completata!"
echo ""
echo "🏋️  Home Workout:"
echo "    http://localhost"
echo "    Registrati o accedi per iniziare"
echo ""
echo "🔗 phpMyAdmin:"
echo "    http://localhost/phpmyadmin"
echo ""
echo "👤 Credenziali phpMyAdmin:"
echo "    Utente: $PMA_USER"
echo "    Password: $PMA_PASS"