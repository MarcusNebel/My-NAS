import os
from flask import Flask, request, jsonify
from flask_cors import CORS

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

if __name__ == '__main__':
    app.run(
        debug=True,
        host='0.0.0.0',
        port=5001,
        ssl_context=('/certs/selfsigned.crt', '/certs/selfsigned.key')
    )
