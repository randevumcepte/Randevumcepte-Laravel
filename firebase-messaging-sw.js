/**
 * Firebase Cloud Messaging — Service Worker (tarayici arka plan push).
 *
 * URL: /firebase-messaging-sw.js  (kok dizinden servis edilir; nginx docroot = repo koku)
 * Kayit: resources/views/layout_isletmeadmin.blade.php icindeki registerFcm() bunu
 *        scope '/' ile register eder ve getToken(...) icin serviceWorkerRegistration olarak verir.
 *
 * SW'ler statik dosyadir; Blade/@json calistiramaz. Asagidaki config PUBLIC client
 * anahtarlaridir (config/firebase_web.php ile ayni; .env FIREBASE_* degerleri), gizli degildir.
 * Deger degisirse bu dosyayi da guncelle.
 *
 * Compat SDK surumu (10.13.2), layout'taki modul import surumuyle ayni tutulmalidir.
 */
importScripts('https://www.gstatic.com/firebasejs/10.13.2/firebase-app-compat.js');
importScripts('https://www.gstatic.com/firebasejs/10.13.2/firebase-messaging-compat.js');

firebase.initializeApp({
  apiKey:            'AIzaSyBmt8u6s7KiD5G78MHmSpjnF3LDghf53a8',
  authDomain:        'randevumcepte-uygulamala-5ff4d.firebaseapp.com',
  projectId:         'randevumcepte-uygulamala-5ff4d',
  storageBucket:     'randevumcepte-uygulamala-5ff4d.firebasestorage.app',
  messagingSenderId: '279726530132',
  appId:             '1:279726530132:web:d2e4baa55ffbc3839f57c1',
  measurementId:     'G-CYH5P07VVZ',
});

const messaging = firebase.messaging();

// Arka planda (sekme kapali/gizli) gelen bildirimler burada gosterilir.
// Sayfa acikken gelenler layout'taki onMessage() banner'i ile gosterilir.
messaging.onBackgroundMessage(function (payload) {
  const n    = payload.notification || {};
  const data = payload.data || {};
  const title = n.title || data.title || 'Randevumcepte';
  const options = {
    body:  n.body || data.body || '',
    icon:  n.icon || data.icon || '/public/img/logo.png',
    badge: '/public/img/logo.png',
    data:  { url: data.deep_link || data.url || data.click_action || '/isletmeyonetim' },
    tag:   data.tag || undefined,
  };
  return self.registration.showNotification(title, options);
});

// Bildirime tiklaninca ilgili sayfayi ac / var olan sekmeye odaklan.
self.addEventListener('notificationclick', function (event) {
  event.notification.close();
  const target = (event.notification.data && event.notification.data.url) || '/isletmeyonetim';
  event.waitUntil(
    clients.matchAll({ type: 'window', includeUncontrolled: true }).then(function (list) {
      for (const c of list) {
        if ('focus' in c) { c.navigate(target); return c.focus(); }
      }
      if (clients.openWindow) return clients.openWindow(target);
    })
  );
});
