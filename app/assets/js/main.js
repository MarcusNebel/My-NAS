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
