/*
 * =====================================================================
 *  SmartBin IoT — Firmware NodeMCU ESP8266
 *  DISESUAIKAN dengan hardware yang benar-benar dipakai:
 *   - 2x sensor ULTRASONIK  -> ukur kapasitas per-bin (1 bin Logam, 1 bin Non-Logam)
 *   - 1x sensor INFRARED OBSTACLE -> trigger deteksi ada objek/tangan masuk
 *   - 1x sensor PROXIMITY INDUKTIF -> deteksi logam (non-logam = "bukan logam")
 *   - 1x SERVO MG90S -> pemilah kiri/kanan (TIDAK ADA servo tutup terpisah)
 *
 *  Library yang dibutuhkan (install via Arduino IDE > Library Manager):
 *   - ESP8266WiFi        (bawaan board package ESP8266)
 *   - ESP8266HTTPClient  (bawaan board package ESP8266)
 *   - Servo               (bawaan, "Servo" untuk ESP8266)
 *   - ArduinoJson         (install manual, versi 6.x)
 *
 *  Board: NodeMCU 1.0 (ESP-12E Module) — Arduino IDE
 * =====================================================================
 */

#include <ESP8266WiFi.h>
#include <ESP8266HTTPClient.h>
#include <WiFiClient.h>
#include <Servo.h>
#include <ArduinoJson.h>

// ---------------------------------------------------------------
// KONFIGURASI WIFI & SERVER — SESUAIKAN SEBELUM UPLOAD
// ---------------------------------------------------------------
const char* WIFI_SSID     = "callmecakraa";
const char* WIFI_PASSWORD = "12345678";

// Ganti dengan alamat server website Anda, contoh:
// "http://192.168.1.10/smartbin-web/api/send_data" (jaringan lokal)
// "https://domainanda.com/api/send_data" (online, disarankan HTTPS)
const char* SERVER_URL = "http://10.223.132.112/smartbin-web/api/send_data";

// Harus SAMA PERSIS dengan $apiKey di application/controllers/Api.php
const char* API_KEY = "SMARTBIN_API_KEY_2026";

// ---------------------------------------------------------------
// PIN MAPPING — sesuai hardware yang dipasang
// ---------------------------------------------------------------
#define ULTRA_LOGAM_TRIG    D2   // Ultrasonik bin LOGAM - Trig (ukur kapasitas)
#define ULTRA_LOGAM_ECHO    D1   // Ultrasonik bin LOGAM - Echo
#define ULTRA_NONLOGAM_TRIG D5   // Ultrasonik bin NON-LOGAM - Trig (ukur kapasitas)
#define ULTRA_NONLOGAM_ECHO D6   // Ultrasonik bin NON-LOGAM - Echo
#define PIN_IR_OBSTACLE     D3   // Sensor Infrared Obstacle - trigger deteksi objek/tangan
#define PIN_INDUKTIF        D7   // Sensor Proximity Induktif - deteksi logam
#define SERVO_PEMILAH_PIN   D4   // Servo pemilah kiri/kanan (satu-satunya servo)
#define LED_PIN             D0   // LED indikator kapasitas penuh
#define BUZZER_PIN          D9   // Buzzer (GPIO3 / pin RX0)

// Kebanyakan modul IR obstacle avoidance itu ACTIVE-LOW (output LOW saat ada
// objek di depannya, HIGH saat kosong). Kalau ternyata modul Anda kebalikannya
// (banyak juga varian active-HIGH), tinggal ganti true -> false di sini.
const bool IR_ACTIVE_LOW = true;

// Sensor proximity induktif: kebanyakan modul NPN NO -> output HIGH saat IDLE
// (tidak ada logam) dan baru turun ke LOW sesaat saat logam lewat/terdeteksi.
// Kalau ternyata modul Anda active-HIGH (PNP), ganti true -> false di sini.
const bool PROXIMITY_ACTIVE_LOW = true;

// Berapa lama (ms) menunggu & memantau sensor proximity setelah IR trigger,
// untuk menangkap momen objek lewat (mengantisipasi variasi kecepatan jatuh).
const unsigned long JENDELA_DETEKSI_PROXIMITY_MS = 700;
// ---------------------------------------------------------------
// PARAMETER SISTEM — kalibrasi sesuai fisik masing-masing bin
// ---------------------------------------------------------------
const float TINGGI_BIN_LOGAM_CM    = 60.0;  // tinggi bin logam, kalibrasi sesuai fisik
const float TINGGI_BIN_NONLOGAM_CM = 60.0;  // tinggi bin non-logam, kalibrasi sesuai fisik

const int SERVO_NETRAL   = 90;   // derajat, posisi tengah (istirahat)
const int SERVO_NONLOGAM = 10;   // derajat, arah bin non-logam (kiri) - dimiringkan lebih jauh
const int SERVO_LOGAM    = 170;  // derajat, arah bin logam (kanan) - dimiringkan lebih jauh

// delay_close & interval_send idealnya diambil dari /api/summary (tabel settings)
// agar sinkron dengan Pengaturan di website. Untuk kesederhanaan firmware ini
// memakai nilai default lalu bisa disesuaikan manual sesuai menu Pengaturan.
unsigned long DELAY_SORTIR_MS   = 2000;  // lama servo menahan posisi pemilah sebelum balik netral (diperpanjang biar keliatan jelas)
unsigned long INTERVAL_KIRIM_MS = 5000;  // 5 detik (default interval_send)

// Ambang batas LED/buzzer (idealnya sinkron warning_limit/critical_limit di Settings)
const int AMBANG_WARNING  = 80;
const int AMBANG_CRITICAL = 100;

Servo servoPemilah;

unsigned long lastSend = 0;
String lastWasteType = "Unknown";
String lidStatus = "Closed"; // tidak ada lid fisik, dipakai murni sebagai status "sedang memilah" utk dashboard

// Kapasitas TIDAK diukur ulang tiap kali kirim data — cuma di-refresh secara
// berkala (perbaruiKapasitas(), dipanggil dari jalur heartbeat, kondisi tenang).
// Event pemilahan "pinjam" nilai terakhir ini, supaya angka yang tampil di
// Monitoring & Riwayat selalu berasal dari sumber ukur yang sama & konsisten
// (menghindari baca ultrasonik pas kondisi baru saja ada barang jatuh/bergerak).
int capacityLogamTerkini = 0;
int capacityNonLogamTerkini = 0;

// ---------------------------------------------------------------
// SETUP
// ---------------------------------------------------------------
void setup() {
  Serial.begin(115200);

  pinMode(ULTRA_LOGAM_TRIG, OUTPUT);
  pinMode(ULTRA_LOGAM_ECHO, INPUT);
  pinMode(ULTRA_NONLOGAM_TRIG, OUTPUT);
  pinMode(ULTRA_NONLOGAM_ECHO, INPUT);
  pinMode(PIN_IR_OBSTACLE, INPUT);
  pinMode(PIN_INDUKTIF, INPUT);
  pinMode(LED_PIN, OUTPUT);
  pinMode(BUZZER_PIN, OUTPUT);

  servoPemilah.attach(SERVO_PEMILAH_PIN);
  servoPemilah.write(SERVO_NETRAL);

  digitalWrite(LED_PIN, LOW);
  digitalWrite(BUZZER_PIN, LOW);

  connectWiFi();
  perbaruiKapasitas(); // ukur kondisi awal sebelum ada aktivitas apapun
}

// ---------------------------------------------------------------
// LOOP UTAMA
// ---------------------------------------------------------------
void loop() {
  if (WiFi.status() != WL_CONNECTED) {
    connectWiFi();
  }

  // 1) Sensor IR obstacle mendeteksi objek/tangan -> proses pemilahan
  if (objekTerdeteksi()) {
    String jenis = deteksiJenisSampah();
    lastWasteType = jenis;

    gerakkanServoPemilah(jenis);

    // Sengaja TIDAK ukur ulang kapasitas di sini — pakai nilai terakhir hasil
    // perbaruiKapasitas() dari heartbeat sebelumnya (kondisi tenang), supaya
    // konsisten dengan yang ditampilkan Monitoring. Event fokus melaporkan
    // jenis sampah yang baru dipilah, bukan pengukuran kapasitas baru.
    kirimData(true);
    lastSend = millis();
  }

  // 2) Kirim data berkala sesuai interval_send walau tidak ada aktivitas (trigger_type = "heartbeat")
  if (millis() - lastSend >= INTERVAL_KIRIM_MS) {
    kirimData(false);
    lastSend = millis();
  }

  delay(100); // jeda kecil supaya loop tidak terlalu rapat
}

// ---------------------------------------------------------------
// FUNGSI: objekTerdeteksi() — baca sensor infrared obstacle
// ---------------------------------------------------------------
bool objekTerdeteksi() {
  int nilai = digitalRead(PIN_IR_OBSTACLE);
  return IR_ACTIVE_LOW ? (nilai == LOW) : (nilai == HIGH);
}

// ---------------------------------------------------------------
// FUNGSI: bacaJarak() — 1x pengukuran ultrasonik HC-SR04
// ---------------------------------------------------------------
float bacaJarak(int pinTrig, int pinEcho) {
  digitalWrite(pinTrig, LOW);
  delayMicroseconds(2);
  digitalWrite(pinTrig, HIGH);
  delayMicroseconds(10);
  digitalWrite(pinTrig, LOW);

  long durasi = pulseIn(pinEcho, HIGH, 30000); // timeout 30ms (~5m)
  if (durasi == 0) return -1; // tidak ada pantulan terbaca

  float jarakCm = durasi * 0.034 / 2.0; // konversi waktu pantul ke cm
  return jarakCm;
}

// ---------------------------------------------------------------
// FUNGSI: bacaJarakStabil() — ambil beberapa sampel & pakai median
// Kenapa: 1 kali tembak ultrasonik gampang meleset (noise, pantulan
// ganda dari dalam bin, atau sisa gelombang dari sensor satunya kalau
// keduanya ditembak berdekatan). Median lebih tahan spike dibanding
// rata-rata biasa.
// ---------------------------------------------------------------
float bacaJarakStabil(int pinTrig, int pinEcho) {
  const int JUMLAH_SAMPEL = 5;
  float sampel[JUMLAH_SAMPEL];
  int valid = 0;

  for (int i = 0; i < JUMLAH_SAMPEL; i++) {
    float j = bacaJarak(pinTrig, pinEcho);
    if (j >= 0) sampel[valid++] = j;
    delay(15); // jeda antar tembakan biar gema sebelumnya reda
  }
  if (valid == 0) return -1;

  // urutkan lalu ambil nilai tengah (median)
  for (int i = 0; i < valid - 1; i++) {
    for (int j = i + 1; j < valid; j++) {
      if (sampel[j] < sampel[i]) {
        float t = sampel[i]; sampel[i] = sampel[j]; sampel[j] = t;
      }
    }
  }
  return sampel[valid / 2];
}

// ---------------------------------------------------------------
// FUNGSI: deteksiJenisSampah() — baca proximity induktif
// Catatan: cuma ada 1 sensor proximity (induktif -> khusus logam).
// Non-logam ditentukan dari "kalau bukan logam".
// ---------------------------------------------------------------
String deteksiJenisSampah() {
  // Pantau sensor proximity selama jendela waktu tertentu (bukan cuma 1 titik
  // waktu), supaya tetap tertangkap walau kecepatan jatuh objek bervariasi.
  bool logamTerdeteksi = false;
  unsigned long mulai = millis();
  while (millis() - mulai < JENDELA_DETEKSI_PROXIMITY_MS) {
    int nilai = digitalRead(PIN_INDUKTIF);
    bool aktif = PROXIMITY_ACTIVE_LOW ? (nilai == LOW) : (nilai == HIGH);
    if (aktif) { logamTerdeteksi = true; break; }
    delay(10);
  }

  Serial.print("[Proximity] logam terdeteksi = ");
  Serial.println(logamTerdeteksi ? "YA (Logam)" : "TIDAK (Non-Logam)");

  return logamTerdeteksi ? "Logam" : "Non-Logam";
}

void gerakkanServoPemilah(String jenis) {
  lidStatus = "Open"; // status logis "sedang memilah", dilaporkan sebentar ke API
  if (jenis == "Logam") {
    servoPemilah.write(SERVO_LOGAM);
  } else {
    servoPemilah.write(SERVO_NONLOGAM);
  }
  delay(DELAY_SORTIR_MS); // beri waktu servo bergerak & sampah jatuh ke bin
  servoPemilah.write(SERVO_NETRAL);
  lidStatus = "Closed";
}

// ---------------------------------------------------------------
// FUNGSI: hitungKapasitas() — konversi jarak ultrasonik ke persen
// ---------------------------------------------------------------
int hitungKapasitas(int pinTrig, int pinEcho, float tinggiBinCm) {
  float jarak = bacaJarakStabil(pinTrig, pinEcho);
  if (jarak < 0) return -1; // gagal baca sensor

  float persen = ((tinggiBinCm - jarak) / tinggiBinCm) * 100.0;
  if (persen < 0)   persen = 0;
  if (persen > 100) persen = 100;
  return (int) persen;
}

// ---------------------------------------------------------------
// FUNGSI: perbaruiKapasitas() — ukur ultrasonik & simpan ke variabel
// global. Dipanggil dari jalur heartbeat (kondisi tenang, tidak lagi
// ada objek jatuh/servo bergerak), BUKAN dari jalur event.
// ---------------------------------------------------------------
void perbaruiKapasitas() {
  int kapasitasLogam = hitungKapasitas(ULTRA_LOGAM_TRIG, ULTRA_LOGAM_ECHO, TINGGI_BIN_LOGAM_CM);
  delay(60); // jeda supaya gema sensor bin logam reda dulu sebelum sensor bin non-logam nembak (cegah interferensi antar sensor)
  int kapasitasNonLogam = hitungKapasitas(ULTRA_NONLOGAM_TRIG, ULTRA_NONLOGAM_ECHO, TINGGI_BIN_NONLOGAM_CM);
  if (kapasitasLogam < 0) kapasitasLogam = capacityLogamTerkini;       // gagal baca -> pertahankan nilai lama
  if (kapasitasNonLogam < 0) kapasitasNonLogam = capacityNonLogamTerkini;

  capacityLogamTerkini = kapasitasLogam;
  capacityNonLogamTerkini = kapasitasNonLogam;
}

// ---------------------------------------------------------------
// FUNGSI: kirimData() — susun JSON & POST ke /api/send_data
// isEvent=true  -> trigger_type "event"     (ada barang masuk & dipilah,
//                  pakai kapasitas terakhir hasil perbaruiKapasitas())
// isEvent=false -> trigger_type "heartbeat" (ukur kapasitas baru dulu,
//                  baru kirim)
// ---------------------------------------------------------------
void kirimData(bool isEvent) {
  if (WiFi.status() != WL_CONNECTED) return;

  if (!isEvent) {
    perbaruiKapasitas(); // cuma heartbeat yang ukur ulang, di kondisi tenang
  }
  int kapasitasLogam = capacityLogamTerkini;
  int kapasitasNonLogam = capacityNonLogamTerkini;

  int kapasitasTertinggi = max(kapasitasLogam, kapasitasNonLogam);

  // Indikator LED & Buzzer sesuai ambang batas (idealnya sinkron dengan
  // warning_limit/critical_limit dari tabel settings — di sini pakai default 80/100)
  if (kapasitasTertinggi >= AMBANG_CRITICAL) {
    digitalWrite(LED_PIN, HIGH);
    digitalWrite(BUZZER_PIN, HIGH);
  } else if (kapasitasTertinggi >= AMBANG_WARNING) {
    digitalWrite(LED_PIN, HIGH);
    digitalWrite(BUZZER_PIN, LOW);
  } else {
    digitalWrite(LED_PIN, LOW);
    digitalWrite(BUZZER_PIN, LOW);
  }

  StaticJsonDocument<256> doc;
  doc["capacity_logam"]     = kapasitasLogam;
  doc["capacity_non_logam"] = kapasitasNonLogam;
  doc["waste_type"]         = lastWasteType;
  doc["lid"]                = lidStatus;
  doc["status"]             = "Online";
  doc["trigger_type"]       = isEvent ? "event" : "heartbeat";

  String payload;
  serializeJson(doc, payload);

  WiFiClient client;
  HTTPClient http;

  http.begin(client, SERVER_URL);
  http.addHeader("Content-Type", "application/json");
  http.addHeader("X-API-KEY", API_KEY);

  int httpCode = http.POST(payload);

  Serial.print("POST /api/send_data -> HTTP ");
  Serial.println(httpCode);
  Serial.println(payload);

  http.end();
}

// ---------------------------------------------------------------
// FUNGSI: connectWiFi()
// ---------------------------------------------------------------
void connectWiFi() {
  Serial.print("Menghubungkan ke WiFi: ");
  Serial.println(WIFI_SSID);

  WiFi.mode(WIFI_STA);
  WiFi.begin(WIFI_SSID, WIFI_PASSWORD);

  int attempt = 0;
  while (WiFi.status() != WL_CONNECTED && attempt < 40) {
    delay(500);
    Serial.print(".");
    attempt++;
  }

  if (WiFi.status() == WL_CONNECTED) {
    Serial.println("\nWiFi Terhubung. IP: " + WiFi.localIP().toString());
  } else {
    Serial.println("\nGagal terhubung WiFi, akan dicoba ulang di loop berikutnya.");
  }
}
