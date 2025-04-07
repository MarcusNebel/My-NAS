document.getElementById('uploadForm').addEventListener('submit', function (event) {
    event.preventDefault(); // Verhindert das Standardformular-Submissionsverhalten

    let formData = new FormData();
    let fileInput = document.getElementById('fileInput');
    formData.append('file', fileInput.files[0]);

    let xhr = new XMLHttpRequest();
    let startTime = Date.now(); // Startzeit für die Berechnung der Upload-Geschwindigkeit

    // Fortschrittsbalken und Geschwindigkeit
    let progressBar = document.getElementById('progress-bar');
    let uploadStatus = document.getElementById('uploadStatus');
    let uploadSpeed = document.getElementById('upload-speed');

    // Event-Listener für den Upload-Fortschritt
    xhr.upload.addEventListener('progress', function (event) {
        if (event.lengthComputable) {
            // Berechnung des Fortschritts (Prozentualer Wert)
            let percent = (event.loaded / event.total) * 100;
            progressBar.style.width = percent + '%';
            progressBar.innerHTML = Math.round(percent) + '%';

            // Berechnung der Upload-Geschwindigkeit in MB/s (statt KB/s)
            let elapsedTime = (Date.now() - startTime) / 1000;  // Zeit in Sekunden
            let speed = (event.loaded / (1024 * 1024)) / elapsedTime;  // Geschwindigkeit in MB/s
            uploadSpeed.innerText = 'Upload-Geschwindigkeit: ' + speed.toFixed(1) + ' MB/s';
        }
    });

    // Event-Listener für das Ende des Uploads
    xhr.addEventListener('load', function () {
        if (xhr.status === 200) {
            uploadStatus.innerText = 'Datei erfolgreich hochgeladen!';
            window.location.href = "../../User_Files.php";
        } else {
            uploadStatus.innerText = 'Fehler beim Hochladen der Datei.';
        }
    });

    // Fehlerbehandlung für den Upload
    xhr.addEventListener('error', function () {
        uploadStatus.innerText = 'Es gab einen Fehler beim Hochladen der Datei. Möglicherweise wurde die Verbindung blockiert oder unterbrochen.';
        console.error('Upload-Fehler:', xhr.status, xhr.statusText);
    });

    // Sende die Formulardaten an die Flask-Upload-Route
    xhr.open('POST', 'http://localhost:5001/api/upload-file', true);  // URL zum Flask-Server
    xhr.send(formData);
});
