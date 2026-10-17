/* Brücke zwischen Websystem und Ingame-Tablet (FiveM-Resource sams_tablet).
   Läuft das System im Tablet (iframe), können Seiten Spielfunktionen nutzen,
   z. B. die aktuelle Position für einen Drucker. Außerhalb des Spiels inaktiv. */
(function () {
    'use strict';
    var inTablet = window.parent !== window;
    var pending = {};
    var seq = 0;

    window.addEventListener('message', function (e) {
        if (e.source !== window.parent || !e.data || typeof e.data.sams !== 'string') { return; }
        if (e.data.reply && pending[e.data.reply]) {
            pending[e.data.reply](e.data.data || {});
            delete pending[e.data.reply];
        }
    });

    window.SAMSGame = {
        inTablet: inTablet,
        /** Anfrage ans Spiel, Antwort als Promise (z. B. 'getCoords'). */
        request: function (type, data) {
            return new Promise(function (resolve, reject) {
                if (!inTablet) { reject(new Error('Nur im Ingame-Tablet verfügbar')); return; }
                var id = 'r' + (++seq);
                pending[id] = resolve;
                window.parent.postMessage({ sams: type, id: id, data: data || {} }, '*');
                setTimeout(function () { if (pending[id]) { delete pending[id]; reject(new Error('Keine Antwort vom Spiel')); } }, 5000);
            });
        },
        /** Meldung ans Spiel ohne Antwort (z. B. 'printQueued'). */
        notify: function (type, data) {
            if (inTablet) { window.parent.postMessage({ sams: type, data: data || {} }, '*'); }
        }
    };

    if (inTablet) {
        document.documentElement.classList.add('in-tablet');
        // ESC schließt das Tablet auch, wenn der Fokus im Websystem liegt
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && !document.querySelector('dialog[open]')) {
                window.parent.postMessage({ sams: 'closeTablet' }, '*');
            }
        });
    }
    document.querySelectorAll('[data-game-only]').forEach(function (el) {
        el.hidden = !inTablet;
    });
    document.querySelectorAll('[data-browser-only]').forEach(function (el) {
        el.hidden = inTablet;
    });
    // Seiten können eine Meldung ans Spiel hinterlegen (z. B. nach einem Druckauftrag)
    document.querySelectorAll('[data-game-notify]').forEach(function (el) {
        try { window.SAMSGame.notify(el.dataset.gameNotify, JSON.parse(el.dataset.payload || '{}')); } catch (err) { /* ignorieren */ }
    });
})();
