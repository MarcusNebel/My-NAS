from flask import Flask, send_file, request, jsonify, make_response
from flask_cors import CORS  # Importiere CORS
import os
import zipfile
import io
from datetime import datetime
from zoneinfo import ZoneInfo

app = Flask(__name__)
CORS(app)  # CORS nur einmal aktivieren

# Definiere den Zielordner für Uploads
UPLOAD_FOLDER = '/uploads'  # Der Pfad zum Ordner im Docker-Container
app.config['UPLOAD_FOLDER'] = UPLOAD_FOLDER

# Stelle sicher, dass der Upload-Ordner existiert
if not os.path.exists(UPLOAD_FOLDER):
    os.makedirs(UPLOAD_FOLDER)

# Setze ein maximales Dateigrößenlimit (optional)
app.config['MAX_CONTENT_LENGTH'] = 100 * 1024 * 1024 * 1024  # 100 GB

@app.route('/upload', methods=['POST'])
def upload_file():
    username = request.form.get('username')
    if not username:
        return jsonify({'error': 'Kein Benutzername übermittelt'}), 400

    if 'file[]' not in request.files:
        return jsonify({'error': 'Keine Datei hochgeladen'}), 400

    files = request.files.getlist('file[]')
    if not files:
        return jsonify({'error': 'Keine Dateien ausgewählt'}), 400

    # Ordner für Benutzer erstellen (falls nicht vorhanden)
    user_folder = os.path.join(app.config['UPLOAD_FOLDER'], username)
    os.makedirs(user_folder, exist_ok=True)

    # Dateien speichern
    saved_files = []
    for file in files:
        if file.filename == '':
            continue
        try:
            file_path = os.path.join(user_folder, file.filename)
            file.save(file_path)
            saved_files.append(file.filename)
        except Exception as e:
            return jsonify({'error': str(e)}), 500

    return jsonify({'message': 'Dateien erfolgreich hochgeladen', 'files': saved_files}), 200


BASE_DIR = "/uploads"  # Passe bei Bedarf an

@app.route('/zip_download', methods=['POST'])
def zip_download():
    data = request.json  # Hier wird der JSON-Body des Requests geparsed
    print("Request JSON:", data)  # Ausgabe der gesamten JSON-Daten
    username = data.get("username")
    files = data.get("files")

    if not username or not files:
        return jsonify({"error": "Username oder Dateien fehlen"}), 400

    user_dir = os.path.join(BASE_DIR, username)
    if not os.path.exists(user_dir):
        return jsonify({"error": "Benutzerverzeichnis nicht gefunden"}), 404

    # Datum und Uhrzeit für den Zip-Namen formatieren
    current_time = datetime.now(ZoneInfo("Europe/Berlin")).strftime("%Y-%m-%d_%H-%M-%S")
    zip_name = f"My-NAS_{username}_{current_time}.zip"

    zip_buffer = io.BytesIO()

    try:
        with zipfile.ZipFile(zip_buffer, "w", zipfile.ZIP_DEFLATED) as zipf:
            for rel_path in files:
                abs_path = os.path.join(user_dir, rel_path)
                if os.path.isfile(abs_path):
                    zipf.write(abs_path, arcname=rel_path)
                else:
                    print(f"Datei nicht gefunden: {abs_path}")
                    return jsonify({"error": f"Datei {rel_path} nicht gefunden"}), 404
    except Exception as e:
        return jsonify({"error": f"Fehler beim Erstellen der ZIP-Datei: {str(e)}"}), 500


    zip_buffer.seek(0)

    response = make_response(send_file(
        zip_buffer,
        mimetype='application/zip',
        as_attachment=True,
        download_name=zip_name
    ))
    response.headers['Content-Disposition'] = f'attachment; filename="{zip_name}"'
    response.headers['Access-Control-Expose-Headers'] = 'Content-Disposition'
    return response

if __name__ == '__main__':
    app.run(
        debug=True,
        host='0.0.0.0',
        port=5001,
        ssl_context=('/certs/selfsigned.crt', '/certs/selfsigned.key')
    )
