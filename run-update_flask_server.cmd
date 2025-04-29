@echo off
:: Hole den Pfad der aktuellen Datei
setlocal
set SCRIPT_DIR=%~dp0

:: Zum Verzeichnis der aktuellen Datei navigieren
cd /d "%SCRIPT_DIR%"

:: Zeige den aktuellen Pfad an
echo The current path is: %cd%

:: Update Server starten
echo Update server is running.
echo You can close this Window.
pythonw update-server/update_flask_server.py
