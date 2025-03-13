# Offizielles PHP-Apache-Image als Basis
FROM php:8.3-apache

# Installiere benötigte Pakete
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    unzip \
    mariadb-client \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd mysqli pdo pdo_mysql zip

# Erstellt das Verzeichniss für die hocgeldenen Dateien
RUN mkdir -p /home/nas-website-files/user_files
RUN chown -R www-data:www-data /home/nas-website-files/user_files/
RUN chmod -R 775 /home/nas-website-files/user_files/

# Aktiviert mod_rewrite für Apache (wichtig für .htaccess)
RUN a2enmod rewrite

# Setzt das Arbeitsverzeichnis für die Website
WORKDIR /var/www/html

# Kopiert den Website-Code ins Container-Verzeichnis
COPY ./app/ /var/www/html/nas-website/

# Setzt die korrekten Dateiberechtigungen
RUN chown -R www-data:www-data /var/www/html/nas-website

# Setze das Skript als ausführbar
RUN chmod +x /var/www/html/nas-website/adjust_upload_limit.sh

# Führe das Skript aus
RUN bash /var/www/html/nas-website/adjust_upload_limit.sh

# Exponiert Port 80 für den Webserver
EXPOSE 80

# Startet Apache beim Container-Start
CMD ["apache2-foreground"]
