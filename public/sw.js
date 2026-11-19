/* CareGivers254 service worker */
var VERSION = 'care-v1';
var STATIC_CACHE = VERSION + '-static';
var PAGE_CACHE = VERSION + '-pages';
var PRECACHE = ['/offline.html', '/css/app.css', '/favicon.svg', '/icons/icon-192.png', '/icons/icon-512.png', '/manifest.webmanifest'];

self.addEventListener('install', function (event) {
  event.waitUntil(caches.open(STATIC_CACHE).then(function (c) { return c.addAll(PRECACHE); }).then(function () { return self.skipWaiting(); }));
});

self.addEventListener('activate', function (event) {
  event.waitUntil(caches.keys().then(function (keys) {
    return Promise.all(keys.filter(function (k) { return k.indexOf(VERSION) !== 0; }).map(function (k) { return caches.delete(k); }));
  }).then(function () { return self.clients.claim(); }));
});

self.addEventListener('fetch', function (event) {
  var req = event.request;
  if (req.method !== 'GET') return;
  var url = new URL(req.url);
  if (url.origin !== self.location.origin) return;
  if (url.pathname.indexOf('/documents/') === 0 || url.pathname.indexOf('/webhooks/') === 0) return;

  // Static assets: cache first
  if (/\.(css|js|png|jpg|jpeg|svg|webp|woff2?)$/.test(url.pathname)) {
    event.respondWith(caches.match(req).then(function (hit) {
      return hit || fetch(req).then(function (res) {
        if (res.ok) { var copy = res.clone(); caches.open(STATIC_CACHE).then(function (c) { c.put(req, copy); }); }
        return res;
      });
    }));
    return;
  }

  // Pages: network first; public pages fall back to the last cached copy, then the offline page
  if (req.mode === 'navigate') {
    var cacheable = url.pathname === '/' || url.pathname === '/pricing' || url.pathname.indexOf('/caregivers') === 0;
    event.respondWith(fetch(req).then(function (res) {
      if (cacheable && res.ok) { var copy = res.clone(); caches.open(PAGE_CACHE).then(function (c) { c.put(req, copy); }); }
      return res;
    }).catch(function () {
      return caches.match(req).then(function (hit) { return hit || caches.match('/offline.html'); });
    }));
  }
});

// Push notifications (payload from PlatformNotice::toWebPush: {title, body, url})
self.addEventListener('push', function (event) {
  var data = {};
  try { data = event.data ? event.data.json() : {}; } catch (e) { data = {body: event.data ? event.data.text() : ''}; }
  var title = data.title || 'CareGivers254';
  event.waitUntil(self.registration.showNotification(title, {
    body: data.body || '',
    icon: '/icons/icon-192.png',
    badge: '/icons/badge-96.png',
    data: {url: data.url || '/dashboard'},
    tag: data.tag || undefined,
    renotify: !!data.tag
  }));
});

self.addEventListener('notificationclick', function (event) {
  event.notification.close();
  var target = (event.notification.data && event.notification.data.url) || '/dashboard';
  event.waitUntil(self.clients.matchAll({type: 'window', includeUncontrolled: true}).then(function (list) {
    for (var i = 0; i < list.length; i++) {
      var c = list[i];
      if (new URL(c.url).origin === self.location.origin && 'focus' in c) {
        return c.focus().then(function (w) { return w && 'navigate' in w ? w.navigate(target) : w; });
      }
    }
    return self.clients.openWindow(target);
  }));
});
