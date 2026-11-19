{{-- App install (prominent, for every visitor) + phone alerts opt-in (signed-in users). Plain styles: no dependency on compiled CSS. --}}
<style>
.cgi-only{display:none}
html.cgi-can .cgi-only{display:inline-flex}
html.cgi-can .cgi-only.cgi-block{display:block}
html.cgi-installed .cgi-only{display:none!important}
@media (display-mode: standalone){.cgi-only{display:none!important}}
.cgi-btn{align-items:center;gap:.45rem;border:0;cursor:pointer;font-weight:800;border-radius:.8rem;white-space:nowrap}
.cgi-pill{background:#fbbf24;color:#1c1917;padding:.45rem .8rem;font-size:.8rem;box-shadow:0 6px 18px -8px rgba(251,191,36,.9)}
.cgi-pill:hover{background:#f59e0b}
.cgi-pulse{animation:cgipulse 2.2s infinite}
@keyframes cgipulse{0%{box-shadow:0 0 0 0 rgba(251,191,36,.7)}70%{box-shadow:0 0 0 12px rgba(251,191,36,0)}100%{box-shadow:0 0 0 0 rgba(251,191,36,0)}}
#cgi-bar{position:fixed;left:12px;right:12px;bottom:12px;z-index:60;max-width:580px;margin:0 auto;background:linear-gradient(135deg,#042f2e,#0f766e);color:#fff;border-radius:18px;box-shadow:0 18px 40px -10px rgba(15,23,42,.55);padding:14px 16px;font-size:14px;line-height:1.4;transform:translateY(140%);transition:transform .35s ease}
#cgi-bar.on{transform:none}
#cgi-modal{position:fixed;inset:0;z-index:70;background:rgba(15,23,42,.6);display:none;align-items:flex-end;justify-content:center;padding:12px}
#cgi-modal.on{display:flex}
@media(min-width:640px){#cgi-modal{align-items:center}}
#cgi-modal .box{background:#fff;color:#0f172a;border-radius:20px;max-width:440px;width:100%;padding:22px;box-shadow:0 30px 60px -20px rgba(0,0,0,.5)}
#cgi-modal ol{margin:14px 0 0;padding:0;list-style:none;display:grid;gap:12px;counter-reset:s}
#cgi-modal li{counter-increment:s;display:flex;gap:12px;align-items:flex-start;font-size:15px;line-height:1.45}
#cgi-modal li:before{content:counter(s);flex:none;display:grid;place-items:center;width:28px;height:28px;border-radius:9px;background:#ccfbf1;color:#0f766e;font-weight:900;font-size:14px}
</style>

<div id="cgi-bar" role="dialog" aria-live="polite" hidden>
    <div style="display:flex;gap:12px;align-items:center">
        <img src="/icons/icon-192.png" alt="" style="width:46px;height:46px;border-radius:12px;flex:none;box-shadow:0 0 0 2px rgba(255,255,255,.35)">
        <div style="flex:1;min-width:0">
            <div id="cgi-title" style="font-weight:800;font-size:15px"></div>
            <div id="cgi-text" style="opacity:.9;font-size:13px"></div>
        </div>
        <button id="cgi-go" type="button" class="cgi-btn cgi-pill cgi-pulse" style="display:inline-flex;padding:10px 16px;font-size:14px"></button>
        <button id="cgi-x" type="button" aria-label="Not now" style="flex:none;background:transparent;color:#fff;border:0;font-size:24px;line-height:1;padding:4px 6px;opacity:.75;cursor:pointer">&times;</button>
    </div>
</div>

<div id="cgi-modal" role="dialog" aria-modal="true" aria-labelledby="cgi-mt">
    <div class="box">
        <div style="display:flex;gap:12px;align-items:center">
            <img src="/icons/icon-192.png" alt="" style="width:48px;height:48px;border-radius:12px">
            <div>
                <div id="cgi-mt" style="font-weight:900;font-size:18px">Install CareGivers254</div>
                <div style="font-size:13px;color:#64748b">Free · under 1 MB · opens from your home screen</div>
            </div>
        </div>
        <ol id="cgi-steps"></ol>
        <button type="button" id="cgi-close" class="cgi-btn" style="display:flex;justify-content:center;width:100%;margin-top:18px;background:#0f766e;color:#fff;padding:12px;font-size:15px">Got it</button>
    </div>
</div>

<script>
(function () {
    var html = document.documentElement;
    var standalone = window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true;
    if (standalone) html.classList.add('cgi-installed');
    var ua = navigator.userAgent;
    var ios = /iphone|ipad|ipod/i.test(ua) || (navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1);
    var android = /android/i.test(ua);
    var store = { get: function (k) { try { return localStorage.getItem(k); } catch (e) { return null; } },
                  set: function (k, v) { try { localStorage.setItem(k, v); } catch (e) {} } };
    var snoozed = function (k) { return Date.now() < (+store.get(k) || 0); };
    var snooze = function (k, days) { store.set(k, String(Date.now() + days * 864e5)); };

    // Install buttons are visible for everyone not already using the app.
    if (!standalone) html.classList.add('cgi-can');

    var bar = document.getElementById('cgi-bar'), modal = document.getElementById('cgi-modal');
    var deferred = null, mode = null;

    function show(m, title, text, btn) {
        mode = m;
        document.getElementById('cgi-title').textContent = title;
        document.getElementById('cgi-text').textContent = text;
        var go = document.getElementById('cgi-go');
        go.textContent = btn; go.style.display = btn ? 'inline-flex' : 'none';
        bar.hidden = false;
        requestAnimationFrame(function () { bar.classList.add('on'); });
    }
    function hide() { bar.classList.remove('on'); mode = null; setTimeout(function () { if (!mode) bar.hidden = true; }, 400); }

    function steps() {
        var s;
        if (ios) s = ['Tap the <b>Share</b> button <span style="font-size:18px">&#x2B06;&#xFE0E;</span> at the bottom (or top) of Safari.',
                      'Scroll down and tap <b>Add to Home Screen</b>.',
                      'Tap <b>Add</b>. CareGivers254 now opens like an app.'];
        else if (android) s = ['Tap the <b>&#8942;</b> menu at the top right of Chrome.',
                      'Tap <b>Install app</b> (or <b>Add to Home screen</b>).',
                      'Tap <b>Install</b>. The CareGivers254 icon appears on your phone.'];
        else s = ['Click the <b>install icon</b> at the right end of the address bar (a screen with an arrow), or open the browser menu.',
                  'Choose <b>Install CareGivers254</b>.',
                  'It opens in its own window, and you can pin it to your taskbar.'];
        document.getElementById('cgi-steps').innerHTML = '<li><span>' + s.join('</span></li><li><span>') + '</span></li>';
        modal.classList.add('on');
    }
    document.getElementById('cgi-close').addEventListener('click', function () { modal.classList.remove('on'); });
    modal.addEventListener('click', function (e) { if (e.target === modal) modal.classList.remove('on'); });

    function install() {
        if (deferred) {
            var d = deferred; deferred = null;
            d.prompt();
            d.userChoice.then(function (c) { if (c.outcome === 'accepted') { html.classList.add('cgi-installed'); hide(); } });
        } else {
            steps();
        }
    }
    window.cgInstall = install;
    document.addEventListener('click', function (e) {
        var t = e.target.closest && e.target.closest('[data-cg-install]');
        if (t) { e.preventDefault(); install(); }
    });

    function offerInstall() {
        if (standalone || mode || snoozed('cgi-install-snooze')) return;
        show('install', 'Get the CareGivers254 app', 'One tap from your home screen. Free, fast, and alerts you about new care requests.', 'Install');
    }

    window.addEventListener('beforeinstallprompt', function (e) {
        e.preventDefault(); // use our own, more visible buttons
        deferred = e;
        setTimeout(offerInstall, 2500);
    });
    window.addEventListener('appinstalled', function () { deferred = null; html.classList.add('cgi-installed'); hide(); });
    // Browsers that never fire beforeinstallprompt (iPhone, Firefox, Samsung sometimes): still offer, with instructions.
    if (!standalone && (ios || android)) setTimeout(function () { if (!deferred) offerInstall(); }, 4000);

    document.getElementById('cgi-x').addEventListener('click', function () {
        snooze(mode === 'push' ? 'cgi-push-snooze' : 'cgi-install-snooze', mode === 'push' ? 7 : 2);
        hide();
    });

    if (!('serviceWorker' in navigator)) {
        document.getElementById('cgi-go').addEventListener('click', install);
        return;
    }

    // ---- Phone alerts (web push) for signed-in users ----
    var vapid = @json(config('webpush.public_key'));
    var loggedIn = @json(auth()->check());
    var csrf = @json(csrf_token());
    var subUrl = @json(route('push.subscribe'));
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

    document.getElementById('cgi-go').addEventListener('click', function () {
        if (mode === 'install') { install(); return; }
        if (mode === 'push' && window.__cgReg) {
            Notification.requestPermission().then(function (p) {
                hide();
                if (p === 'granted') subscribe(window.__cgReg).catch(function () {});
                else snooze('cgi-push-snooze', 30);
            });
        }
    });

    navigator.serviceWorker.register('/sw.js').then(function (reg) {
        window.__cgReg = reg;
        if (!loggedIn || !vapid || !('PushManager' in window) || !('Notification' in window)) return;
        if (Notification.permission === 'granted') { subscribe(reg).catch(function () {}); return; }
        if (Notification.permission === 'default' && !snoozed('cgi-push-snooze') && (standalone || snoozed('cgi-install-snooze'))) {
            setTimeout(function () {
                if (mode) return;
                show('push', 'Turn on care alerts', 'New care requests, job matches and contact unlocks pop up even when the app is closed.', 'Turn on');
            }, 1500);
        }
    }).catch(function () {});
})();
</script>