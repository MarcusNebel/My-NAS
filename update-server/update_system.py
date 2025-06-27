import os
import subprocess
import requests
import zipfile
import shutil
import json
import time
import sys

sys.stdout.reconfigure(encoding='utf-8')
sys.stderr.reconfigure(encoding='utf-8')

print("Update-script is running!")

# Docker-Kommandos ausführen
def run_docker_command(command, cwd=None):
    result = subprocess.run(command, shell=True, cwd=cwd, stdout=subprocess.PIPE, stderr=subprocess.PIPE)
    if result.returncode == 0:
        print(f"Command executed successfully: {command}")
        print(result.stdout.decode())
    else:
        print(f"Error executing command: {command}")
        print(result.stderr.decode())
        raise Exception(f"Command failed: {command}")

# Backup der MySQL-Datenbank
def backup_mysql_database(backup_file, container_name, database_name, user, password):
    print(f"Backing up MySQL database {database_name}...")
    try:
        command = [
            "docker", "exec", container_name, "mysqldump", "-u", user, f"-p{password}", database_name
        ]
        with open(backup_file, 'w', encoding="utf-8") as f:
            subprocess.run(command, stdout=f, check=True)
        print(f"MySQL database {database_name} backed up to {backup_file}")
    except Exception as e:
        raise Exception(f"Failed to backup MySQL database: {str(e)}")

# Wiederherstellen der MySQL-Datenbank
def restore_mysql_database(backup_file, container_name, database_name, user, password):
    print(f"Restoring MySQL database {database_name}...")
    print("Waiting to create the MySQL database...")
    for remaining in range(30, 0, -1):
        print(f"Remaining: {remaining} seconds", end='\r')
        time.sleep(1)
    try:
        with open(backup_file, 'rb') as f:
            process = subprocess.run(
                [
                    "docker", "exec", "-i", container_name,
                    "mysql", "-u", user, f"-p{password}", database_name
                ],
                stdin=f,
                stdout=subprocess.PIPE,
                stderr=subprocess.PIPE
            )
        if process.returncode != 0:
            raise Exception(process.stderr.decode())
        print(f"MySQL database {database_name} restored from {backup_file}")
    except Exception as e:
        raise Exception(f"Failed to restore MySQL database: {str(e)}")

# Alte Konfigurationswerte lesen und im Skript speichern
def read_and_store_old_config(config_path):
    try:
        with open(config_path, 'r') as file:
            old_config = json.load(file)
        print(f"Old config.json successfully read and stored: {old_config}")
        return old_config
    except Exception as e:
        raise Exception(f"Failed to read old config.json: {str(e)}")

# Alte Werte in die neue config.json einfügen
def apply_config_to_new_file(new_config_path, old_config):
    try:
        # Neue config.json lesen
        if os.path.exists(new_config_path):
            with open(new_config_path, 'r') as file:
                new_config = json.load(file)
        else:
            raise FileNotFoundError(f"New config file not found: {new_config_path}")

        # Rekursive Funktion, um die Werte aus der alten Konfiguration in die neue zu übertragen
        def overwrite_values(source, destination):
            for key, value in source.items():
                if isinstance(value, dict) and key in destination and isinstance(destination[key], dict):
                    overwrite_values(value, destination[key])  # Rekursion für verschachtelte Werte
                elif key in destination:  # Nur überschreiben, wenn der Schlüssel in der neuen Config existiert
                    destination[key] = value  # Wert überschreiben

        overwrite_values(old_config, new_config)

        # Aktualisierte neue config.json zurückschreiben
        with open(new_config_path, 'w') as file:
            json.dump(new_config, file, indent=4)

        print(f"New config.json successfully updated with stored values from old config.")
    except Exception as e:
        raise Exception(f"Failed to apply old config values to new config.json: {str(e)}")

# Aktualisieren zusätzlicher Dateien
def update_additional_files(source_dir, target_dir, files):
    print("Updating additional files...")
    for file_name in files:
        source_path = os.path.join(source_dir, file_name)
        target_path = os.path.join(target_dir, file_name)
        if os.path.exists(source_path):
            print(f"Updating {file_name}...")
            shutil.copy2(source_path, target_path)
        else:
            print(f"File {file_name} not found in {source_dir}, skipping.")
    print("Additional files updated successfully.")

# Löschen der Container
def remove_containers(containers):
    print("Stopping and removing containers...")
    for container in containers:
        try:
            run_docker_command(f"docker stop {container}")
            run_docker_command(f"docker rm {container}")
        except Exception as e:
            print(f"Warning: Could not stop/remove container {container}: {str(e)}")

# Löschen der Images
def remove_images(images):
    print("Removing images...")
    for image in images:
        try:
            run_docker_command(f"docker rmi -f {image}")
        except Exception as e:
            print(f"Warning: Could not remove image {image}: {str(e)}")

# Neuaufbau und Start der Container
def rebuild_and_start_containers(compose_file):
    if not os.path.isfile(compose_file):
        raise Exception(f"Compose file {compose_file} does not exist! Ensure the file is in the correct location.")

    print("Building and starting containers...")

    try:
        # Build containers
        build_result = subprocess.run(
            f"docker compose {compose_file} build --no-cache",
            shell=True,
            stdout=subprocess.PIPE,
            stderr=subprocess.PIPE,
            encoding="utf-8",  # Verwende UTF-8
            errors="replace"  # Ersetze nicht darstellbare Zeichen
        )

        if build_result.returncode != 0:
            print("STDERR (Build):", build_result.stderr)
            raise Exception(f"Failed to build containers:\n{build_result.stderr}")

        print("Build completed successfully.")
        print(build_result.stdout)

        # Start containers
        start_result = subprocess.run(
            f"docker compose {compose_file} up -d",
            shell=True,
            stdout=subprocess.PIPE,
            stderr=subprocess.PIPE,
            encoding="utf-8",  # Verwende UTF-8
            errors="replace"  # Ersetze nicht darstellbare Zeichen
        )

        if start_result.returncode != 0:
            print("STDERR (Start):", start_result.stderr)
            raise Exception(f"Failed to start containers:\n{start_result.stderr}")

        print("Containers started successfully.")
        print(start_result.stdout)

    except Exception as e:
        raise Exception(f"Failed to rebuild and start containers: {str(e)}")

# Herunterladen des neuesten Releases
def download_latest_release(repo_owner, repo_name, download_dir):
    print(f"Fetching the latest release of {repo_owner}/{repo_name}...")
    url = f"https://api.github.com/repos/{repo_owner}/{repo_name}/releases/latest"
    headers = {"Accept": "application/vnd.github.v3+json"}

    response = requests.get(url, headers=headers)
    if response.status_code == 200:
        release_data = response.json()
        assets = release_data.get("assets", [])
        zip_url = next((asset["browser_download_url"] for asset in assets if asset["name"] == "release.zip"), None)

        if not zip_url:
            raise Exception("release.zip not found in the latest release assets")

        zip_file_path = os.path.join(download_dir, "release.zip")
        shutil.rmtree(download_dir, ignore_errors=True)
        os.makedirs(download_dir, exist_ok=True)

        print(f"Downloading release from {zip_url}...")
        with requests.get(zip_url, stream=True) as r:
            with open(zip_file_path, 'wb') as f:
                shutil.copyfileobj(r.raw, f)

        if not zipfile.is_zipfile(zip_file_path):
            raise Exception(f"The downloaded file is not a valid ZIP archive: {zip_file_path}")

        with zipfile.ZipFile(zip_file_path, 'r') as zip_ref:
            zip_ref.extractall(download_dir)
        os.remove(zip_file_path)

        print(f"Release downloaded and extracted to {download_dir}")
        return download_dir
    else:
        raise Exception(f"Failed to fetch the latest release: {response.status_code} - {response.text}")

# Löschen der Zielordner
def delete_target_folders(folders):
    print("Deleting target folders...")
    for folder in folders:
        if os.path.exists(folder):
            print(f"Deleting folder: {folder}")
            shutil.rmtree(folder)
        else:
            print(f"Folder not found, skipping: {folder}")

# Verschieben der Dateien in den Zielordner
def move_files_to_target(source_dir, target_folders):
    print(f"Moving files from {source_dir} to target folders: {target_folders}...")
    for item in os.listdir(source_dir):
        item_path = os.path.join(source_dir, item)
        if os.path.isdir(item_path) and item in target_folders:
            target_path = os.path.join(".", item)
            print(f"Moving {item_path} to {target_path}...")
            if os.path.exists(target_path):
                shutil.rmtree(target_path)
            shutil.move(item_path, target_path)
        else:
            print(f"Skipping {item_path}, not in target folders.")
    print("Files moved successfully.")

# Löschen des MySQL-Volumes
def remove_mysql_volume(volume_name):
    print(f"Removing MySQL volume {volume_name}...")
    try:
        run_docker_command(f"docker volume rm {volume_name}")
        print(f"MySQL volume {volume_name} removed successfully.")
    except Exception as e:
        print(f"Warning: Could not remove MySQL volume {volume_name}: {str(e)}")

print("Funktionen wurden geladen!")

# Haupt-Update-Logik
def main():
    repo_owner = "MarcusNebel"
    repo_name = "My-NAS"
    containers = ["nas-website", "mysql", "flask-server"]
    images = ["nas-website-nas-website:latest", "nas-website-flask-server:latest"]
    compose_file = "./docker-compose.yml"
    download_dir = "./tmp/nas-update"
    target_folders = ["app", "flask-upload"]
    additional_files = ["init.sql", "LICENSE", "README.md"]
    current_config_path = "./app/config.json"  # Alte config.json
    new_config_path = "./app/config.json"  # Neue config.json
    mysql_backup_file = "./update-server/mysql_backup.sql"
    mysql_container = "mysql"
    mysql_volume = "nas-website_mysql_data"
    mysql_user = "root"
    mysql_password = "59LWrt!mDo6GC4"
    mysql_database = "nas-website"

    try:
        # Alte Konfigurationswerte lesen und zwischenspeichern
        old_config = read_and_store_old_config(current_config_path)

        # Backup der MySQL-Datenbank
        backup_mysql_database(mysql_backup_file, mysql_container, mysql_database, mysql_user, mysql_password)

        # Container und Images entfernen
        remove_containers(containers)
        remove_images(images)
        remove_mysql_volume(mysql_volume)

        # Neueste Version herunterladen
        extracted_dir = download_latest_release(repo_owner, repo_name, download_dir)

        # Zielordner löschen und Dateien verschieben
        delete_target_folders([os.path.join(".", folder) for folder in target_folders])
        move_files_to_target(extracted_dir, target_folders)

        # Zusätzliche Dateien aktualisieren
        update_additional_files(extracted_dir, ".", additional_files)

        # Neue Konfiguration mit gespeicherten alten Werten aktualisieren
        apply_config_to_new_file(new_config_path, old_config)

        # Container neu aufbauen und starten
        rebuild_and_start_containers(compose_file)

        # MySQL-Datenbank wiederherstellen
        restore_mysql_database(mysql_backup_file, mysql_container, mysql_database, mysql_user, mysql_password)

        print("Update process completed successfully.")
    except Exception as e:
        print(f"Update process failed: {str(e)}")
    finally:
        shutil.rmtree(download_dir, ignore_errors=True)

if __name__ == "__main__":
    main()