sudo apt install python3-full
python3 -m venv ~/python-env/venv

~/python-env/venv/bin/pip install -r update-server/requirements.txt

~/python-env/venv/bin/python3 update-server/update_flask_server.py