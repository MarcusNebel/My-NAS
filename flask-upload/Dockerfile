FROM python:3.11-slim

# Arbeitsverzeichnis
WORKDIR /app

# Abhängigkeiten
COPY requirements.txt .
RUN pip install --no-cache-dir -r requirements.txt

# App-Dateien
COPY app.py .

# Upload-Verzeichnis erstellen
RUN mkdir -p /home/nas-website-files/user_files && chmod -R 777 /home/nas-website-files/user_files

# Zip Verzeichnis
RUN mkdir -p /home/nas-website-files/tmp_zips && chmod -R 777 /home/nas-website-files/tmp_zips

# OpenSSL installieren
RUN apt-get update && apt-get install -y openssl

# SSL-Zertifikate generieren
RUN mkdir -p /certs && \
    openssl req -x509 -newkey rsa:2048 -nodes \
    -keyout /certs/selfsigned.key \
    -out /certs/selfsigned.crt \
    -days 365 \
    -subj "/C=DE/ST=NRW/L=Localhost/O=NAS-API/OU=Dev/CN=localhost"

# Port freigeben
EXPOSE 5001

# Starte die App über HTTPS
CMD ["python", "app.py"]
