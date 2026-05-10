"""
Salonappy scraper - 403 / network drop dayanikli, TEK DOSYA.

Telefon hotspot kullaniliyorsa: 403 alindiginda script pause olur,
kullaniciyi uyarir, ucak modu aç/kapat sonrasi Enter'a basildiginda
kaldigi yerden devam eder.

Progress (musteriIndex, ziyaret edilen url'ler) salonappy_progress.json
dosyasinda tutulur; script crash olsa bile yeniden baslattiğinizda
kaldigi yerden devam eder.
"""

from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
from selenium.common.exceptions import TimeoutException, WebDriverException
import time
import requests
import json
import os
import sys
from datetime import datetime, timedelta
import locale

# ============================================================
# AYARLAR
# ============================================================
PROGRESS_FILE = "salonappy_progress.json"
ISLETME_ID = 368
USERNAME = "5070373742"
PASSWORD = "220787"
API_BASE = "https://app.randevumcepte.com.tr"

# Manuel baslangic — None ise progress dosyasindan okur.
# 6 yazarsaniz: 1..6 atlanir, 7'den baslanir.
MANUAL_START_FROM = None

BLOCK_MARKERS = [
    "Access Denied",
    "Erişim Engellendi",
    "Access denied - Erişim engellendi",
    "Please make sure that you are not using a VPN",
    "salonAppy sistemine erişiminiz engellenmiştir",
]
MAX_RETRY = 3
RETRY_BACKOFF = 5

aylar = {
    "Ocak": "01", "Şubat": "02", "Mart": "03", "Nisan": "04",
    "Mayıs": "05", "Haziran": "06", "Temmuz": "07", "Ağustos": "08",
    "Eylül": "09", "Ekim": "10", "Kasım": "11", "Aralık": "12",
}


# ============================================================
# PROGRESS
# ============================================================
def load_progress():
    if os.path.exists(PROGRESS_FILE):
        try:
            with open(PROGRESS_FILE, "r", encoding="utf-8") as f:
                return json.load(f)
        except Exception:
            pass
    return {"musteriIndex": 0, "ziyaretEdilen": []}


def save_progress(progress):
    try:
        with open(PROGRESS_FILE, "w", encoding="utf-8") as f:
            json.dump(progress, f, ensure_ascii=False)
    except Exception as e:
        print(f"[WARN] Progress kaydedilemedi: {e}")


# ============================================================
# BLOCK / NETWORK
# ============================================================
def is_blocked(driver) -> bool:
    try:
        src = driver.page_source or ""
    except Exception:
        return False
    for m in BLOCK_MARKERS:
        if m in src:
            return True
    return False


def is_network_down() -> bool:
    try:
        requests.head("https://www.google.com", timeout=5)
        return False
    except Exception:
        return True


def wait_for_user(reason: str):
    print("\n" + "=" * 60)
    print(f"⚠️  DURAKLATILDI: {reason}")
    print("=" * 60)
    print("Yapilacaklar:")
    print("  1) Telefonu UÇAK MODU'na al")
    print("  2) Birkac saniye bekle")
    print("  3) UÇAK MODU'nu kapat (yeni IP alir)")
    print("  4) Network bağlantısı geri geldiğinde Enter'a bas")
    print("=" * 60)
    while True:
        input("Hazır olduğunda Enter'a bas... ")
        if is_network_down():
            print("❌ Network hala kapali. Bağlantıyı kontrol et.")
            continue
        print("✓ Network OK, devam ediliyor.")
        return


def safe_driver_get(driver, url: str):
    for attempt in range(1, MAX_RETRY + 1):
        try:
            driver.get(url)
            time.sleep(2)
            if is_blocked(driver):
                wait_for_user(f"Sayfa bloklu (403). URL: {url}")
                continue
            return True
        except WebDriverException as e:
            print(f"[WARN] driver.get hata (deneme {attempt}): {e}")
            if is_network_down():
                wait_for_user("Network koptu (selenium hata aldi).")
            else:
                time.sleep(RETRY_BACKOFF)
    return False


def safe_post(url: str, json_data: dict):
    for attempt in range(1, MAX_RETRY + 1):
        try:
            return requests.post(url, json=json_data, timeout=30)
        except requests.exceptions.RequestException as e:
            print(f"[WARN] POST hata (deneme {attempt}): {e}")
            if is_network_down():
                wait_for_user("Network koptu (API POST sirasinda).")
            else:
                time.sleep(RETRY_BACKOFF)
    raise RuntimeError(f"POST {url} {MAX_RETRY} denemede basarisiz.")


def son_bir_yil_icinde_mi(tarih_str: str) -> bool:
    try:
        gun, ay_str, yil = tarih_str.split()
        ay = int(aylar[ay_str])
        tarih = datetime(int(yil), ay, int(gun))
        return tarih >= datetime.now() - timedelta(days=730)
    except Exception:
        return False


# ============================================================
# MAIN
# ============================================================
try:
    locale.setlocale(locale.LC_TIME, "tr_TR.UTF-8")
except locale.Error:
    pass

progress = load_progress()
if MANUAL_START_FROM is not None:
    progress["musteriIndex"] = MANUAL_START_FROM
    print(f"📂 Manuel baslangic: musteriIndex={MANUAL_START_FROM} (1..{MANUAL_START_FROM} atlanir, {MANUAL_START_FROM + 1}'den devam)")
else:
    print(f"📂 Progress: musteriIndex={progress.get('musteriIndex', 0)}")
skip_until = progress.get("musteriIndex", 0)
ziyaretEdilenDetaylar = list(progress.get("ziyaretEdilen", []))

driver = webdriver.Chrome()
if not safe_driver_get(driver, "https://webapp.salonappy.com/#/login"):
    print("Login sayfasi yuklenemedi.")
    sys.exit(1)

wait = WebDriverWait(driver, 20)

# LOGIN
telefon = wait.until(EC.element_to_be_clickable((By.CSS_SELECTOR, "div.phone-input-col input")))
telefon.click()
telefon.send_keys(USERNAME)

sifre = wait.until(EC.element_to_be_clickable((By.CSS_SELECTOR, "input[name='password']")))
sifre.click()
sifre.send_keys(PASSWORD)
time.sleep(1)

login_button = wait.until(EC.element_to_be_clickable((By.CSS_SELECTOR, "div.buttons button")))
login_button.click()
time.sleep(3)

# MUSTERI LISTESI
if not safe_driver_get(driver, "https://webapp.salonappy.com/#/client/list"):
    sys.exit(1)
time.sleep(10)

musteriKartLinkleri = []
musteriIndex = 0

while True:
    if is_blocked(driver):
        wait_for_user("Müşteri listesi bloklu (403).")
        driver.refresh()
        time.sleep(5)
        continue

    musteriRows = driver.find_elements(By.XPATH, '//table[contains(@class, "p-datatable-table")]/tbody/tr')

    for row in musteriRows:
        musteriIndex = musteriIndex + 1
        cells = row.find_elements(By.TAG_NAME, "td")
        print(str(len(cells)))
        if len(cells) > 10:
            try:
                link_element = cells[10].find_element(By.TAG_NAME, "a")
                href_value = link_element.get_attribute("href")
                print("Link:", href_value)
                musteriKartLinkleri.append(href_value)

                # Skip mantigi: progress'teki son indeksi pas gec
                if musteriIndex <= skip_until:
                    continue

                ziyaretEdilenDetaylar.append(href_value)
                progress["musteriIndex"] = musteriIndex
                progress["ziyaretEdilen"] = ziyaretEdilenDetaylar
                save_progress(progress)

                driver.execute_script("window.open(arguments[0], '_blank');", href_value)
                driver.switch_to.window(driver.window_handles[1])

                try:
                    time.sleep(5)
                    if is_blocked(driver):
                        wait_for_user("Müşteri detay sayfası bloklu.")

                    detayBilgileri = driver.find_elements(By.XPATH, "//div[contains(@class,'p-col-8')]")
                    print(f"✅ Ad Soyad :"+detayBilgileri[1].text)
                    print(f"✅ Cep Telefon  :"+detayBilgileri[2].text)
                    print(f"✅ E-posta  :"+detayBilgileri[3].text)
                    print(f"✅ Doğum tarihi :"+detayBilgileri[4].text)
                    print(f"✅ Cinsiyet :"+detayBilgileri[5].text)
                    print(f"✅ Notlar :"+detayBilgileri[6].text)

                    musteriData = {
                        "musteriAdi": detayBilgileri[1].text,
                        "telefon": detayBilgileri[2].text,
                        "ePosta": detayBilgileri[3].text,
                        "dogumTarihi": detayBilgileri[4].text,
                        "cinsiyet": detayBilgileri[5].text,
                        "notlar": detayBilgileri[6].text,
                        "medeniDurum": "",
                        "kayitTarihi": "",
                        "meslek": "",
                        "adres": "",
                        "salonId": ISLETME_ID,
                    }
                    musteriId = safe_post(f"{API_BASE}/api/v1/aktarimMusteriKontrol", musteriData)
                    print("Müşteri Kaydedildi veya Güncellendi : "+musteriId.text.strip())

                    panelLinkleri = driver.find_elements(By.XPATH, "//a[contains(@class,'p-panelmenu-header-link')]")
                    panelLinkleri[2].click()
                    time.sleep(5)
                    randevuIndex = 0

                    while True:
                        if is_blocked(driver):
                            wait_for_user("Randevu listesi bloklu.")

                        randevuRows = driver.find_elements(By.XPATH, '//table[contains(@class, "p-datatable-table")]/tbody/tr')
                        for row in randevuRows:
                            randevuIndex = randevuIndex + 1
                            cells = row.find_elements(By.TAG_NAME, "td")

                            randevuDetayi = cells[7].find_element(By.TAG_NAME, "a")

                            geldiBilgi = cells[3].text.strip()
                            tarih = cells[0].text.strip()
                            if not son_bir_yil_icinde_mi(tarih):
                                continue
                            saat = cells[1].text.strip()
                            olusturan = cells[5].text.strip()
                            olusturulma = cells[6].text.strip()
                            durum = cells[2].text.strip()
                            randevuDetayHref = randevuDetayi.get_attribute("href")
                            driver.execute_script("window.open(arguments[0], '_blank');", randevuDetayHref)
                            driver.switch_to.window(driver.window_handles[2])
                            time.sleep(5)

                            if is_blocked(driver):
                                wait_for_user("Randevu detay sayfası bloklu.")

                            randevuNotu = driver.find_element(By.XPATH, './/input[@placeholder="Notlar"]')
                            randevuDetayKart = driver.find_elements(By.XPATH, '//div[contains(@class,"card-w-title")]')
                            p_grid_divs = randevuDetayKart[0].find_elements(By.XPATH, './/div[contains(@class,"p-grid ng-star-inserted")]')
                            p_grid_divs2 = randevuDetayKart[1].find_elements(By.XPATH, './/div[contains(@class,"p-grid ng-star-inserted")]')
                            randevuHizmetler = []
                            urunler = []

                            for div in p_grid_divs:
                                paketMevcut = div.find_elements(By.XPATH, './/input[@placeholder="Paket mevcut"]')
                                hizmetFiyati = 0
                                if len(paketMevcut) == 0:
                                    hizmetFiyati = div.find_element(By.XPATH, './/input[@placeholder="Tutar"]').get_attribute('value')
                                hizmetSuresi = div.find_element(By.XPATH, './/input[@placeholder="Hizmet süresi"]').get_attribute('value')
                                personel = div.find_element(By.XPATH, './/span[contains(@class,"p-dropdown-label")]').text
                                hizmet = div.find_element(By.XPATH, ".//div").text
                                print("Hizmet Süresi : "+str(hizmetSuresi))
                                print("Hizmet Fiyatı : "+str(hizmetFiyati))
                                print("Hizmet : "+str(hizmet))
                                print("personel : "+str(personel))
                                randevuHizmetler.append({
                                    "hizmet": hizmet,
                                    "fiyat": hizmetFiyati,
                                    "sureDk": hizmetSuresi,
                                    "personel": personel,
                                })

                            for div2 in p_grid_divs2:
                                urunFiyati = div2.find_element(By.XPATH, './/input[@placeholder="Tutar"]').get_attribute('value')
                                adet = div2.find_element(By.XPATH, './/input[@placeholder="Adet"]').get_attribute('value')
                                personel = div2.find_element(By.XPATH, './/span[contains(@class,"p-dropdown-label")]').text
                                urun = div2.find_element(By.XPATH, ".//div").text
                                print("Ürün adedi : "+str(adet))
                                print("Ürün Fiyatı : "+str(urunFiyati))
                                print("Ürün : "+str(urun))
                                print("personel : "+str(personel))
                                urunler.append({
                                    "urun": urun,
                                    "fiyat": urunFiyati,
                                    "adet": adet,
                                    "personel": personel,
                                })

                            print("Randevu tarihi : "+str(tarih))
                            print("Randevu saati : "+str(saat))
                            print("Randevuya geldi : "+str(geldiBilgi))
                            print("Randevuyu oluşturan : "+str(olusturan))

                            randevuAdisyonData = {
                                "notlar": randevuNotu.get_attribute('value'),
                                "salonId": ISLETME_ID,
                                "userId": musteriId.text,
                                "tarih": tarih,
                                "saat": saat,
                                "geldi": geldiBilgi,
                                "durum": durum,
                                "olusturan": olusturan,
                                "hizmetler": randevuHizmetler,
                                "urunler": urunler,
                                "olusturulma": olusturulma,
                            }

                            adisyonId = safe_post(f"{API_BASE}/api/v1/salonAppyAdisyonRandevuEkle", randevuAdisyonData)
                            print(f"✅ randevu ve adisyon eklenme durumu " + adisyonId.text)

                            # Tahsilat bolumleri (3 farkli div index)
                            odemeBolumu = driver.find_elements(By.XPATH, '/html/body/app-root/app-main/div/div/div[1]/app-booking-details/div[2]/div[2]')
                            time.sleep(1)
                            if len(odemeBolumu) > 0:
                                for div_idx in (4, 5, 6):
                                    yokTextXp = f'/html/body/app-root/app-main/div/div/div[1]/app-booking-details/div[2]/div[2]/div[{div_idx}]/div/div/div[2]/div/span'
                                    listXp    = f'/html/body/app-root/app-main/div/div/div[1]/app-booking-details/div[2]/div[2]/div[{div_idx}]/div/div/div[2]/div/div'
                                    yokText = driver.find_elements(By.XPATH, yokTextXp)
                                    tahsilatlar = driver.find_elements(By.XPATH, listXp)
                                    if len(yokText) == 0:
                                        for tahsilat in tahsilatlar:
                                            tahsilatTarihi = tahsilat.find_element(By.XPATH, './div[1]')
                                            odemeYontemi = tahsilat.find_element(By.XPATH, './div[2]')
                                            tutar = tahsilat.find_element(By.XPATH, './div[3]')
                                            print("Tahsilat tarihi : "+str(tahsilatTarihi.text))
                                            print("Ödeme Yöntemi : "+str(odemeYontemi.text))
                                            print("Tutar : "+str(tutar.text))
                                            tahsilatData = {
                                                "userId": musteriId.text,
                                                "adisyonId": adisyonId.text,
                                                "odemeTarihi": tahsilatTarihi.text,
                                                "tahsilatTutari": tutar.text.replace(' TL', ''),
                                                "odemeYontemi": odemeYontemi.text,
                                                "salonId": ISLETME_ID,
                                            }
                                            tahsilatId = safe_post(f"{API_BASE}/api/v1/salonAppyTahsilatEkle", tahsilatData)
                                            print(f"✅ Tahsilat ekleme durumu : "+tahsilatId.text)

                            driver.close()
                            driver.switch_to.window(driver.window_handles[1])

                        try:
                            next_button = driver.find_element(By.XPATH, '//button[contains(@class, "p-paginator-next")]')
                            if "p-disabled" not in next_button.get_attribute("class"):
                                next_button.click()
                                time.sleep(5)
                            else:
                                print("Tüm randevular tarandı!")
                                break
                        except Exception:
                            print("Randevu tablosunda ileri butonu bulunamadı!")
                            break

                except Exception as e:
                    print("Detay bilgisi alınamadı!", e)
                    print(f"Müşteri index {musteriIndex}")
                    if is_blocked(driver):
                        wait_for_user("Sayfa bloklu hata sirasinda.")
                    elif is_network_down():
                        wait_for_user("Network koptu hata sirasinda.")

                # Detay sekmelerini kapat, ana sayfaya don
                while len(driver.window_handles) > 1:
                    try:
                        driver.switch_to.window(driver.window_handles[-1])
                        driver.close()
                    except Exception:
                        break
                driver.switch_to.window(driver.window_handles[0])

            except Exception as e:
                print(f"10. sütunda <a> bulunamadı veya hata: {e}")

    try:
        next_button = driver.find_element(By.XPATH, '//button[contains(@class, "p-paginator-next")]')
        if "p-disabled" not in next_button.get_attribute("class"):
            next_button.click()
            time.sleep(2)
        else:
            print("Tüm müşteriler tarandı!")
            break
    except Exception:
        print("Müşteri tablosunda ileri butonu bulunamadı!")
        break

input("Tarayıcıyı kapatmak için Enter'a bas...")
