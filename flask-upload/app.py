from flask import Flask, send_file, request, jsonify, make_response
from flask_cors import CORS
import os
import zipfile
import io
from datetime import datetime
from zoneinfo import ZoneInfo
import pymysql
from werkzeug.utils import secure_filename

app = Flask(__name__)
CORS(app)  # CORS aktivieren

# --- Konfiguration ---
UPLOAD_FOLDER = '/home/nas-website-files/user_files'  # Im Container
BASE_DIR = UPLOAD_FOLDER

DB_CONFIG = {
    'host': 'db',
    'user': 'root',
    'password': '59LWrt!mDo6GC4',
    'database': 'nas-website'
}

app.config['UPLOAD_FOLDER'] = UPLOAD_FOLDER
app.config['MAX_CONTENT_LENGTH'] = 100 * 1024 * 1024 * 1024  # 100 GB

# --- Hilfsfunktionen ---
def get_api_key():
    auth = request.headers.get('Authorization')
    if not auth:
        return None
    return auth.strip()

def get_username_by_api_key(api_key):
    connection = pymysql.connect(**DB_CONFIG)
    try:
        with connection.cursor() as cursor:
            cursor.execute("SELECT USERNAME FROM accounts WHERE api_key = %s", (api_key,))
            result = cursor.fetchone()
            return result[0] if result else None
    finally:
        connection.close()

# --- Upload Route ---
@app.route('/upload', methods=['POST'])
def upload_file():
    print("UPLOAD API HIT")
    print("Headers:", request.headers)
    print("Form:", request.form)
    print("Files:", request.files)

    api_key = get_api_key()

    # --- Upload per API-Key (Flutter App) ---
    if api_key:
        username = get_username_by_api_key(api_key)
        if not username:
            return jsonify({'error': 'Ungültiger API-Key'}), 401

        user_folder = os.path.join(app.config['UPLOAD_FOLDER'], username)
        relative_path = request.form.get('path', '').strip('/')
        full_upload_path = os.path.join(user_folder, relative_path)

        if 'file' not in request.files:
            return jsonify({'error': 'Keine Datei hochgeladen'}), 400

        file = request.files['file']
        filename = secure_filename(file.filename)

        os.makedirs(full_upload_path, exist_ok=True)
        file_path = os.path.join(full_upload_path, filename)

        # Sicherheits-Check
        abs_user_folder = os.path.abspath(user_folder)
        abs_target_path = os.path.abspath(file_path)
        if not abs_target_path.startswith(abs_user_folder):
            return jsonify({'error': 'Ungültiger Pfad'}), 400

        file.save(file_path)

        return jsonify({'message': 'Upload erfolgreich', 'filename': filename}), 200

    # --- Upload über Website (username + file[]) ---
    username = request.form.get('username')
    sub_path = request.form.get('path', '')

    if not username:
        return jsonify({'error': 'Kein Benutzername übermittelt'}), 400

    if 'file[]' not in request.files:
        return jsonify({'error': 'Keine Datei hochgeladen'}), 400

    files = request.files.getlist('file[]')
    if not files:
        return jsonify({'error': 'Keine Dateien ausgewählt'}), 400

    user_folder = os.path.join(app.config['UPLOAD_FOLDER'], username)
    full_upload_path = os.path.join(user_folder, sub_path.strip("/"))
    os.makedirs(full_upload_path, exist_ok=True)

    saved_files = []
    for file in files:
        if file.filename == '':
            continue
        try:
            filename = secure_filename(file.filename)
            file_path = os.path.join(full_upload_path, filename)

            # Sicherheits-Check
            abs_user_folder = os.path.abspath(user_folder)
            abs_target_path = os.path.abspath(file_path)
            if not abs_target_path.startswith(abs_user_folder):
                return jsonify({'error': 'Ungültiger Pfad'}), 400

            file.save(file_path)
            saved_files.append(os.path.join(sub_path, filename))
        except Exception as e:
            return jsonify({'error': str(e)}), 500

    return jsonify({'message': 'Dateien erfolgreich hochgeladen', 'files': saved_files}), 200

# --- ZIP-Download Route ---
@app.route('/zip_download', methods=['POST'])
def zip_download():
    import os

    data = request.json
    print("Request JSON:", data)

    username = data.get("username")
    files = data.get("files")
    path = data.get("path", "")

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
                # Prüfen, ob der Pfad absolut ist
                if os.path.isabs(rel_path):
                    abs_path = os.path.abspath(rel_path)  # Absoluten Pfad direkt verwenden
                else:
                    full_rel_path = os.path.join(path, rel_path) if path else rel_path
                    safe_rel_path = full_rel_path.strip("/")
                    abs_path = os.path.join(user_dir, safe_rel_path)  # Basis-Pfad nur anhängen, wenn relativ

                # Sicherheitscheck
                abs_user_dir = os.path.abspath(user_dir)
                abs_file_path = os.path.abspath(abs_path)
                if not abs_file_path.startswith(abs_user_dir):
                    return jsonify({"error": f"Ungültiger Pfad: {rel_path}"}), 403

                if os.path.isfile(abs_file_path):
                    zipf.write(abs_file_path, arcname=os.path.relpath(abs_file_path, user_dir))
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

# --- Server starten ---
if __name__ == '__main__':
    app.run(
        debug=True,
        host='0.0.0.0',
        port=5001,
        ssl_context=('/certs/selfsigned.crt', '/certs/selfsigned.key')
    )