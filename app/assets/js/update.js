document.addEventListener("DOMContentLoaded", function () {
    console.log("Marked.js verfügbar:", typeof marked == "function");
    fetch("../../api/check_update.php")
        .then(response => response.json())
        .then(data => {
            console.log(data)
            if (data.update_available) {
                const currentPage = window.location.pathname.split("/").pop();
                if (currentPage === "index.php" || currentPage === "") {
                    const popup = document.createElement("div");
                    popup.id = "update-popup";
                    popup.innerHTML = `

                        <div id="update-popup">
                            <div class="popup-content">
                                <div class="popup-header">
                                    <i class='bx bx-error'></i>
                                    <span>Update Hinweis</span>
                                </div>
                                <div class="popup-body">
                                    <p>Ein neues Update ist verfügbar: <strong>Version ${data.latest_version}</strong>.</p>
                                    <p id="latest-version-release-notes">Release Notes werden geladen...</p>
                                    <p>Bitte aktualisieren Sie My NAS.</p>
                                </div>
                                <div class="popup-footer">
                                    <a href="../../account-system/account.php">Details ansehen</a>
                                    <button id="close-popup">Schließen</button>
                                </div>
                            </div>
                        </div>
                    `;
                    document.body.appendChild(popup);

                    document.getElementById("close-popup").addEventListener("click", function () {
                        popup.remove();
                    });

                    // Release Notes aktualisieren
                    const releaseNotesElement = document.getElementById("latest-version-release-notes");
                    if (data.release_notes) {
                        releaseNotesElement.textContent = data.release_notes;
                    } else {
                        releaseNotesElement.textContent = "Keine Release Notes verfügbar.";
                    }
                }
                
                const releaseNotesElement = document.getElementById("latest-version-release-notes");
                if (releaseNotesElement) {
                    if (data.release_notes) {
                        try {
                            // Markdown in HTML umwandeln
                            const renderedHtml = marked(data.release_notes);
                            releaseNotesElement.innerHTML = renderedHtml;
                        } catch (error) {
                            console.error("Fehler beim Rendern von Markdown:", error);
                            releaseNotesElement.textContent = "";
                        }
                    } else {
                        releaseNotesElement.textContent = "Keine Release Notes verfügbar.";
                    }
                }
            }
        })
        .catch(error => {
            console.error("Fehler beim Abrufen der Update-Informationen:", error);
        });
});