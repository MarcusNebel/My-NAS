console.log("Script läuft!");
window.addEventListener('load', function () {
    console.log("Screen width:", window.innerWidth);
    if (window.innerWidth < 768) { // z. B. unter 768px = Mobilgerät
        alert("Diese Seite ist nur auf größeren Geräten verfügbar.");
    window.location.href = "../../account-system/account.php"; // Seite für blockierte Geräte
    }
});