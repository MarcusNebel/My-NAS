#!/bin/bash

# Name des Skripts, das beim Systemstart ausgeführt werden soll
SCRIPT_PATH="~/nas-website/run-update_flask_server.sh"

# Prüfen, ob das Skript existiert
if [ ! -f "$SCRIPT_PATH" ]; then
  echo "Error: The named script isn't $SCRIPT_PATH existing."
  exit 1
fi

# Crontab-Eintrag für das Skript
CRON_ENTRY="@reboot $SCRIPT_PATH"

# Aktuelle Crontab in einer temporären Datei speichern
TEMP_CRON=$(mktemp)
crontab -l > "$TEMP_CRON" 2>/dev/null

# Prüfen, ob der Eintrag bereits existiert
if grep -Fxq "$CRON_ENTRY" "$TEMP_CRON"; then
  echo "Crontab is already existing."
else
  # Neuen Eintrag hinzufügen
  echo "$CRON_ENTRY" >> "$TEMP_CRON"
  crontab "$TEMP_CRON"
  echo "Crontab was added successfully."
fi

# Temporäre Datei aufräumen
rm -f "$TEMP_CRON"

# Sicherstellen, dass das Skript ausführbar ist
chmod +x "$SCRIPT_PATH"

docker-compose up -f docker-compose.main.yml build --no-cache
docker-compose up -f docker-compose.main.yml up -d

echo "Docker Container is running."