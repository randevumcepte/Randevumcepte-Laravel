<?php

/**
 * Firebase Cloud Messaging icin servis hesap dosyalari.
 *
 * NotificationService::send() salon'un firebase_profile kolonuna bakar,
 * burada karsiligi olan path'i yukler. Profile bos veya tanimsizsa 'default'
 * kullanilir.
 *
 * Path'ler storage_path()'e gore goreli (storage/app/... altinda).
 * JSON dosyalarinin kendisi git'e commit edilmez (gitignore'da olmali).
 *
 * Yeni marka eklemek icin:
 *  1) Firebase Console'dan service account JSON indir
 *  2) storage/app/firebase/{profile}.json olarak koy
 *  3) Bu listeye yeni satir ekle
 *  4) Salon kayitinda firebase_profile alanini bu isme set et
 */
return [
    // Eski "uygulamala" projesi (5ff4d) - cogu marka burada
    'default'      => 'app/firebase/randevumcepte-uygulamala-5ff4d-8a85c43832c1.json',
    // Salooncadde markasi - randevumcepte-uygulamalar projesinde
    'salooncadde'  => 'app/firebase/salooncadde.json',
];
