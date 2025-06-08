window.onload = function () {
	window.addEventListener('scroll', function (e) {
		if (window.pageYOffset > 100) {
			document.querySelector("header").classList.add('is-scrolling');
		} else {
			document.querySelector("header").classList.remove('is-scrolling');
		}
	});

	const menu_btn = document.querySelector('.hamburger');
	const mobile_menu = document.querySelector('.mobile-nav');

	menu_btn.addEventListener('click', function () {
		menu_btn.classList.toggle('is-active');
		mobile_menu.classList.toggle('is-active');
	});
}

async function handleDownload() {
    const configResponse = await fetch('../../config.json'); // Passe den Pfad an die tatsächliche Position der config.json an
	if (!configResponse.ok) {
		throw new Error('Fehler beim Laden der config.json');
	}

	config = await configResponse.json();
    const flaskServerURL = config.flaskServerURL;
    const checkboxes = document.querySelectorAll('.file-checkbox:checked');
    const files = [];
    const folders = []; // Speichert die ausgewählten Ordner

    checkboxes.forEach(checkbox => {
        if (checkbox.dataset.type === 'folder') {
            console.log("Ordner ausgewählt:", checkbox.dataset.fullPath); // Debugging
            folders.push(checkbox.dataset.fullPath); // Ordnerpfad hinzufügen
        } else {
            console.log("Datei ausgewählt:", checkbox.dataset.fullPath); // Debugging
            files.push(checkbox.dataset.fullPath); // Datei hinzufügen
        }
    });

    if (files.length === 0 && folders.length === 0) {
        alert("Bitte wählen Sie mindestens eine Datei oder einen Ordner zum Herunterladen aus.");
        return;
    }

    const username = document.getElementById("username-hidden").value;

    // Den Pfad aus der URL extrahieren (falls vorhanden)
    const urlParams = new URLSearchParams(window.location.search);
    const currentPath = urlParams.get('path') || ''; // Falls kein Pfad angegeben ist, verwenden wir einen leeren String

    if (files.length === 1 && folders.length === 0) {
        // Direktdownload für eine einzelne Datei über das PHP-Skript
        const file = files[0];
        const fileName = file.split('/').pop(); // Extrahiere den Dateinamen
        const filePath = currentPath; // Nutze den aktuellen Pfad aus der URL

        // Debugging
        console.log("Dateiname:", fileName);
        console.log("Pfad:", filePath);

        // Erstellen der GET-URL für den Download
        const downloadUrl = `assets/php/download.php?file=${encodeURIComponent(fileName)}&path=${encodeURIComponent(filePath)}`;
        console.log("Download-URL:", downloadUrl);

        // Starte den Download
        const a = document.createElement("a");
        a.href = downloadUrl;
        a.download = fileName;
        document.body.appendChild(a);
        a.click();
        a.remove();
    } else {
        // Fortfahren mit der ZIP-Logik für mehrere Dateien oder Ordner
        document.getElementById('overlay').style.display = 'flex';

        try {
            // Dateien aus den ausgewählten Ordnern abrufen
            for (const folder of folders) {
                const folderContents = await fetch('assets/php/list_folder_contents.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: new URLSearchParams({ folderPath: folder }),
                });

                if (!folderContents.ok) {
                    const errorDetails = await folderContents.json();
                    throw new Error(errorDetails.error || 'Fehler beim Abrufen des Ordnerinhalts.');
                }

                const folderFiles = await folderContents.json();
                folderFiles.forEach(file => {
                    if (!file.is_dir) {
                        files.push(file.path); // Dateien aus dem Ordner hinzufügen
                    }
                });
            }

            console.log("Username:", username, "Files:", files);

            // Daten an den Flask-Server senden
            console.log("Flask Server URL:", flaskServerURL);
            const response = await fetch(flaskServerURL + "/zip_download", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ username, files, folders, currentPath }) // Füge Ordner hinzu
            });

            if (!response.ok) {
                const errorDetails = await response.json();
                throw new Error(errorDetails.error || "Fehler beim Herunterladen der ZIP-Datei.");
            }

            const disposition = response.headers.get("Content-Disposition");
            const matches = /filename="(.+)"/.exec(disposition);
            const zipName = matches ? matches[1] : "download.zip";

            const blob = await response.blob();
            const downloadUrl = window.URL.createObjectURL(blob);
            const a = document.createElement("a");
            a.href = downloadUrl;
            a.download = zipName;
            document.body.appendChild(a);
            a.click();
            a.remove();
            window.URL.revokeObjectURL(downloadUrl);
        } catch (err) {
            const zipDownloadURL = flaskServerURL + "/zip_download";

            if (err instanceof TypeError && err.message === "Failed to fetch") {
                document.getElementById("downloadStatus").innerHTML =
                    '❌ Verbindung zum Download-Server fehlgeschlagen. ' +
                    'Gehe auf <a href="' + zipDownloadURL + '" target="_blank" rel="noopener noreferrer">' +
                    'diese Seite</a>, vertraue dem Zertifikat und lade die Seite neu.';
            } else {
                alert(`Fehler beim Herunterladen der ZIP-Datei: ${err.message}`);
            }
            console.error("Fehlerdetails:", err);
        } finally {
            document.getElementById('overlay').style.display = 'none';
        }
    }
}

// Event Listener für den Download-Button
document.addEventListener("DOMContentLoaded", function () {
    document.getElementById("download-selected").addEventListener("click", handleDownload);
});

document.querySelectorAll('.file-item').forEach(fileItem => {
    fileItem.addEventListener('dblclick', async (event) => {
        const checkbox = fileItem.querySelector('.file-checkbox');
        if (!checkbox) return;

        const type = checkbox.getAttribute('data-type');

        if (type === 'file') {
            document.querySelectorAll('.file-checkbox').forEach(cb => cb.checked = false);
            checkbox.checked = true;
            await handleDownload();
        }
    });
});

// Event Listener for delete button
function submitDeleteForm() {
    var checkboxes = document.querySelectorAll('.file-checkbox:checked');
    var files = [];
    var folders = [];

    // Dateien und Ordner trennen
    checkboxes.forEach(checkbox => {
        if (checkbox.dataset.type === "folder") {
            folders.push(checkbox.value);
        } else {
            files.push(checkbox.value);
        }
    });

    if (files.length === 0 && folders.length === 0) {
        alert("Bitte wählen Sie mindestens eine Datei oder einen Ordner zum Löschen aus.");
        return;
    }

    // Erste Bestätigungsabfrage
    if (!confirm("Die Dateien werden in den Papierkorb verschoben. Möchten Sie dies tun?")) {
        return;
    }

    // Gemeinsames Formular erstellen
    const urlParams = new URLSearchParams(window.location.search);
    const path = urlParams.get('path') || '';

    var deleteForm = document.createElement("form");
    deleteForm.method = "POST";
    deleteForm.action = `assets/php/delete_handler_combined.php?path=${encodeURIComponent(path)}`;
    deleteForm.style.display = "none";

    // Dateien hinzufügen
    files.forEach(file => {
        var input = document.createElement("input");
        input.type = "hidden";
        input.name = "files[]";
        input.value = file;
        deleteForm.appendChild(input);
    });

    // Ordner hinzufügen
    folders.forEach(folder => {
        var input = document.createElement("input");
        input.type = "hidden";
        input.name = "folders[]";
        input.value = folder;
        deleteForm.appendChild(input);
    });

    document.body.appendChild(deleteForm);
    deleteForm.submit();
}

// Event Listener für Löschen und Download
document.addEventListener("DOMContentLoaded", function() {
    document.getElementById("delete-selected").addEventListener("click", submitDeleteForm);
    document.getElementById("download-selected").addEventListener("click", handleDownload);
});

// Copying the API-Key
function copyApiKey() {
    var apiKeyElement = document.getElementById("apiKey");
    var copyButton = document.getElementById("copyButton");

    if (!apiKeyElement || !copyButton) {
        console.error("Elemente nicht gefunden!");
        return;
    }

    var apiKey = apiKeyElement.innerText.trim();

    if (apiKey === "Kein API-Schlüssel vorhanden") {
        copyButton.classList.remove("bxs-copy", "copied");
        copyButton.classList.add("bxs-error", "error");
        copyButton.title = "Kein Schlüssel!";
    } else {
        navigator.clipboard.writeText(apiKey).then(() => {
            copyButton.classList.remove("bxs-copy", "error");
            copyButton.classList.add("bxs-check-circle", "copied");
            copyButton.title = "Kopiert!";
        }).catch(err => {
            console.error("Fehler beim Kopieren:", err);
            copyButton.classList.remove("bxs-copy", "copied");
            copyButton.classList.add("bxs-error", "error");
            copyButton.title = "Fehler!";
        });
    }

    // Nach 2 Sekunden Icon zurücksetzen
    setTimeout(() => {
        copyButton.classList.remove("bxs-check-circle", "bxs-error", "copied", "error");
        copyButton.classList.add("bxs-copy");
        copyButton.title = "Kopieren";
    }, 2000);
}

// index.php Scroll inicator
const scrollIndicator = document.getElementById("scroll-indicator");

window.addEventListener("scroll", () => {
    const scrollY = window.scrollY; // Aktueller Scrollwert

    // Wenn mehr als 50px nach unten gescrollt wurde, Pfeil ausblenden
    if (scrollY > 50) {
        scrollIndicator.style.display = "none";  // Pfeil wird unsichtbar
    } else {
        scrollIndicator.style.display = "";  // Pfeil wird wieder sichtbar
    }
});

// Optional: Wenn der Benutzer auf den Pfeil klickt, nach unten scrollen
scrollIndicator.addEventListener("click", () => {
    window.scrollBy({
        top: window.innerHeight, // Scrollt genau eine Bildschirmhöhe nach unten
        behavior: 'smooth'
    });
});

// + Dropdown-menu
document.getElementById("toggle-menu").addEventListener("click", function() {
    let menu = document.getElementById("dropdown-menu");
    // Toggle Visibility of the dropdown
    if (menu.style.display === "block") {
        menu.style.display = "none";
    } else {
        menu.style.display = "block";
    }
});

// Schließt das Menü, wenn außerhalb geklickt wird
document.addEventListener("click", function(event) {
    let menu = document.getElementById("dropdown-menu");
    let button = document.getElementById("toggle-menu");

    if (!menu.contains(event.target) && !button.contains(event.target)) {
        menu.style.display = "none";
    }
});

// Show File-Ipload-Section after clicking on add file
document.getElementById("upload-file").addEventListener("click", function() {
    // Versteckt alle anderen Abschnitte und zeigt den Datei-Upload
    document.getElementById("upload-section").style.display = "block";
});

// Optional: Falls du den Upload-Fortschritt und Geschwindigkeit anzeigen möchtest
const uploadForm = document.getElementById("uploadForm");
uploadForm.addEventListener("submit", function(event) {
    event.preventDefault();

    const formData = new FormData(uploadForm);
    const xhr = new XMLHttpRequest();
    xhr.open("POST", uploadForm.action, true);

    // Zeigt Fortschrittsbalken während des Uploads
    xhr.upload.onprogress = function(event) {
        if (event.lengthComputable) {
            const percent = (event.loaded / event.total) * 100;
            document.getElementById("progress-bar").style.width = percent + "%";
            document.getElementById("progress-bar").innerText = Math.round(percent) + "%";
        }
    };

    // Zeigt die Upload-Geschwindigkeit
    let startTime = Date.now();
    xhr.upload.onloadstart = function() {
        startTime = Date.now();
    };

    xhr.upload.onloadend = function() {
        const endTime = Date.now();
        const uploadDuration = (endTime - startTime) / 1000; // in Sekunden
        const fileSize = uploadForm.querySelector('input[type="file"]').files[0].size / 1024 / 1024; // in MB
        const speed = (fileSize / uploadDuration).toFixed(2); // Geschwindigkeit in MB/s
        document.getElementById("upload-speed").innerText = `Upload-Geschwindigkeit: ${speed} MB/s`;
    };

    xhr.send(formData);
});

async function fetchStats() {
    try {
        const response = await fetch('../php/stats.php');
        const data = await response.json();

        if (data.cpu !== undefined && data.ramUsed !== undefined && data.ramTotal !== undefined) {
            updateCPUChart(data.cpu);
            updateRAMChart(data.ramUsed, data.ramTotal);
        }
    } catch (error) {
        console.error("Fehler beim Abrufen der Systemdaten:", error);
    }
}

function updateCPUChart(cpuUsage) {
    if (cpuChart) {
        cpuChart.data.datasets[0].data.push(cpuUsage);
        if (cpuChart.data.datasets[0].data.length > 10) {
            cpuChart.data.datasets[0].data.shift(); // Älteste Werte entfernen
        }
        cpuChart.update();
    }
}

function updateRAMChart(ramUsed, ramTotal) {
    if (ramChart) {
        ramChart.data.datasets[0].data = [ramUsed, ramTotal - ramUsed];
        ramChart.update();
    }
}

setInterval(fetchStats, 1000); // Alle 1 Sekunde aktualisieren

// Initial laden
fetchStats();

const ctxCPU = document.getElementById('cpuChart').getContext('2d');
const cpuChart = new Chart(ctxCPU, {
    type: 'line',
    data: {
        labels: Array(10).fill(""),
        datasets: [{
            label: 'CPU-Auslastung (%)',
            data: [],
            borderColor: 'rgb(40, 148, 244)',
            borderWidth: 2,
            fill: false
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: {
                beginAtZero: true,
                max: 100
            }
        }
    }
});

const ctxRAM = document.getElementById('ramChart').getContext('2d');
const ramChart = new Chart(ctxRAM, {
    type: 'doughnut',
    data: {
        labels: ['Benutzt', 'Frei'],
        datasets: [{
            label: 'RAM-Nutzung (MB)',
            data: [],
            backgroundColor: ['#2894f4', '#dddddd'],
            hoverOffset: 4
        }]
    }
});
