/* SAMS Dokumentensystem – QR-Codes und Bildexport der Dokumente */
(function () {
    'use strict';

    // QR-Codes für die Echtheitsprüfung erzeugen
    document.querySelectorAll('[data-qr]').forEach(function (el) {
        if (typeof qrcode !== 'function') { return; }
        var qr = qrcode(0, 'M');
        qr.addData(el.dataset.qr);
        qr.make();
        el.innerHTML = qr.createSvgTag({ cellSize: 2, margin: 0, scalable: true });
    });

    // Dokument als PNG speichern (z.B. zum Teilen im Discord / In-Game-Handy)
    document.querySelectorAll('[data-export-png]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            if (typeof html2canvas !== 'function') { alert('Bildexport nicht verfügbar.'); return; }
            var papers = document.querySelectorAll('#doc-stage .paper');
            var label = btn.textContent;
            btn.disabled = true;
            btn.textContent = 'Wird erstellt …';
            var jobs = Array.prototype.map.call(papers, function (paper) {
                return html2canvas(paper, { scale: 2, backgroundColor: null, useCORS: true, logging: false });
            });
            Promise.all(jobs).then(function (canvases) {
                // Mehrere Seiten untereinander zu einem Bild zusammenfügen
                var gap = 40;
                var width = Math.max.apply(null, canvases.map(function (c) { return c.width; }));
                var height = canvases.reduce(function (s, c) { return s + c.height; }, 0) + gap * (canvases.length - 1);
                var out = document.createElement('canvas');
                out.width = width; out.height = height;
                var ctx = out.getContext('2d');
                var y = 0;
                canvases.forEach(function (c) { ctx.drawImage(c, (width - c.width) / 2, y); y += c.height + gap; });
                var a = document.createElement('a');
                a.download = btn.dataset.exportPng + '.png';
                a.href = out.toDataURL('image/png');
                document.body.appendChild(a); a.click(); a.remove();
            }).catch(function (e) {
                alert('Bildexport fehlgeschlagen: ' + e);
            }).finally(function () {
                btn.disabled = false; btn.textContent = label;
            });
        });
    });
})();
