#!/bin/bash

# Zielverzeichnis für die php.ini
PHP_INI_DIR="/usr/local/etc/php/conf.d/"

# Datei-Pfad für die neue php.ini
PHP_INI_FILE="${PHP_INI_DIR}php.ini"

# Erstelle die php.ini mit den gewünschten Einstellungen
echo -e "upload_max_filesize = 0\npost_max_size = 0\nmemory_limit = -1\nmax_execution_time = 0" > "$PHP_INI_FILE"

# Bestätige die Erstellung der Datei
echo "Die php.ini wurde erfolgreich erstellt unter $PHP_INI_FILE"
