// Funktion zum Setzen eines Cookies
function setCookie(name, value, days) {
    let expires = "";
    if (days) {
        let date = new Date();
        date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000)); // Gültigkeit in Tagen
        expires = "; expires=" + date.toUTCString();
    }
    document.cookie = name + "=" + value + "; path=/" + expires;
}

// Funktion zum Abrufen eines Cookies
function getCookie(name) {
    let nameEQ = name + "=";
    let cookies = document.cookie.split(";");
    for (let i = 0; i < cookies.length; i++) {
        let c = cookies[i].trim();
        if (c.indexOf(nameEQ) === 0) return c.substring(nameEQ.length, c.length);
    }
    return null;
}

// Funktion zum Abrufen der Sprache (aus URL oder Cookie)
function getLanguage() {
    const params = new URLSearchParams(window.location.search);
    const urlLang = params.get("lang");
    const cookieLang = getCookie("language");

    if (urlLang) {
        setCookie("language", urlLang, 30); // Sprache im Cookie speichern (30 Tage)
        return urlLang;
    }

    return cookieLang || "en"; // Standard ist Englisch
}

// Funktion zum Setzen der Sprache in der URL & im Cookie
function setLanguage(lang) {
    const newUrl = new URL(window.location);
    newUrl.searchParams.set("lang", lang);
    window.history.pushState({}, "", newUrl); // URL aktualisieren ohne Neuladen
    setCookie("language", lang, 30); // Cookie setzen
    loadLanguage(lang);
}

// Sprache aus JSON-Datei laden und anwenden
function loadLanguage(lang) {
    fetch(`../../lang/${lang}.json`)
        .then(response => response.json())
        .then(data => {
            document.querySelectorAll("[data-lang]").forEach(el => {
                el.innerText = data[el.dataset.lang] || el.dataset.lang;
            });
            document.getElementById("lang-switcher").value = lang; // Dropdown aktualisieren
        });
}

// Event Listener für den Sprachwechsel im Dropdown
document.addEventListener("DOMContentLoaded", () => {
    document.getElementById("lang-switcher")?.addEventListener("change", (e) => {
        setLanguage(e.target.value);
    });

    // Sprache beim Laden setzen (erst URL prüfen, dann Cookie)
    const currentLang = getLanguage();
    loadLanguage(currentLang);
});
