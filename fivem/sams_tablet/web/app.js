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
    function openViewer(d) {
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
        }
    });

    document.addEventListener('keydown', function (e) {
        if (e.key !== 'Escape') { return; }
        if (!$('viewer').classList.contains('hidden')) { nui('closeDocument'); }
        else if (!$('tablet').classList.contains('hidden')) { nui('closeTablet'); }
    });

    setInterval(function () {
        var d = new Date();
        $('clock').textContent = ('0' + d.getHours()).slice(-2) + ':' + ('0' + d.getMinutes()).slice(-2);
    }, 10000);
})();
