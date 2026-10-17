/* SAMS Dokumentensystem – Oberfläche */
(function () {
    'use strict';

    // Mobile Navigation
    var toggle = document.querySelector('.nav-toggle');
    if (toggle) {
        toggle.addEventListener('click', function () { document.body.classList.toggle('nav-open'); });
    }

    // Verlauf filtern (Akte)
    document.querySelectorAll('.tabs[data-filter]').forEach(function (tabs) {
        var target = document.querySelector(tabs.dataset.filter);
        tabs.querySelectorAll('button').forEach(function (btn) {
            btn.addEventListener('click', function () {
                tabs.querySelectorAll('button').forEach(function (b) { b.classList.remove('active'); });
                btn.classList.add('active');
                target.querySelectorAll('[data-kind]').forEach(function (el) {
                    el.style.display = btn.dataset.show === 'all' || el.dataset.kind === btn.dataset.show ? '' : 'none';
                });
            });
        });
    });

    // Textbausteine in Textfelder einfügen
    document.querySelectorAll('[data-insert]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var ta = btn.closest('.field').querySelector('textarea');
            ta.value = (ta.value ? ta.value + '\n\n' : '') + btn.dataset.insert;
            ta.focus();
        });
    });

    // Vorschlagsliste (ICD, Medikamente) an Textarea anhängen
    document.querySelectorAll('input.suggest').forEach(function (inp) {
        var add = function () {
            var ta = document.getElementById(inp.dataset.target);
            if (!inp.value.trim()) { return; }
            ta.value = (ta.value.trim() ? ta.value.replace(/\s+$/, '') + '\n' : '') + inp.value.trim();
            inp.value = '';
        };
        inp.addEventListener('keydown', function (e) { if (e.key === 'Enter') { e.preventDefault(); add(); } });
        inp.addEventListener('change', function () {
            // Auswahl aus Datalist direkt übernehmen
            var list = document.getElementById(inp.getAttribute('list'));
            if (list && Array.prototype.some.call(list.options, function (o) { return o.value === inp.value; })) { add(); }
        });
    });

    // Tabellenzeilen (Positionen)
    document.querySelectorAll('table.items').forEach(function (table) {
        var name = table.dataset.name;
        var max = parseInt(table.dataset.max || '50', 10);
        var tbody = table.querySelector('tbody');
        var renumber = function () {
            tbody.querySelectorAll('tr').forEach(function (tr, i) {
                tr.querySelectorAll('input').forEach(function (inp) {
                    inp.name = inp.name.replace(new RegExp('^d\\[' + name + '\\]\\[\\d+\\]'), 'd[' + name + '][' + i + ']');
                });
            });
        };
        table.nextElementSibling.addEventListener('click', function () {
            if (tbody.rows.length >= max) { return; }
            var tr = tbody.rows[0].cloneNode(true);
            tr.querySelectorAll('input').forEach(function (inp) {
                if (inp.type === 'checkbox') { inp.checked = false; } else { inp.value = inp.defaultValue && inp.type === 'number' ? inp.getAttribute('value') : ''; }
            });
            tbody.appendChild(tr);
            renumber();
            tr.querySelector('input').focus();
        });
        tbody.addEventListener('click', function (e) {
            if (!e.target.classList.contains('row-del')) { return; }
            if (tbody.rows.length === 1) {
                tbody.rows[0].querySelectorAll('input').forEach(function (inp) { inp.value = ''; inp.checked = false; });
                return;
            }
            e.target.closest('tr').remove();
            renumber();
        });
    });

    // Unterschrift-Pad (Maus, Stift, Touch) mit druckabhängiger Strichstärke
    document.querySelectorAll('.sigpad').forEach(function (pad) {
        var canvas = pad.querySelector('canvas');
        var input = document.getElementById(pad.dataset.input);
        var ctx = canvas.getContext('2d');
        var drawing = false, last = null, dirty = false;
        ctx.lineCap = 'round';
        ctx.lineJoin = 'round';
        ctx.strokeStyle = '#1b2d78';

        var pos = function (e) {
            var r = canvas.getBoundingClientRect();
            return { x: (e.clientX - r.left) * canvas.width / r.width, y: (e.clientY - r.top) * canvas.height / r.height, p: e.pressure || 0.5, t: Date.now() };
        };
        var save = function () {
            if (!dirty) { return; }
            input.value = trimmed(canvas);
        };
        canvas.addEventListener('pointerdown', function (e) {
            drawing = true; last = pos(e); canvas.setPointerCapture(e.pointerId); e.preventDefault();
        });
        canvas.addEventListener('pointermove', function (e) {
            if (!drawing) { return; }
            var p = pos(e);
            var dist = Math.hypot(p.x - last.x, p.y - last.y);
            var speed = dist / Math.max(1, p.t - last.t);
            var w = Math.max(1.4, Math.min(4.2, (e.pointerType === 'pen' ? p.p * 5 : 3.4) - speed * 0.9));
            ctx.lineWidth = last.w ? (last.w * 0.6 + w * 0.4) : w;
            ctx.beginPath();
            ctx.moveTo(last.x, last.y);
            ctx.quadraticCurveTo(last.x, last.y, (last.x + p.x) / 2, (last.y + p.y) / 2);
            ctx.lineTo(p.x, p.y);
            ctx.stroke();
            p.w = ctx.lineWidth;
            last = p;
            dirty = true;
        });
        ['pointerup', 'pointerleave', 'pointercancel'].forEach(function (ev) {
            canvas.addEventListener(ev, function () { if (drawing) { drawing = false; save(); } });
        });
        pad.querySelector('.sig-clear').addEventListener('click', function () {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            input.value = '';
            dirty = false;
        });
        // Vorhandene Unterschrift anzeigen
        if (input.value) {
            var img = new Image();
            img.onload = function () {
                var s = Math.min(canvas.width / img.width, canvas.height / img.height, 1);
                ctx.drawImage(img, (canvas.width - img.width * s) / 2, (canvas.height - img.height * s) / 2, img.width * s, img.height * s);
            };
            img.src = input.value;
        }
    });

    // Leeren Rand der Unterschrift abschneiden → PNG mit Transparenz
    function trimmed(canvas) {
        var ctx = canvas.getContext('2d');
        var d = ctx.getImageData(0, 0, canvas.width, canvas.height).data;
        var minX = canvas.width, minY = canvas.height, maxX = -1, maxY = -1;
        for (var y = 0; y < canvas.height; y++) {
            for (var x = 0; x < canvas.width; x++) {
                if (d[(y * canvas.width + x) * 4 + 3] > 10) {
                    if (x < minX) { minX = x; } if (x > maxX) { maxX = x; }
                    if (y < minY) { minY = y; } if (y > maxY) { maxY = y; }
                }
            }
        }
        if (maxX < 0) { return ''; }
        var pad = 6;
        minX = Math.max(0, minX - pad); minY = Math.max(0, minY - pad);
        maxX = Math.min(canvas.width - 1, maxX + pad); maxY = Math.min(canvas.height - 1, maxY + pad);
        var out = document.createElement('canvas');
        out.width = maxX - minX + 1; out.height = maxY - minY + 1;
        out.getContext('2d').drawImage(canvas, minX, minY, out.width, out.height, 0, 0, out.width, out.height);
        return out.toDataURL('image/png');
    }

    // Körperschema: Klick setzt nummerierte Markierung
    document.querySelectorAll('.bodymap-editor').forEach(function (wrap) {
        var svg = wrap.querySelector('svg');
        var input = document.getElementById(wrap.dataset.input);
        var marks = svg.querySelector('.marks');
        var points = [];
        try { points = JSON.parse(input.value || '[]'); } catch (e) { points = []; }
        var ns = 'http://www.w3.org/2000/svg';
        var draw = function () {
            marks.innerHTML = '';
            points.forEach(function (pt, i) {
                var g = document.createElementNS(ns, 'g'); g.setAttribute('class', 'mark'); g.dataset.i = i;
                var c = document.createElementNS(ns, 'circle');
                c.setAttribute('cx', pt.x * 2.2); c.setAttribute('cy', pt.y * 1.75); c.setAttribute('r', 5);
                var t = document.createElementNS(ns, 'text');
                t.setAttribute('x', pt.x * 2.2); t.setAttribute('y', pt.y * 1.75 + 2.1); t.setAttribute('text-anchor', 'middle');
                t.textContent = i + 1;
                g.appendChild(c); g.appendChild(t); marks.appendChild(g);
            });
            input.value = JSON.stringify(points);
        };
        svg.addEventListener('click', function (e) {
            var mark = e.target.closest('.mark');
            if (mark) { points.splice(parseInt(mark.dataset.i, 10), 1); draw(); return; }
            var pt = svg.createSVGPoint(); pt.x = e.clientX; pt.y = e.clientY;
            var p = pt.matrixTransform(svg.getScreenCTM().inverse());
            points.push({ x: Math.round(p.x / 2.2 * 10) / 10, y: Math.round(p.y / 1.75 * 10) / 10 });
            draw();
        });
    });

    // In Zwischenablage kopieren
    document.querySelectorAll('[data-copy]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var text = btn.dataset.copy;
            var done = function () { var o = btn.textContent; btn.textContent = 'Kopiert ✓'; setTimeout(function () { btn.textContent = o; }, 1500); };
            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(text).then(done);
            } else {
                var ta = document.createElement('textarea'); ta.value = text; document.body.appendChild(ta); ta.select();
                document.execCommand('copy'); ta.remove(); done();
            }
        });
    });
})();

/* KIS-Rahmen: Schnellsuche (F2) und Uhr in der Statusleiste */
(function () {
    'use strict';
    var qs = document.getElementById('quicksearch');
    document.addEventListener('keydown', function (e) {
        if (e.key === 'F2' && qs) { e.preventDefault(); qs.focus(); qs.select(); }
        if (e.key === 'Escape' && document.activeElement === qs) { qs.blur(); }
    });
    var clock = document.querySelector('[data-clock]');
    if (clock) {
        var pad = function (n) { return (n < 10 ? '0' : '') + n; };
        var tick = function () {
            var d = new Date();
            clock.textContent = pad(d.getDate()) + '.' + pad(d.getMonth() + 1) + '.' + d.getFullYear() + ' ' + pad(d.getHours()) + ':' + pad(d.getMinutes());
        };
        tick();
        setInterval(tick, 15000);
    }
    // Farbwähler und Textfeld synchron halten (Einstellungen → Design)
    document.querySelectorAll('.color-row').forEach(function (row) {
        var color = row.querySelector('input[type=color]');
        var text = row.querySelector('input[type=text]');
        color.addEventListener('input', function () { text.value = color.value; });
        text.addEventListener('input', function () { if (/^#[0-9a-f]{6}$/i.test(text.value)) { color.value = text.value; } });
    });
})();

/* Rechte-Auswahl im Rang-Editor */
(function () {
    'use strict';
    var boxes = function (scope) {
        return Array.prototype.filter.call(scope.querySelectorAll('input[name="perms[]"]'), function (b) { return !b.disabled; });
    };
    document.querySelectorAll('[data-perm-all]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            boxes(document).forEach(function (b) { b.checked = btn.dataset.permAll === '1'; });
            sync();
        });
    });
    var sync = function () {
        document.querySelectorAll('.perm-group').forEach(function (g) {
            var toggle = g.querySelector('.perm-group-toggle');
            var list = boxes(g);
            toggle.disabled = list.length === 0;
            toggle.checked = list.length > 0 && list.every(function (b) { return b.checked; });
            toggle.indeterminate = !toggle.checked && list.some(function (b) { return b.checked; });
        });
    };
    document.querySelectorAll('.perm-group').forEach(function (g) {
        g.querySelector('.perm-group-toggle').addEventListener('change', function (e) {
            boxes(g).forEach(function (b) { b.checked = e.target.checked; });
            sync();
        });
        g.addEventListener('change', function (e) { if (e.target.name === 'perms[]') { sync(); } });
    });
    sync();
})();

// Dokumentformular: Schnell-/Vollmodus und Wahl der Unterschrift
(function () {
    'use strict';
    document.querySelectorAll('[data-mode-for]').forEach(function (tabs) {
        var form = document.querySelector(tabs.dataset.modeFor);
        if (!form) { return; }
        var set = function (mode) {
            form.classList.toggle('quick', mode === 'quick');
            tabs.querySelectorAll('button').forEach(function (b) { b.classList.toggle('active', b.dataset.mode === mode); });
            try { localStorage.setItem('sams.docmode.' + location.search.replace(/.*type=([^&]+).*/, '$1'), mode); } catch (e) { /* ohne Speicher */ }
        };
        tabs.querySelectorAll('button').forEach(function (b) {
            b.addEventListener('click', function () { set(b.dataset.mode); });
        });
        try {
            var saved = localStorage.getItem('sams.docmode.' + location.search.replace(/.*type=([^&]+).*/, '$1'));
            if (saved) { set(saved); }
        } catch (e) { /* ohne Speicher */ }
    });
    document.querySelectorAll('input[name="sig_mode"]').forEach(function (r) {
        r.addEventListener('change', function () {
            var live = document.querySelector('.live-sig');
            if (live) { live.hidden = r.value !== 'live' || !r.checked; }
        });
    });
})();

// Rechnung: Preis aus der Gebührenordnung übernehmen, sobald eine Leistung gewählt wird
(function () {
    'use strict';
    document.addEventListener('change', function (e) {
        var input = e.target;
        if (!window.SAMS_PRICES || !input.name || !/\[text\]$/.test(input.name)) { return; }
        var price = window.SAMS_PRICES[input.value.trim()];
        var row = input.closest('tr');
        var priceInput = row && row.querySelector('input[name$="[price]"]');
        if (price === undefined || !priceInput) { return; }
        // Nur überschreiben, wenn leer oder zuvor automatisch gesetzt
        if (priceInput.value === '' || priceInput.dataset.auto === priceInput.value) {
            priceInput.value = price.toFixed(2);
            priceInput.dataset.auto = priceInput.value;
        }
    });
})();

// Laufende Dienstzeit in der Kopfleiste (vom Server gemessene Dauer, unabhängig von der Uhr des Geräts)
(function () {
    'use strict';
    var el = document.querySelector('.duty-toggle small[data-elapsed]');
    if (!el) { return; }
    var start = Date.now() - parseInt(el.dataset.elapsed, 10) * 1000;
    function tick() {
        var s = Math.max(0, Math.floor((Date.now() - start) / 1000));
        el.textContent = Math.floor(s / 3600) + ':' + ('0' + Math.floor(s % 3600 / 60)).slice(-2) + ' h';
    }
    tick();
    setInterval(tick, 30000);
})();

// Optionale Abmeldung bei Inaktivität: Aktivität hält die Sitzung per Lebenszeichen offen,
// nach Ablauf lädt die Seite neu (der Server meldet dann ab), damit keine Akte offen stehen bleibt.
(function () {
    'use strict';
    var minutes = parseInt(document.body.dataset.idle || '0', 10);
    if (!minutes) { return; }
    var last = Date.now(), pinged = Date.now();
    ['mousemove', 'keydown', 'click', 'touchstart', 'scroll'].forEach(function (ev) {
        document.addEventListener(ev, function () { last = Date.now(); }, { passive: true });
    });
    setInterval(function () {
        var now = Date.now();
        if (now - last > minutes * 60000 + 5000) {
            location.reload();
        } else if (last > pinged && now - pinged > 120000) {
            pinged = now;
            fetch('index.php?p=ping', { credentials: 'same-origin' }).catch(function () {});
        }
    }, 15000);
})();
