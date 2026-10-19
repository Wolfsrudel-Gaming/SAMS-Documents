/* SAMS Tablet – NUI */
(function () {
    'use strict';
    var resource = typeof GetParentResourceName === 'function' ? GetParentResourceName() : 'sams_tablet';
    var $ = function (id) { return document.getElementById(id); };
    var state = { origin: null, user: null, frameUser: null, viewerOrigin: null };

    function nui(name, data) {
        return fetch('https://' + resource + '/' + name, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json; charset=UTF-8' },
            body: JSON.stringify(data || {})
        }).then(function (r) { return r.json(); }).catch(function () { return null; });
    }

    function show(id, on) { $(id).classList.toggle('hidden', !on); }

    // ------------------------------------------------------------- Tablet
    function showLogin(message) {
        show('frame', false); show('loading', false); show('login', true); show('btn-logout', false);
        $('who').textContent = '';
        $('code-row').classList.add('hidden');
        $('login-form').code.value = '';
        $('login-form').password.value = '';
        setError(message);
        setTimeout(function () { $('login-form').username.focus(); }, 50);
    }

    function setError(msg) {
        $('login-error').textContent = msg || '';
        show('login-error', !!msg);
    }

    function showSystem(url, origin, user) {
        state.origin = origin;
        state.user = user;
        $('who').textContent = user ? user.name + (user.rank ? ' · ' + user.rank : '') : '';
        show('login', false); show('btn-logout', true);
        var frame = $('frame');
        // Seite behalten, wenn das Tablet nur geschlossen und wieder geöffnet wurde
        if (frame.src && state.frameUser && user && state.frameUser === user.id) {
            show('loading', false); show('frame', true);
            return;
        }
        show('frame', false); show('loading', true);
        frame.onload = function () { show('loading', false); show('frame', true); };
        frame.src = url;
        state.frameUser = user ? user.id : null;
    }

    $('login-form').addEventListener('submit', function (e) {
        e.preventDefault();
        var f = e.target;
        $('login-btn').disabled = true;
        setError('');
        nui('login', { username: f.username.value.trim(), password: f.password.value, code: f.code.value.trim() }).then(function (res) {
            $('login-btn').disabled = false;
            if (!res) { return setError('Keine Antwort vom Server.'); }
            if (res.status === 'ok' && res.url) {
                state.frameUser = null;
                return showSystem(res.url, res.origin, res.user);
            }
            if (res.status === '2fa') {
                $('code-row').classList.remove('hidden');
                f.code.focus();
                if (f.code.value) { setError(res.message); }
                return;
            }
            setError(res.message || 'Anmeldung fehlgeschlagen.');
        });
    });

    $('btn-close').addEventListener('click', function () { nui('closeTablet'); });
    $('btn-logout').addEventListener('click', function () {
        nui('logout').then(function () { $('frame').src = 'about:blank'; state.frameUser = null; showLogin(); });
    });

    // Nachrichten aus dem eingebetteten Websystem (tablet-bridge.js)
    window.addEventListener('message', function (e) {
        var msg = e.data;
        if (!msg || typeof msg.sams !== 'string') { return; }
        if (msg.sams === 'closeDocument' && e.origin === state.viewerOrigin) { nui('closeDocument'); return; }
        if ((msg.sams === 'forgeDone' || msg.sams === 'forgeCancel') && state.forgeOrigin && e.origin === state.forgeOrigin) { nui(msg.sams); return; }
        if (e.origin !== state.origin) { return; }
        if (msg.sams === 'closeTablet') { nui('closeTablet'); return; }
        var frame = $('frame').contentWindow;
        if (msg.sams === 'getCoords') {
            nui('getCoords').then(function (c) { frame.postMessage({ sams: 'reply', reply: msg.id, data: c }, state.origin); });
        } else if (msg.sams === 'printQueued') {
            nui('printQueued', msg.data);
        } else if (msg.sams === 'logout') {
            nui('webLogout');
            $('frame').src = 'about:blank';
            state.frameUser = null;
            showLogin('Du wurdest abgemeldet.');
        }
    });

    // --------------------------------------------------------- Klemmbrett
    // Unterschriftsleiste: offene Felder unterschreiben, Abschließen (nur wer das Blatt besitzt)
    function renderSigning(sig, canFinalize, revoked) {
        var bar = $('v-sign');
        bar.innerHTML = '';
        if (!sig || !sig.fields || !sig.fields.length || revoked) { show('v-sign', false); return; }
        var add = function (el) { bar.appendChild(el); return el; };
        var mk = function (tag, cls, text) { var el = document.createElement(tag); if (cls) { el.className = cls; } el.textContent = text; return el; };
        if (sig.final) {
            add(mk('span', 'final', '✔ Abgeschlossen'));
        }
        sig.fields.forEach(function (f) {
            if (f.signed) {
                add(mk('span', 'done', '✔ ' + f.label + (f.by ? ' – ' + f.by : '')));
            } else if (!sig.final) {
                var b = add(mk('button', 'tb-btn sign', '✍ ' + f.label));
                b.addEventListener('click', function () { openPad(f); });
            }
        });
        if (!sig.final && canFinalize) {
            var fin = add(mk('button', 'tb-btn', 'Abschließen'));
            // zweiter Klick bestätigt (Dialoge wie confirm() gibt es im Spiel nicht)
            fin.addEventListener('click', function () {
                if (!fin.dataset.sure) { fin.dataset.sure = '1'; fin.textContent = 'Wirklich abschließen? Nochmal klicken'; return; }
                nui('finalizeDocument').then(function (res) { if (res && res.error) { fin.textContent = res.error; } else { afterSign(res); } });
            });
        }
        show('v-sign', true);
    }
    function afterSign(res) {
        if (!res || res.error) { $('sig-error').textContent = (res && res.error) || 'Fehler'; show('sig-error', true); return false; }
        show('sigmodal', false);
        state.viewer.signing = res.signing;
        renderSigning(res.signing, state.viewer.canFinalize, false);
        // Blatt neu laden, damit die Unterschrift sichtbar wird
        var fr = $('v-frame'); fr.src = state.viewer.url + (state.viewer.url.indexOf('?') < 0 ? '?' : '&') + 'r=' + Date.now();
        return true;
    }

    // Unterschriften-Pad
    var pad = { canvas: $('sig-canvas'), field: null, dirty: false, drawing: false, last: null };
    pad.ctx = pad.canvas.getContext('2d');
    function padClear() {
        pad.ctx.clearRect(0, 0, pad.canvas.width, pad.canvas.height);
        pad.dirty = false;
    }
    function padPos(e) {
        var r = pad.canvas.getBoundingClientRect();
        return { x: (e.clientX - r.left) * pad.canvas.width / r.width, y: (e.clientY - r.top) * pad.canvas.height / r.height };
    }
    pad.canvas.addEventListener('pointerdown', function (e) { pad.drawing = true; pad.last = padPos(e); pad.canvas.setPointerCapture(e.pointerId); });
    pad.canvas.addEventListener('pointermove', function (e) {
        if (!pad.drawing) { return; }
        var p = padPos(e), c = pad.ctx;
        c.strokeStyle = '#1b2d78'; c.lineWidth = 2.6; c.lineCap = 'round'; c.lineJoin = 'round';
        c.beginPath(); c.moveTo(pad.last.x, pad.last.y); c.lineTo(p.x, p.y); c.stroke();
        pad.last = p; pad.dirty = true;
    });
    ['pointerup', 'pointerleave', 'pointercancel'].forEach(function (ev) { pad.canvas.addEventListener(ev, function () { pad.drawing = false; }); });
    /** Auf die Unterschrift zuschneiden und klein halten (Netzwerk-Ereignisse im Spiel sind begrenzt). */
    function padExport() {
        var c = pad.canvas, ctx = pad.ctx, img = ctx.getImageData(0, 0, c.width, c.height).data;
        var minX = c.width, minY = c.height, maxX = 0, maxY = 0;
        for (var y = 0; y < c.height; y++) { for (var x = 0; x < c.width; x++) {
            if (img[(y * c.width + x) * 4 + 3] > 10) { if (x < minX) { minX = x; } if (y < minY) { minY = y; } if (x > maxX) { maxX = x; } if (y > maxY) { maxY = y; } }
        } }
        if (maxX <= minX || maxY <= minY) { return ''; }
        var w = maxX - minX + 8, h = maxY - minY + 8, scale = 1, out;
        do {
            var t = document.createElement('canvas');
            t.width = Math.max(1, Math.round(w * scale)); t.height = Math.max(1, Math.round(h * scale));
            t.getContext('2d').drawImage(c, minX - 4, minY - 4, w, h, 0, 0, t.width, t.height);
            out = t.toDataURL('image/png');
            scale *= 0.75;
        } while (out.length > 60000 && scale > 0.2);
        return out;
    }
    function openPad(f) {
        pad.field = f.name;
        $('sig-title').textContent = f.label;
        show('sig-error', false);
        padClear();
        show('sigmodal', true);
    }
    $('sig-clear').addEventListener('click', padClear);
    $('sig-cancel').addEventListener('click', function () { show('sigmodal', false); });
    $('sig-ok').addEventListener('click', function () {
        if (!pad.dirty) { $('sig-error').textContent = 'Bitte zuerst unterschreiben.'; show('sig-error', true); return; }
        var png = padExport();
        if (!png) { return; }
        $('sig-ok').disabled = true;
        nui('signDocument', { field: pad.field, signature: png }).then(function (res) { $('sig-ok').disabled = false; afterSign(res); });
    });

    function openViewer(d) {
        state.viewer = { url: d.url, signing: d.signing, canFinalize: !!d.canFinalize };
        renderSigning(d.signing, !!d.canFinalize, !!d.revoked);
        // Karten (Dienstausweis) ohne Klemmbrett anzeigen
        $('viewer').classList.toggle('card-mode', !!d.card);
        $('v-label').textContent = d.label || 'Dokument';
        $('v-from').textContent = d.from ? 'gezeigt von ' + d.from : '';
        show('v-revoked', !!d.revoked);
        show('v-show', !!d.canShow);
        try { state.viewerOrigin = new URL(d.url).origin; } catch (err) { state.viewerOrigin = null; }
        $('v-frame').src = d.url;
        show('viewer', true);
    }
    function closeViewer() {
        show('viewer', false);
        show('sigmodal', false);
        $('v-frame').src = 'about:blank';
    }
    $('v-close').addEventListener('click', function () { nui('closeDocument'); });
    $('v-show').addEventListener('click', function () { nui('showDocument'); });

    // ------------------------------------------------------- Lua → NUI
    window.addEventListener('message', function (e) {
        var d = e.data;
        if (!d || !d.action) { return; }
        switch (d.action) {
            case 'openTablet':
                show('tablet', true);
                if (d.loggedIn && d.url) { showSystem(d.url, d.origin, d.user); } else { showLogin(); }
                break;
            case 'closeTablet':
                show('tablet', false);
                break;
            case 'viewDocument':
                openViewer(d);
                break;
            case 'closeDocument':
                closeViewer();
                break;
            case 'openForge':
                state.forgeOrigin = d.origin;
                $('forge-frame').src = d.url;
                show('forge', true);
                break;
            case 'closeForge':
                show('forge', false);
                $('forge-frame').src = 'about:blank';
                state.forgeOrigin = null;
                break;
        }
    });

    document.addEventListener('keydown', function (e) {
        if (e.key !== 'Escape') { return; }
        if (!$('forge').classList.contains('hidden')) { nui('forgeCancel'); }
        else if (!$('sigmodal').classList.contains('hidden')) { show('sigmodal', false); }
        else if (!$('viewer').classList.contains('hidden')) { nui('closeDocument'); }
        else if (!$('tablet').classList.contains('hidden')) { nui('closeTablet'); }
    });

    setInterval(function () {
        var d = new Date();
        $('clock').textContent = ('0' + d.getHours()).slice(-2) + ':' + ('0' + d.getMinutes()).slice(-2);
    }, 10000);
})();
