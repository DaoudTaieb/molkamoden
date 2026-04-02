self.addEventListener('install', function (event) {
  self.skipWaiting();
});

self.addEventListener('activate', function (event) {
  event.waitUntil(self.clients.claim());
});

self.addEventListener('fetch', function (event) {
  // Simple pass-through (aucun cache hors-ligne pour le moment)
  event.respondWith(fetch(event.request));
});
