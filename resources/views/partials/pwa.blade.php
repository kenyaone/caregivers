{{-- Install banner + shift-alert opt-in. Plain styles so it does not depend on the compiled CSS. --}}
@auth
<div id="noc-app-bar" hidden style="position:fixed;left:12px;right:12px;bottom:12px;z-index:60;max-width:560px;margin:0 auto;background:#0f766e;color:#fff;border-radius:16px;box-shadow:0 10px 30px rgba(15,23,42,.25);padding:14px 16px;font-size:14px;line-height:1.4">
    <div style="display:flex;gap:12px;align-items:center">
        <img src="/icons/icon-192.png" alt="" style="width:40px;height:40px;border-radius:10px;flex:none;box-shadow:0 0 0 2px rgba(255,255,255,.4)">
        <div style="flex:1;min-width:0">
            <div id="noc-app-title" style="font-weight:700"></div>
            <div id="noc-app-text" style="opacity:.9"></div>
        </div>
        <button id="noc-app-go" type="button" style="flex:none;background:#facc15;color:#1e293b;border:0;border-radius:10px;padding:9px 14px;font-weight:700;font-size:14px"></button>
        <button id="noc-app-x" type="button" aria-label="Not now" style="flex:none;background:transparent;color:#fff;border:0;font-size:22px;line-height:1;padding:4px 6px;opacity:.8">&times;</button>
    </div>
</div>
@endauth
<script>
(function () {
    if (!('serviceWorker' in navigator)) return;
    var vapid = @json(config('webpush.public_key'));
    var loggedIn = @json(auth()->check());
    var csrf = @json(csrf_token());
    var subUrl = @json(route('push.subscribe'));
    var standalone = window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true;
    var ios = /iphone|ipad|ipod/i.test(navigator.userAgent);
    var store = { get: function (k) { try { return localStorage.getItem(k); } catch (e) { return null; } },
                  set: function (k, v) { try { localStorage.setItem(k, v); } catch (e) {} } };
    var snoozed = function (k) { var t = +store.get(k) || 0; return Date.now() < t; };
    var snooze = function (k, days) { store.set(k, String(Date.now() + days * 864e5)); };

    var bar = document.getElementById('noc-app-bar'), deferred = null, mode = null;
    function show(m, title, text, btn) {
        if (!bar) return;
        mode = m;
        document.getElementById('noc-app-title').textContent = title;
        document.getElementById('noc-app-text').textContent = text;
        var go = document.getElementById('noc-app-go');
        go.textContent = btn || ''; go.style.display = btn ? '' : 'none';
        bar.hidden = false;
    }
    function hide() { if (bar) bar.hidden = true; }

    function b64ToBytes(s) {
        var p = '='.repeat((4 - s.length % 4) % 4), b = atob((s + p).replace(/-/g, '+').replace(/_/g, '/'));
        var a = new Uint8Array(b.length); for (var i = 0; i < b.length; i++) a[i] = b.charCodeAt(i); return a;
    }
    function saveSub(sub) {
        return fetch(subUrl, { method: 'POST', credentials: 'same-origin',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
            body: JSON.stringify(sub.toJSON()) });
    }
    function subscribe(reg) {
        return reg.pushManager.getSubscription().then(function (s) {
            return s || reg.pushManager.subscribe({ userVisibleOnly: true, applicationServerKey: b64ToBytes(vapid) });
        }).then(saveSub);
    }

    navigator.serviceWorker.register('/sw.js').then(function (reg) {
        if (!loggedIn || !vapid || !('PushManager' in window) || !('Notification' in window)) return;
        if (Notification.permission === 'granted') { subscribe(reg).catch(function () {}); return; }
        // Ask for alerts only from a tap (browsers block unprompted requests), and not more than once a week.
        if (Notification.permission === 'default' && !snoozed('noc-push-snooze') && (standalone || !deferred)) {
            setTimeout(function () {
                if (mode) return;
                show('push', 'Get Care alerts on this phone', 'New shifts, bookings and approvals pop up even when the app is closed.', 'Turn on');
            }, 1500);
        }
        window.__nocReg = reg;
    }).catch(function () {});

    window.addEventListener('beforeinstallprompt', function (e) {
        deferred = e;
        if (bar && loggedIn && !standalone && !snoozed('noc-install-snooze')) {
            e.preventDefault(); // show our bar instead of Chrome's; guests keep Chrome's own install prompt
            show('install', 'Install CareGivers254', 'Open it from your home screen like an app. Free, under 1 MB.', 'Install');
        }
    });
    window.addEventListener('appinstalled', function () { hide(); deferred = null; });

    if (loggedIn && ios && !standalone && !snoozed('noc-install-snooze')) {
        setTimeout(function () {
            if (!mode) show('ios', 'Install on your iPhone', 'Tap the Share button, then "Add to Home Screen". Alerts work after that.', '');
        }, 1500);
    }

    if (bar) {
        document.getElementById('noc-app-go').addEventListener('click', function () {
            if (mode === 'install' && deferred) {
                deferred.prompt();
                deferred.userChoice.finally(function () { deferred = null; hide(); });
            } else if (mode === 'push' && window.__nocReg) {
                Notification.requestPermission().then(function (p) {
                    if (p === 'granted') return subscribe(window.__nocReg).then(function () {
                        show('done', 'Care alerts are on', 'You will get alerts on this phone.', ''); setTimeout(hide, 3000);
                    });
                    snooze('noc-push-snooze', 30); hide();
                }).catch(function () { hide(); });
            }
        });
        document.getElementById('noc-app-x').addEventListener('click', function () {
            snooze(mode === 'push' ? 'noc-push-snooze' : 'noc-install-snooze', 7); hide();
        });
    }
})();
</script>