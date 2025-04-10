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
    sub_path = request.form.get('path', '')  # Unterordner (optional)

    if not username:
        return jsonify({'error': 'Kein Benutzername übermittelt'}), 400

    if 'file[]' not in request.files:
        return jsonify({'error': 'Keine Datei hochgeladen'}), 400

    files = request.files.getlist('file[]')
    if not files:
        return jsonify({'error': 'Keine Dateien ausgewählt'}), 400

    # Zielordner: /uploads/<Benutzername>/<Pfad>
    user_folder = os.path.join(app.config['UPLOAD_FOLDER'], username)
    full_upload_path = os.path.join(user_folder, sub_path.strip("/"))
    os.makedirs(full_upload_path, exist_ok=True)

    saved_files = []
    for file in files:
        if file.filename == '':
            continue
        try:
            file_path = os.path.join(full_upload_path, file.filename)
            file.save(file_path)
            saved_files.append(os.path.join(sub_path, file.filename))
        except Exception as e:
            return jsonify({'error': str(e)}), 500

    return jsonify({'message': 'Dateien erfolgreich hochgeladen', 'files': saved_files}), 200


BASE_DIR = "/uploads"  # Passe bei Bedarf an

@app.route('/zip_download', methods=['POST'])
def zip_download():
    data = request.json
    print("Request JSON:", data)

    username = data.get("username")
    files = data.get("files")
    path = data.get("path", "")  # Der Pfad aus dem Request

    if not username or not files:
        return jsonify({"error": "Username oder Dateien fehlen"}), 400

    user_dir = os.path.join(BASE_DIR, username)
    if not os.path.exists(user_dir):
        return jsonify({"error": "Benutzerverzeichnis nicht gefunden"}), 404

    current_time = datetime.now(ZoneInfo("Europe/Berlin")).strftime("%Y-%m-%d_%H-%M-%S")
    zip_name = f"My-NAS_{username}_{current_time}.zip"

    zip_buffer = io.BytesIO()

    try:
        with zipfile.ZipFile(zip_buffer, "w", zipfile.ZIP_DEFLATED) as zipf:
            for rel_path in files:
                # Den Pfad aus der URL zum relativen Pfad hinzufügen
                full_rel_path = os.path.join(path, rel_path) if path else rel_path
                safe_rel_path = full_rel_path.strip("/")

                abs_path = os.path.join(user_dir, safe_rel_path)

                # Sicherheitscheck
                abs_user_dir = os.path.abspath(user_dir)
                abs_file_path = os.path.abspath(abs_path)
                if not abs_file_path.startswith(abs_user_dir):
                    return jsonify({"error": f"Ungültiger Pfad: {full_rel_path}"}), 403

                if os.path.isfile(abs_file_path):
                    zipf.write(abs_file_path, arcname=safe_rel_path)
                else:
                    print(f"Datei nicht gefunden: {abs_path}")
                    return jsonify({"error": f"Datei {full_rel_path} nicht gefunden"}), 404
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
