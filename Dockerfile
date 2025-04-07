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
    openssl \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd mysqli pdo pdo_mysql zip

# Aktiviere Apache-Module
RUN a2enmod rewrite ssl

# SSL-Zertifikatsordner anlegen
RUN mkdir -p /etc/apache2/ssl

# Self-Signed Zertifikat erstellen
RUN openssl req -x509 -newkey rsa:2048 -nodes \
    -keyout /etc/apache2/ssl/selfsigned.key \
    -out /etc/apache2/ssl/selfsigned.crt \
    -days 365 \
    -subj "/C=DE/ST=NRW/L=Localhost/O=NAS-Website/OU=Dev/CN=localhost"

# Passe die default-ssl.conf an
RUN sed -i 's|SSLCertificateFile.*|SSLCertificateFile /etc/apache2/ssl/selfsigned.crt|' /etc/apache2/sites-available/default-ssl.conf && \
    sed -i 's|SSLCertificateKeyFile.*|SSLCertificateKeyFile /etc/apache2/ssl/selfsigned.key|' /etc/apache2/sites-available/default-ssl.conf

# Aktiviere die HTTPS-Site
RUN a2ensite default-ssl

# Verzeichnisse für Uploads & temporäre Zips
RUN mkdir -p /home/nas-website-files/user_files && \
    mkdir -p /home/nas-website-files/tmp_zips/ && \
    chown -R www-data:www-data /home/nas-website-files/ && \
    chmod -R 775 /home/nas-website-files/

# Setzt das Arbeitsverzeichnis
WORKDIR /var/www/html

# Kopiert den Website-Code ins Container-Verzeichnis
COPY ./app/ /var/www/html/nas-website/

# Setzt die korrekten Dateiberechtigungen
RUN chown -R www-data:www-data /var/www/html/nas-website

# Exponiert sowohl HTTP als auch HTTPS
EXPOSE 80
EXPOSE 443

# Starte Apache im Vordergrund
CMD ["apache2-foreground"]
