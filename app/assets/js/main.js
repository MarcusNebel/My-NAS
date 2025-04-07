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

function handleDownload() {
    var checkboxes = document.querySelectorAll('.file-checkbox:checked');
    var files = [];

    checkboxes.forEach(checkbox => {
        files.push(checkbox.value);
    });

    if (files.length === 0) {
        alert("Bitte wählen Sie mindestens eine Datei zum Herunterladen aus.");
        return;
    } else if (files.length === 1) {
        // Direktes Herunterladen einer einzelnen Datei
        window.location.href = "assets/php/download.php?file=" + encodeURIComponent(files[0]);
    } else {
        // Mehrere Dateien als ZIP herunterladen
        var formData = new FormData();
        files.forEach(file => {
            formData.append('files[]', file);
        });

        // Overlay anzeigen, um dem Benutzer zu zeigen, dass der Download läuft
        document.getElementById('overlay').style.display = 'flex';

        // AJAX Request zum Herunterladen der ZIP-Datei
        var xhr = new XMLHttpRequest();
        xhr.open('POST', 'assets/php/zip_download.php', true);
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');  // Setzen des Headers, um den Ajax-Request zu kennzeichnen

        xhr.onload = function () {
            if (xhr.status === 200) {
                // Die Antwort des Servers (Dateipfad und Dateiname) parsieren
                var response = JSON.parse(xhr.responseText);
                var downloadLink = document.createElement('a');
                downloadLink.href = response.filePath; // Die URL der heruntergeladenen Datei
                downloadLink.download = response.zipName; // Der Dateiname der ZIP-Datei
                downloadLink.click();

                // Overlay wieder ausblenden, wenn der Download bereit ist
                document.getElementById('overlay').style.display = 'none';
            } else {
                alert('Fehler beim Herunterladen der Datei.');
                document.getElementById('overlay').style.display = 'none'; // Overlay ausblenden, falls ein Fehler auftritt
            }
        };

        xhr.send(formData);
    }
}

// Event Listener für den Download-Button
document.addEventListener("DOMContentLoaded", function() {
    document.getElementById("download-selected").addEventListener("click", handleDownload);
});

// Event Listener for delete button
function submitDeleteForm() {
    var checkboxes = document.querySelectorAll('.file-checkbox:checked');
    var files = [];

    checkboxes.forEach(checkbox => {
        files.push(checkbox.value);
    });

    if (files.length === 0) {
        alert("Bitte wählen Sie mindestens eine Datei zum Löschen aus.");
        return;
    }

    // Bestätigung einholen
    if (!confirm("Möchten Sie die ausgewählten Dateien wirklich löschen?")) {
        return;
    }

    // Form für das Löschen erstellen und absenden
    var form = document.createElement("form");
    form.method = "POST";
    form.action = "assets/php/delete_handler.php";
    form.style.display = "none";

    files.forEach(file => {
        var input = document.createElement("input");
        input.type = "hidden";
        input.name = "files[]";
        input.value = file;
        form.appendChild(input);
    });

    document.body.appendChild(form);
    form.submit();
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

// Detail Windows on the right side
document.addEventListener("DOMContentLoaded", function () {
    const checkboxes = document.querySelectorAll(".file-checkbox");
    const fileInfoPanel = document.getElementById("file-info-panel");
    const fileInfoContent = document.getElementById("file-info-content");

    checkboxes.forEach(checkbox => {
        checkbox.addEventListener("change", updateFileInfo);
    });

    function updateFileInfo() {
        let selectedFiles = Array.from(checkboxes).filter(checkbox => checkbox.checked);

        if (selectedFiles.length === 0) {
            fileInfoPanel.style.display = "none"; // Panel ausblenden
            return;
        }

        let totalSize = 0;
        let fileList = [];

        selectedFiles.forEach(file => {
            let fileName = file.getAttribute("data-name");
            let fileSize = parseInt(file.getAttribute("data-size"));
            let filePath = file.getAttribute("data-path");

            totalSize += fileSize;

            fileList.push({
                name: fileName,
                size: formatFileSize(fileSize),
                path: filePath
            });
        });

        if (selectedFiles.length === 1) {
            // Einzelne Datei anzeigen
            let file = fileList[0];
            fileInfoContent.innerHTML = `
                <h4>📄 ${file.name}</h4>
                <p><span>Größe:</span> ${file.size}</p>
                <p><span>Pfad:</span> ${file.path}</p>
            `;
        } else {
            // Mehrere Dateien: Anzahl + Gesamtgröße
            fileInfoContent.innerHTML = `
                <h4>📂 ${selectedFiles.length} Dateien ausgewählt</h4>
                <p><span>Gesamtgröße:</span> ${formatFileSize(totalSize)}</p>
            `;
        }

        fileInfoPanel.style.display = "block"; // Panel anzeigen
    }

    function formatFileSize(bytes) {
        if (bytes < 1024) return bytes + " B";
        else if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(2) + " KB";
        else if (bytes < 1024 * 1024 * 1024) return (bytes / (1024 * 1024)).toFixed(2) + " MB";
        else return (bytes / (1024 * 1024 * 1024)).toFixed(2) + " GB";
    }
});

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
