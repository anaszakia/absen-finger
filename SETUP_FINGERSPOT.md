# Setup Fingerspot.io untuk Sistem Penggajian

## 📋 Overview

Sistem ini terintegrasi dengan **Fingerspot.io** untuk menerima data absensi secara real-time dari mesin fingerspot Anda.

---

## 🚀 Quick Start

### 1. Dapatkan API Token

1. Login ke: https://developer.fingerspot.io
2. Menu **Settings** → **API Token**
3. Klik **Generate Token** atau **Create New Token**
4. Copy API Token yang ditampilkan
5. **Penting:** Simpan token ini, tidak akan ditampilkan lagi

### 2. Dapatkan Cloud ID

1. Di dashboard fingerspot.io, lihat informasi mesin Anda
2. Copy **Cloud ID** mesin (contoh: `C263045107E1C26`)

### 3. Konfigurasi di Laravel

Edit file `.env`:
```env
FINGERSPOT_API_TOKEN=your_api_token_here
FINGERSPOT_CLOUD_ID=your_cloud_id_here
```

### 4. Setup Webhook di Fingerspot.io

1. Login ke: https://developer.fingerspot.io
2. Menu **Webhook** di sidebar
3. Klik **Tambah Webhook**
4. Masukkan URL webhook Anda:
   ```
   https://yourdomain.com/api/fingerspot/webhook
   ```
   
   > ⚠️ Untuk localhost, gunakan ngrok atau tunneling service

5. Pilih Events:
   - ✅ **Attendance / Scanlog** (data absensi)
   - ✅ **User / Person** (data karyawan)

6. **Save & Test**

---

## 📡 Cara Kerja

```
┌─────────────────┐         ┌──────────────────┐         ┌──────────────────┐
│  Mesin Fingerspot │───────>│  Fingerspot.io   │───────>│  Laravel App     │
│  (Scan Finger)  │         │  (Cloud)         │         │  (Your Server)   │
└─────────────────┘         └──────────────────┘         └──────────────────┘
      Realtime                   Webhook Push              Auto Save
```

1. Karyawan scan finger di mesin
2. Mesin kirim data ke Fingerspot.io cloud
3. Fingerspot.io kirim webhook ke Laravel
4. Laravel auto-save ke database
5. Data langsung muncul di sistem

---

## 🔌 API Endpoints

### 1. Sync Employee Names
**URL:** `POST /api/fingerspot/sync-employee-names`  
**Fungsi:** Ambil semua karyawan dari mesin dan sync nama

**Response:**
```json
{
  "success": true,
  "message": "Berhasil sync karyawan dari mesin: 2 dibuat, 3 diupdate",
  "total_pins": 5,
  "created": 2,
  "updated": 3
}
```

### 2. Check Connection
**URL:** `GET /api/fingerspot/check-connection`  
**Fungsi:** Test koneksi ke Fingerspot.io API

### 3. Get All PINs
**URL:** `GET /api/fingerspot/all-pins`  
**Fungsi:** Lihat daftar PIN yang terdaftar di mesin

---

## 🔧 Troubleshooting

### Nama Karyawan Masih "Employee 1, Employee 2"

**Solusi:**
```
POST /api/fingerspot/sync-employee-names
```

Atau klik tombol **"Sync dari Mesin"** di halaman Data Karyawan.

### Error: "Cloud ID tidak ditemukan"

**Penyebab:** Cloud ID salah atau tidak ada karyawan di mesin

**Solusi:**
1. Cek Cloud ID di dashboard fingerspot.io
2. Pastikan ada karyawan yang terdaftar di mesin
3. Update `FINGERSPOT_CLOUD_ID` di `.env`

### Error: "API Token tidak valid"

**Solusi:**
1. Generate token baru dari developer.fingerspot.io
2. Update `FINGERSPOT_API_TOKEN` di `.env`
3. Restart server Laravel

---

## 📚 Dokumentasi Lengkap

Lihat [FINGERSPOT_API_SETUP.md](FINGERSPOT_API_SETUP.md) untuk dokumentasi API lengkap.

---

**Dibuat:** Desember 2025  
**Versi:** 2.0 (Fingerspot.io Cloud-Based)
}
```

### 2. User Data (Person)

```json
{
  "type": "user",
  "cloud_id": "C263045107E1C26",
  "data": {
    "pin": "001",
    "personname": "Budi Santoso",
    "privilege": "0"
  }
}
```

---

## 🎯 Fitur Auto-Process

### ✅ Yang Dilakukan Otomatis:

1. **Auto-create Karyawan**
   - Jika ada absensi dari PIN yang belum terdaftar
   - Sistem otomatis membuat data karyawan baru

2. **Auto-create Mesin**
   - Jika Cloud ID belum terdaftar
   - Sistem otomatis register mesin

3. **Smart Check-In/Check-Out**
   - Absensi pertama = Check-In
   - Absensi berikutnya di hari sama = Check-Out

4. **Status Otomatis**
   - Late: jika check-in > 08:00
   - Present: jika check-in <= 08:00

---

## 📊 Monitoring

### 1. Cek Webhook Logs

```sql
SELECT * FROM fingerspot_webhook_logs 
ORDER BY created_at DESC 
LIMIT 10;
```

### 2. Cek Data Absensi Terbaru

```sql
SELECT e.employee_id, e.name, a.date, a.check_in, a.check_out, a.status
FROM attendances a
JOIN employees e ON a.employee_id = e.id
ORDER BY a.created_at DESC
LIMIT 10;
```

### 3. Cek di Web

- Menu: **HR & Absensi** → **Rekap Absensi**
- Data akan muncul real-time setelah scan

---

## 🐛 Troubleshooting

### Problem 1: Webhook tidak menerima data

**Penyebab:**
- URL tidak bisa diakses dari internet
- Firewall memblokir
- URL salah di Fingerspot.io

**Solusi:**
```bash
# Test endpoint dari luar
curl http://192.168.0.118:8000/api/fingerspot/test

# Cek firewall
Test-NetConnection -ComputerName 192.168.0.118 -Port 8000

# Allow port 8000
New-NetFirewallRule -DisplayName "Laravel Dev" -Direction Inbound -LocalPort 8000 -Protocol TCP -Action Allow
```

### Problem 2: Data tidak masuk ke database

**Cek Log:**
```bash
# Lihat log Laravel
Get-Content storage/logs/laravel.log -Tail 50

# Filter fingerspot only
Select-String -Path storage/logs/laravel.log -Pattern "Fingerspot"
```

**Cek Webhook Logs Table:**
```sql
SELECT * FROM fingerspot_webhook_logs ORDER BY id DESC LIMIT 1;
```

### Problem 3: Localhost tidak bisa diakses internet

**Gunakan ngrok:**
```bash
# Install ngrok
choco install ngrok

# Jalankan ngrok
ngrok http 8000

# Copy URL yang diberikan, contoh:
# https://abc123.ngrok.io

# Gunakan di Fingerspot:
# https://abc123.ngrok.io/api/fingerspot/webhook
```

### Problem 4: Data duplikat

**Penyebab:** Webhook dipanggil berkali-kali untuk event yang sama

**Solusi:** Sistem sudah handle duplikat. Cek dengan:
```sql
SELECT date, employee_id, COUNT(*) as total
FROM attendances
GROUP BY date, employee_id
HAVING total > 1;
```

---

## 📱 Testing Manual

### 1. Test dengan cURL

```bash
# Test endpoint aktif
curl http://192.168.0.118:8000/api/fingerspot/test

# Simulate webhook attendance
curl -X POST http://192.168.0.118:8000/api/fingerspot/webhook \
-H "Content-Type: application/json" \
-d '{
  "type": "attendance",
  "cloud_id": "C263045107E1C26",
  "data": {
    "pin": "001",
    "personname": "Test User",
    "scan_date": "2025-12-11 08:00:00"
  }
}'

# Simulate webhook user
curl -X POST http://192.168.0.118:8000/api/fingerspot/webhook \
-H "Content-Type: application/json" \
-d '{
  "type": "user",
  "cloud_id": "C263045107E1C26",
  "data": {
    "pin": "002",
    "personname": "New Employee"
  }
}'
```

### 2. Test dengan Postman

1. **Method:** POST
2. **URL:** `http://192.168.0.118:8000/api/fingerspot/webhook`
3. **Headers:**
   - Content-Type: application/json
4. **Body (raw JSON):**
```json
{
  "type": "attendance",
  "cloud_id": "C263045107E1C26",
  "data": {
    "pin": "001",
    "personname": "Test User",
    "scan_date": "2025-12-11 08:00:00"
  }
}
```

---

## 📈 Best Practices

### 1. Monitoring Regular
- Cek webhook logs setiap hari
- Pastikan tidak ada error yang berulang
- Monitor storage space untuk logs

### 2. Backup
- Backup database regular
- Export webhook logs setiap bulan
- Simpan konfigurasi Fingerspot

### 3. Security
- Jangan expose endpoint ke public tanpa validasi
- Gunakan HTTPS untuk production
- Set rate limiting jika perlu

### 4. Maintenance
- Clear old webhook logs (> 3 bulan)
```sql
DELETE FROM fingerspot_webhook_logs 
WHERE created_at < DATE_SUB(NOW(), INTERVAL 3 MONTH);
```

---

## 🆘 Support

### Fingerspot.io
- Website: https://fingerspot.io
- Support: support@fingerspot.io
- Documentation: https://developer.fingerspot.io/docs

### Local Support
- Check: `storage/logs/laravel.log`
- Email: admin@yourcompany.com

---

## ✅ Checklist Setup

- [ ] Login ke developer.fingerspot.io
- [ ] Mesin sudah terkoneksi (Cloud ID: C263045107E1C26)
- [ ] Webhook URL sudah disimpan
- [ ] Event Attendance & User sudah dicentang
- [ ] Test endpoint berhasil (hijau)
- [ ] Test scan fingerprint di mesin
- [ ] Data muncul di web dalam 5 detik
- [ ] Cek webhook logs ada entry
- [ ] Cek tabel attendances ada data baru

---

## 🎉 Selesai!

Sistem Anda sudah siap menerima data absensi real-time dari Fingerspot.io!

**Next Steps:**
1. Lakukan scan fingerprint di mesin
2. Tunggu 3-5 detik
3. Refresh halaman Rekap Absensi
4. Data akan otomatis muncul!
