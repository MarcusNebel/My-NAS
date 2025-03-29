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
        var form = document.createElement("form");
        form.method = "POST";
        form.action = "assets/php/zip_download.php";
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
        document.body.removeChild(form);
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

// Weather function
async function getWeather() {
    let apiKey = localStorage.getItem("weather_api_key");
    let city = localStorage.getItem("weather_city");

    if (!apiKey || !city) {
        apiKey = prompt("Bitte gib deinen OpenWeatherMap API-Key ein. Sie finden diesen unter 'https://home.openweathermap.org/api_keys':");
        city = prompt("Bitte gib deine Stadt ein:");
        
        alert("Die Einrichtung für das Wetter ist abgeschlossen. Sie können die Daten auf der Seite 'Mein Account' zurücksetzen.");

        if (!apiKey || !city) {
            alert("API-Key und Stadt sind erforderlich für das Wetter auf dem Dashboard! Um die Daten einzugeben, laden Sie die Seite neu.");
            return;
        }

        localStorage.setItem("weather_api_key", apiKey);
        localStorage.setItem("weather_city", city);
    }

    // URLs für aktuelle Wetterdaten und Vorhersage
    const currentWeatherUrl = `https://api.openweathermap.org/data/2.5/weather?q=${city}&appid=${apiKey}&units=metric&lang=de`;
    const hourlyForecastUrl = `https://api.openweathermap.org/data/2.5/forecast?q=${city}&appid=${apiKey}&units=metric&lang=de`;

    try {
        // Abrufen der aktuellen Wetterdaten
        const response = await fetch(currentWeatherUrl);
        const data = await response.json();

        if (data.cod !== 200) {
            alert("Fehler: " + data.message);
            return;
        }

        // Anzeige der aktuellen Wetterdaten
        document.getElementById("city").textContent = city;
        document.getElementById("temp").textContent = data.main.temp;
        document.getElementById("condition").textContent = data.weather[0].description;

        // Abrufen der stündlichen Wettervorhersage
        const hourlyResponse = await fetch(hourlyForecastUrl);
        const hourlyData = await hourlyResponse.json();

        if (hourlyData.cod !== "200") {
            alert("Fehler: " + hourlyData.message);
            return;
        }

        // Berechnung der Höchst- und Tiefsttemperatur des aktuellen Tages
        const today = new Date().setHours(0, 0, 0, 0); // 00:00 Uhr des heutigen Tages
        let maxTemp = -Infinity;
        let minTemp = Infinity;
        let foundData = false; // Prüfen, ob Werte für heute gefunden wurden

        hourlyData.list.forEach(entry => {
            const entryDate = new Date(entry.dt * 1000).setHours(0, 0, 0, 0); // 00:00 Uhr des Eintrags
            if (entryDate === today) { 
                maxTemp = Math.max(maxTemp, entry.main.temp_max);
                minTemp = Math.min(minTemp, entry.main.temp_min);
                foundData = true;
            }
        });

        // Falls keine Daten für heute gefunden wurden, Werte auf "N/A" setzen
        document.getElementById("max-temp").textContent = foundData ? maxTemp.toFixed(1) + "°C" : "N/A";
        document.getElementById("min-temp").textContent = foundData ? minTemp.toFixed(1) + "°C" : "N/A";

        // Anzeige der stündlichen Vorhersage (für die nächsten 6 Stunden)
        const forecastList = document.getElementById("hourly-forecast");
        forecastList.innerHTML = ""; // Alte Einträge löschen

        for (let i = 0; i < 6; i++) {
            const hourData = hourlyData.list[i];
            const time = new Date(hourData.dt * 1000).toLocaleTimeString("de-DE", { hour: "2-digit", minute: "2-digit" });
            const temp = hourData.main.temp;
            const condition = hourData.weather[0].description;
            const icon = hourData.weather[0].icon;

            const listItem = document.createElement("li");

            listItem.innerHTML = `
                <img src="http://openweathermap.org/img/wn/${icon}.png" alt="${condition}">
                <div class="time">${time}</div>
                <div class="temp">${temp}°C</div>
                <div class="condition">${condition}</div>
            `;
            
            forecastList.appendChild(listItem);
        }
    } catch (error) {
        console.error("Fehler beim Abrufen der Wetterdaten:", error);
        alert("Es gab einen Fehler beim Abrufen der Wetterdaten. Bitte versuche es später erneut.");
    }
}

// Scroll Animation
const forecastContainer = document.getElementById("hourly-forecast-container");

forecastContainer.addEventListener("wheel", (event) => {
    event.preventDefault();
    
    let scrollAmount = event.deltaY * 0.05; // Reduziert die Scrollgeschwindigkeit für sanfteres Scrollen
    let startTime = performance.now();

    function smoothScroll(currentTime) {
        let elapsedTime = currentTime - startTime;
        let progress = Math.min(elapsedTime / 300, 1); // 300ms für sanftes Scrollen

        forecastContainer.scrollLeft += scrollAmount * progress;

        if (progress < 1) {
            requestAnimationFrame(smoothScroll);
        }
    }

    requestAnimationFrame(smoothScroll);
});

// Funktion zum Zurücksetzen der Wetterdaten
function resetWeather() {
    localStorage.removeItem("weather_api_key");
    localStorage.removeItem("weather_city");
    alert("Daten zurückgesetzt! Lade die Seite neu.");
}

// Wetter beim Laden der Seite abrufen
getWeather();

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
