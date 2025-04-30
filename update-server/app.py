from flask import Flask, request, jsonify
from flask_cors import CORS
import requests
import json

app = Flask(__name__)
CORS(app)

# Lade die Konfiguration aus der config.json
with open("/config/config.json", "r") as config_file:
    config = json.load(config_file)

# Hole die Update-Server-IP aus der Konfiguration
update_server_ip = config.get("update_server_ip")
if not update_server_ip:
    raise ValueError("Die IP-Adresse des Update-Servers ist in der config.json nicht definiert.")

# Proxy-Route
@app.route('/proxy-update', methods=['POST'])
def proxy_update():
    # Ziel-URL des Update-Servers
    update_server_url = f"{update_server_ip}/update"

    try:
        # Sende die Anfrage an den Update-Server
        response = requests.post(update_server_url, json=request.json)

        # Antwort des Update-Servers zurückgeben
        return (response.text, response.status_code, response.headers.items())
    except requests.exceptions.RequestException as e:
        return jsonify({"error": str(e)}), 500

if __name__ == '__main__':
    app.run(host='0.0.0.0', port=5002, ssl_context=('/certs/selfsigned.crt', '/certs/selfsigned.key'), debug=True)