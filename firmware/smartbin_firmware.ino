/*
 * =====================================================================
 *  SmartBin IoT — Firmware NodeMCU ESP8266
 *  Sesuai BAB IV.2 (Flowchart Firmware), BAB V (Pin Mapping),
 *  BAB VI (Format Data & Komunikasi) PRD SmartBin IoT v1.0
 * =====================================================================
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
const char* WIFI_SSID     = "Redmi Note 9 Pro";
const char* WIFI_PASSWORD = "87654321";

// Ganti dengan alamat server website Anda, contoh:
// "http://192.168.1.10/smartbin-web/api/send_data" (jaringan lokal)
// "https://domainanda.com/api/send_data" (online, disarankan HTTPS)
const char* SERVER_URL = "http://172.28.41.92/smartbin-web/api/send_data";

// Harus SAMA PERSIS dengan $apiKey di application/controllers/Api.php
const char* API_KEY = "SMARTBIN_API_KEY_2026";

// ---------------------------------------------------------------
// PIN MAPPING — sesuai BAB V.1 PRD
// ---------------------------------------------------------------
#define TRIG1 D1   // Ultrasonik Atas - Trig (deteksi tangan)
#define ECHO1 D2   // Ultrasonik Atas - Echo
#define TRIG2 D3   // Ultrasonik Dalam - Trig (deteksi kapasitas)
#define ECHO2 D4   // Ultrasonik Dalam - Echo
#define PIN_INDUKTIF  D5   // Sensor Proximity Induktif (mendeteksi logam)
#define PIN_KAPASITIF D6   // Sensor Proximity Kapasitif (mendeteksi objek non-logam)
#define SERVO1_PIN D7      // Servo Tutup
#define SERVO2_PIN D8      // Servo Pemilah
#define LED_PIN D0         // LED indikator tong penuh
#define BUZZER_PIN RX      // Buzzer (GPIO3)

// ---------------------------------------------------------------
// PARAMETER SISTEM — sesuai BAB 3.5 PRD
// ---------------------------------------------------------------
const float TINGGI_TONG_CM   = 60.0;  // tinggi tong, kalibrasi sesuai fisik
const float THRESHOLD_TANGAN = 10.0;  // cm, sensor atas mendeteksi tangan (5-10cm)
const int   SERVO1_TUTUP     = 0;     // derajat, posisi tertutup
const int   SERVO1_BUKA      = 90;    // derajat, posisi terbuka
const int   SERVO2_NETRAL    = 90;    // derajat, posisi tengah
const int   SERVO2_NONLOGAM = 30;    // derajat, arah wadah non-logam (kiri)
const int   SERVO2_LOGAM    = 150;   // derajat, arah wadah logam (kanan)

// delay_close & interval_send idealnya diambil dari /api/summary (tabel settings)
// agar sinkron dengan Pengaturan di website. Untuk kesederhanaan firmware ini
// memakai nilai default lalu bisa disesuaikan manual sesuai menu Pengaturan.
unsigned long DELAY_TUTUP_MS   = 5000;  // 5 detik (default delay_close)
unsigned long INTERVAL_KIRIM_MS = 5000; // 5 detik (default interval_send)

Servo servoTutup;
Servo servoPemilah;

unsigned long lastSend = 0;
String lastWasteType = "Unknown";
String lidStatus = "Closed";

// ---------------------------------------------------------------
// SETUP
// ---------------------------------------------------------------
void setup() {
  Serial.begin(115200);

  pinMode(TRIG1, OUTPUT);
  pinMode(ECHO1, INPUT);
  pinMode(TRIG2, OUTPUT);
  pinMode(ECHO2, INPUT);
  pinMode(PIN_INDUKTIF, INPUT);
  pinMode(PIN_KAPASITIF, INPUT);
  pinMode(LED_PIN, OUTPUT);
  pinMode(BUZZER_PIN, OUTPUT);

  servoTutup.attach(SERVO1_PIN);
  servoPemilah.attach(SERVO2_PIN);
  servoTutup.write(SERVO1_TUTUP);
  servoPemilah.write(SERVO2_NETRAL);

  digitalWrite(LED_PIN, LOW);
  digitalWrite(BUZZER_PIN, LOW);

  connectWiFi();
}

// ---------------------------------------------------------------
// LOOP UTAMA — mengikuti flowchart BAB 4.2
// ---------------------------------------------------------------
void loop() {
  if (WiFi.status() != WL_CONNECTED) {
    connectWiFi();
  }

  float jarakTangan = bacaJarak(TRIG1, ECHO1);

  // 1) Deteksi tangan -> buka tutup
  if (jarakTangan > 0 && jarakTangan < THRESHOLD_TANGAN) {
    bukaTutup();

    // 2) Baca sensor proximity untuk menentukan jenis sampah
    String jenis = deteksiJenisSampah();
    lastWasteType = jenis;

    // 3) Gerakkan servo pemilah sesuai jenis
    gerakkanServoPemilah(jenis);

    // 4) Delay lalu tutup kembali
    delay(DELAY_TUTUP_MS);
    tutupKembali();

    // Kirim data segera setelah ada aktivitas pembuangan sampah
    kirimData();
    lastSend = millis();
  }

  // 5) Kirim data berkala sesuai interval_send walau tidak ada aktivitas
  if (millis() - lastSend >= INTERVAL_KIRIM_MS) {
    kirimData();
    lastSend = millis();
  }

  delay(100); // jeda kecil supaya loop tidak terlalu rapat
}

// ---------------------------------------------------------------
// FUNGSI: bacaJarak() — pengukuran ultrasonik HC-SR04
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
// FUNGSI: deteksiJenisSampah() — sesuai BAB 3.5.2 & 3.5.3
// Catatan: sensor proximity induktif mendeteksi benda konduktif (logam),
// sensor proximity kapasitif mendeteksi objek non-konduktif (non-logam).
// ---------------------------------------------------------------
String deteksiJenisSampah() {
  delay(300); // beri waktu objek jatuh melewati sensor proximity
  bool logam    = digitalRead(PIN_INDUKTIF) == HIGH;
  bool nonLogam = digitalRead(PIN_KAPASITIF) == HIGH;

  if (logam) return "Logam";
  if (!logam && nonLogam) return "Non-Logam";
  return "Unknown";
}

void gerakkanServoPemilah(String jenis) {
  if (jenis == "Logam") {
    servoPemilah.write(SERVO2_LOGAM);
  } else if (jenis == "Non-Logam") {
    servoPemilah.write(SERVO2_NONLOGAM);
  } else {
    servoPemilah.write(SERVO2_NETRAL);
  }
  delay(500); // beri waktu servo bergerak & sampah jatuh ke wadah
  servoPemilah.write(SERVO2_NETRAL);
}

void bukaTutup() {
  servoTutup.write(SERVO1_BUKA);
  lidStatus = "Open";
}

void tutupKembali() {
  servoTutup.write(SERVO1_TUTUP);
  lidStatus = "Closed";
}

// ---------------------------------------------------------------
// FUNGSI: hitungKapasitas() — sesuai rumus BAB 3.5.1
// ---------------------------------------------------------------
int hitungKapasitas() {
  float jarak = bacaJarak(TRIG2, ECHO2);
  if (jarak < 0) return -1; // gagal baca sensor

  float persen = ((TINGGI_TONG_CM - jarak) / TINGGI_TONG_CM) * 100.0;
  if (persen < 0)   persen = 0;
  if (persen > 100) persen = 100;
  return (int) persen;
}

// ---------------------------------------------------------------
// FUNGSI: kirimData() — susun JSON & POST ke /api/send_data
// ---------------------------------------------------------------
void kirimData() {
  if (WiFi.status() != WL_CONNECTED) return;

  int kapasitas = hitungKapasitas();
  if (kapasitas < 0) kapasitas = 0; // fallback jika sensor gagal baca

  // Indikator LED & Buzzer sesuai ambang batas (idealnya sinkron dengan
  // warning_limit/critical_limit dari tabel settings — di sini pakai default 80/100)
  if (kapasitas >= 100) {
    digitalWrite(LED_PIN, HIGH);
    digitalWrite(BUZZER_PIN, HIGH);
  } else if (kapasitas >= 80) {
    digitalWrite(LED_PIN, HIGH);
    digitalWrite(BUZZER_PIN, LOW);
  } else {
    digitalWrite(LED_PIN, LOW);
    digitalWrite(BUZZER_PIN, LOW);
  }

  StaticJsonDocument<256> doc;
  doc["capacity"]   = kapasitas;
  doc["waste_type"] = lastWasteType;
  doc["lid"]        = lidStatus;
  doc["status"]     = "Online";

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
