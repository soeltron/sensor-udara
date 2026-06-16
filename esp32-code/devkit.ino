#include <DHT.h>
#include <WiFi.h>
#include <HTTPClient.h>
#include <ArduinoJson.h>

// ==================== PIN DEFINITIONS ====================
#define DHTPIN 4
#define DHTTYPE DHT22
#define MQ135_PIN 34
#define RELAY_PIN 5

#define LED_HIJAU 18
#define LED_KUNING 19
#define LED_MERAH 23

// ==================== WIFI & SERVER CONFIG ====================
const char* ssid     = "NAMA_WIFI_ANDA";       // Ganti dengan SSID WiFi Anda
const char* password = "PASSWORD_WIFI_ANDA";   // Ganti dengan password WiFi Anda

const char* serverDataUrl     = "http://192.168.1.100/api/data";     // Ganti dengan endpoint API data
const char* serverSettingsUrl = "http://192.168.1.100/api/settings"; // Ganti dengan endpoint API settings

// ==================== OBJECTS & VARS ====================
DHT dht(DHTPIN, DHTTYPE);

const unsigned long SEND_INTERVAL     = 15000UL; // 15 detik
const unsigned long SETTINGS_INTERVAL = 20000UL; // 20 detik

unsigned long lastSendTime     = 0;
unsigned long lastSettingsTime = 0;

float maxTemperature = 30.0;
int   maxAirQuality = 500;

bool fanStatusServer = false;

// ==================== FUNGSI KONEKSI WIFI ====================
void connectWiFi() {
  Serial.print("Menghubungkan ke WiFi: ");
  Serial.println(ssid);

  WiFi.begin(ssid, password);

  int attempt = 0;
  while (WiFi.status() != WL_CONNECTED) {
    delay(500);
    Serial.print(".");
    attempt++;
    if (attempt > 30) {
      Serial.println("\nGagal terhubung ke WiFi! Restart...");
      ESP.restart();
    }
  }

  Serial.println("\nWiFi Terhubung!");
  Serial.print("IP Address: ");
  Serial.println(WiFi.localIP());
}

// ==================== FUNGSI AMBIL SETTINGS DARI SERVER ====================
void updateSettingsFromServer() {
  if (WiFi.status() != WL_CONNECTED) {
    connectWiFi();
  }

  HTTPClient http;
  http.begin(serverSettingsUrl);
  int httpCode = http.GET();

  if (httpCode == HTTP_CODE_OK) {
    String payload = http.getString();
    StaticJsonDocument<256> doc;
    DeserializationError err = deserializeJson(doc, payload);

    if (!err && doc["status"] == "success" && doc.containsKey("data")) {
      JsonObject data = doc["data"];
      maxTemperature = data["max_temperature"] | maxTemperature;
      maxAirQuality = data["max_air_quality"] | maxAirQuality;
      fanStatusServer = (String(data["fan"]).equalsIgnoreCase("ON"));

      Serial.println("[SETTINGS] Diperbarui dari server:");
      Serial.print("  maxTemperature = "); Serial.println(maxTemperature);
      Serial.print("  maxAirQuality = "); Serial.println(maxAirQuality);
      Serial.print("  fanStatusServer = "); Serial.println(fanStatusServer ? "ON" : "OFF");
    } else {
      Serial.println("[ERROR] Response settings tidak valid.");
    }
  } else {
    Serial.print("[ERROR] Gagal ambil settings, kode: ");
    Serial.println(httpCode);
  }

  http.end();
}

// ==================== FUNGSI KIRIM DATA KE LARAVEL ====================
bool kirimDataKeServer(float suhu, float kelembapan, int kualitasUdara, bool kipasAktif) {
  if (WiFi.status() != WL_CONNECTED) {
    connectWiFi();
  }

  HTTPClient http;
  http.begin(serverDataUrl);
  http.addHeader("Content-Type", "application/json");

  StaticJsonDocument<256> doc;
  doc["temperature"]   = suhu;
  doc["humidity"]      = kelembapan;
  doc["air_quality"]   = kualitasUdara;
  doc["fan_status"]    = kipasAktif ? "ON" : "OFF";

  String jsonPayload;
  serializeJson(doc, jsonPayload);

  Serial.println("[HTTP] Mengirim data: " + jsonPayload);

  int httpResponseCode = http.POST(jsonPayload);
  bool success = false;

  if (httpResponseCode > 0) {
    String response = http.getString();
    Serial.print("[HTTP] Response Code: ");
    Serial.println(httpResponseCode);
    Serial.print("[HTTP] Response Body: ");
    Serial.println(response);
    success = (httpResponseCode == HTTP_CODE_OK || httpResponseCode == HTTP_CODE_CREATED);
  } else {
    Serial.print("[ERROR] HTTP POST Gagal. Error: ");
    Serial.println(http.errorToString(httpResponseCode).c_str());
  }

  http.end();
  return success;
}

// ==================== SETUP ====================
void setup() {
  Serial.begin(115200);

  pinMode(RELAY_PIN, OUTPUT);
  pinMode(LED_HIJAU, OUTPUT);
  pinMode(LED_KUNING, OUTPUT);
  pinMode(LED_MERAH, OUTPUT);

  digitalWrite(LED_HIJAU, LOW);
  digitalWrite(LED_KUNING, LOW);
  digitalWrite(LED_MERAH, LOW);
  digitalWrite(RELAY_PIN, LOW);

  dht.begin();
  connectWiFi();
  updateSettingsFromServer();

  Serial.println("Sistem Siap! Memulai pembacaan sensor...");
}

// ==================== LOOP ====================
void loop() {
  if (WiFi.status() != WL_CONNECTED) {
    connectWiFi();
  }

  float suhu       = dht.readTemperature();
  float kelembapan = dht.readHumidity();
  int mq135_value  = analogRead(MQ135_PIN);

  if (isnan(suhu) || isnan(kelembapan)) {
    Serial.println("[WARNING] Gagal membaca sensor DHT22!");
    delay(500);
    return;
  }

  bool suhuPan   = suhu > maxTemperature;
  bool suhuDingin = suhu < 18.0;
  bool kelembapanTinggi = kelembapan > 70.0;
  bool kelembapanRendah = kelembapan < 30.0;
  bool udaraKotor = mq135_value > maxAirQuality;

  bool kondisiBaik = !udaraKotor && !suhuPan && !suhuDingin && !kelembapanTinggi && !kelembapanRendah;
  bool kondisiSedang = !udaraKotor && !kondisiBaik;

  // Gunakan fan berdasarkan perintah web (server)
  digitalWrite(RELAY_PIN, fanStatusServer ? HIGH : LOW);

  // LED logic
  digitalWrite(LED_MERAH, udaraKotor ? HIGH : LOW);
  digitalWrite(LED_KUNING, (kondisiSedang && !udaraKotor) ? HIGH : LOW);
  digitalWrite(LED_HIJAU, kondisiBaik ? HIGH : LOW);

  if (millis() - lastSettingsTime >= SETTINGS_INTERVAL) {
    updateSettingsFromServer();
    lastSettingsTime = millis();
  }

  if (millis() - lastSendTime >= SEND_INTERVAL) {
    Serial.println("\n========== Mengirim Data ke Database ==========");
    Serial.print("Suhu          : "); Serial.print(suhu); Serial.println(" C");
    Serial.print("Kelembapan    : "); Serial.print(kelembapan); Serial.println(" %");
    Serial.print("Kualitas Udara: "); Serial.println(mq135_value);
    Serial.print("Fan Server    : "); Serial.println(fanStatusServer ? "ON" : "OFF");
    Serial.print("LED Merah     : "); Serial.println(udaraKotor ? "ON" : "OFF");
    Serial.print("LED Kuning    : "); Serial.println((kondisiSedang && !udaraKotor) ? "ON" : "OFF");
    Serial.print("LED Hijau     : "); Serial.println(kondisiBaik ? "ON" : "OFF");
    Serial.print("WiFi Signal   : "); Serial.print(WiFi.RSSI()); Serial.println(" dBm");

    bool berhasil = kirimDataKeServer(suhu, kelembapan, mq135_value, fanStatusServer);
    Serial.println(berhasil ? "[OK] Data berhasil dikirim ke server!" : "[GAGAL] Data gagal dikirim ke server.");
    Serial.println("===============================================\n");
    lastSendTime = millis();
  }

  delay(500);
}
