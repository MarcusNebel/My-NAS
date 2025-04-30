from flask import Flask, jsonify, request
from flask_cors import CORS
from subprocess import Popen, PIPE

app = Flask(__name__)

CORS(app)

# Route to trigger the update process
@app.route('/update', methods=['POST'])
def trigger_update():
    try:
        # Start the update process
        process = Popen(['python', 'update-server/update_system.py'], stdout=PIPE, stderr=PIPE)
        stdout, stderr = process.communicate()

        if process.returncode == 0:
            return jsonify({"status": "success", "message": "Update completed successfully!", "output": stdout.decode()}), 200
        else:
            return jsonify({"status": "failure", "message": "Update failed!", "error": stderr.decode()}), 500

    except Exception as e:
        return jsonify({"status": "error", "message": str(e)}), 500


if __name__ == '__main__':
    app.run(host='0.0.0.0', port=5000)