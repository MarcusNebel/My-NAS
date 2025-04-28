document.addEventListener("DOMContentLoaded", function () {
    fetch("../../api/check_update.php")
        .then(response => response.json())
        .then(data => {
            if (data.update_available) {
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
                                <p>Bitte aktualisieren Sie My NAS.</p>
                            </div>
                            <div class="popup-footer">
                                <button id="close-popup">Schließen</button>
                            </div>
                        </div>
                    </div>
                `;
                document.body.appendChild(popup);

                document.getElementById("close-popup").addEventListener("click", function () {
                    popup.remove();
                });
            }
        })
        .catch(error => {
            console.error("Fehler beim Abrufen der Update-Informationen:", error);
        });
});