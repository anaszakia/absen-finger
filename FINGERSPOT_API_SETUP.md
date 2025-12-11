# Panduan Setup Fingerspot.io API

## 🔑 Cara Mendapatkan API Token

### 1. Login ke Developer Portal
1. Buka https://developer.fingerspot.io
2. Login dengan akun Fingerspot.io Anda
3. Jika belum punya akun, daftar terlebih dahulu

### 2. Dapatkan API Token
1. Setelah login, klik menu **Settings** atau **API Token** di sidebar
2. Klik tombol **Generate Token** atau **Create New Token**
3. Copy API Token yang ditampilkan
4. **PENTING:** Simpan token ini dengan aman, tidak akan ditampilkan lagi

### 3. Konfigurasi di Laravel
1. Buka file `.env` di root project
2. Tambahkan konfigurasi berikut:
   ```
   FINGERSPOT_API_TOKEN=2VBG6F40KLHJUHSH
   FINGERSPOT_CLOUD_ID=xxxx
   ```
   - `FINGERSPOT_API_TOKEN`: Token yang sudah di-copy dari developer portal
   - `FINGERSPOT_CLOUD_ID`: Cloud ID mesin fingerspot Anda (cek di dashboard fingerspot.io)
3. Save file `.env`

### 4. Restart Laravel Server
```bash
# Jika menggunakan php artisan serve, tekan Ctrl+C lalu jalankan ulang:
php artisan serve

# Atau jika menggunakan Laragon, restart dari Laragon menu
```

### 5. Test Koneksi
1. Buka http://192.168.0.118:8000/machines/fingerspot/setup
2. Halaman akan otomatis mengecek koneksi ke API Fingerspot.io
3. Jika berhasil, akan muncul status **✅ Mesin Terkoneksi**
4. Jika gagal, ikuti instruksi troubleshooting yang muncul

---

## 📡 Fitur yang Tersedia

### 1. Check Connection
- **URL:** http://192.168.0.118:8000/api/fingerspot/check-connection
- **Method:** GET
- **Fungsi:** Mengecek apakah web bisa mengakses data absensi dari Fingerspot.io API
- **API yang digunakan:** `get_attlog` dengan parameter `cloud_id`
- **Response (Berhasil):**
  ```json
  {
    "success": true,
    "message": "Koneksi ke Fingerspot.io API berhasil! Mesin terhubung ke webhook.",
    "connected": true,
    "cloud_id": "xxxx",
    "trans_id": "1",
    "data_count": 5,
    "date_range": "2025-12-10 s/d 2025-12-11",
    "info": "Data absensi akan otomatis dikirim via webhook ke aplikasi ini"
  }
  ```
- **Response (Gagal):**
  ```json
  {
    "success": false,
    "message": "Cloud ID tidak ditemukan. Pastikan Cloud ID benar: xxxx",
    "connected": false,
    "http_code": 404
  }
  ```

### 2. Sync Users dari Mesin
- **URL:** http://192.168.0.118:8000/api/fingerspot/sync-users
- **Method:** POST
- **Fungsi:** Mengambil semua user yang terdaftar di mesin fingerprint
- **Response:**
  ```json
  {
    "success": true,
    "message": "Berhasil sync 5 user dan 15 data absensi dari mesin",
    "synced": 5,
    "users": 5,
    "attendances": 15
  }
  ```

### 3. Sync Employee Names (Update Nama Karyawan)
- **URL:** http://192.168.0.118:8000/api/fingerspot/sync-employee-names
- **Method:** POST
- **Fungsi:** Mengambil nama lengkap karyawan dari API `get_userinfo` untuk karyawan yang namanya masih default
- **Kapan digunakan:** Jika nama karyawan masih "Employee 1", "Employee 2", dst
- **Response:**
  ```json
  {
    "success": true,
    "message": "Berhasil update 5 nama karyawan dari Fingerspot.io",
    "updated": 5,
    "failed": 0,
    "total_checked": 5,
    "details": [
      {
        "pin": "1",
        "old_name": "Employee 1",
        "new_name": "Budi Santoso",
        "status": "updated"
      }
    ]
  }
  ```

### 4. Get User Info (Per Karyawan)
- **URL:** http://192.168.0.118:8000/api/fingerspot/user-info/{pin}
- **Method:** GET
- **Fungsi:** Mengambil informasi lengkap satu karyawan berdasarkan PIN
- **Contoh:** http://192.168.0.118:8000/api/fingerspot/user-info/1
- **Response:**
  ```json
  {
    "success": true,
    "data": {
      "trans_id": "1",
      "success": true,
      "data": {
        "pin": "1",
        "name": "Budi Santoso",
        "personname": "Budi Santoso"
      }
    }
  }
  ```

### 5. Webhook Receiver
- **URL:** http://192.168.0.118:8000/api/fingerspot/webhook
- **Method:** ANY (GET/POST)
- **Fungsi:** Menerima data real-time dari Fingerspot.io cloud
- **Data yang diterima:**
  - Attendance/Scanlog (data absensi)
  - User/Person (data karyawan)

---

## 🔧 Troubleshooting

### ⚠️ PENTING: Cara Kerja Fingerspot.io

**Konsep Webhook-Based System:**
- Mesin fingerspot terhubung ke **Fingerspot.io Cloud** (bukan ke Laravel langsung)
- Saat ada absensi, mesin kirim data ke **Fingerspot.io Cloud**
- Cloud Fingerspot.io kemudian **push data via webhook** ke Laravel Anda
- Laravel hanya perlu **menerima webhook** dan menyimpan data

**API `get_attlog`:**
- Digunakan untuk **mengambil riwayat data** yang sudah ada di cloud
- Parameter: `cloud_id`, `start_date`, `end_date`
- Range maksimal: 2 hari per request
- Berguna untuk **sinkronisasi data historis**

**Bukan Real-time Device Connection:**
- Tidak mengecek apakah mesin fisik sedang online/offline
- Hanya mengecek apakah API Fingerspot.io bisa diakses
- Data sudah tersimpan di cloud Fingerspot.io

### ⚠️ Masalah Nama Karyawan "Employee 1, Employee 2, dst"

**Penyebab:**
- Webhook attendance dari Fingerspot.io **tidak menyertakan nama karyawan**
- Hanya menyertakan `pin` (ID karyawan)
- Laravel auto-create employee dengan nama default "Employee {PIN}"

**Solusi:**
1. **Sync Employee Names (Otomatis):**
   ```
   POST http://192.168.0.118:8000/api/fingerspot/sync-employee-names
   ```
   Fungsi ini akan:
   - Mencari semua employee dengan nama "Employee X"
   - Memanggil API `get_userinfo` untuk setiap employee
   - Update nama dengan data asli dari mesin

2. **Get User Info (Manual per karyawan):**
   ```
   GET http://192.168.0.118:8000/api/fingerspot/user-info/{pin}
   ```
   Untuk mengecek informasi satu karyawan tertentu

**Cara Kerja API `get_userinfo`:**
- Endpoint: `https://developer.fingerspot.io/api/get_userinfo`
- Parameter: `trans_id`, `cloud_id`, `pin`
- Response: Data lengkap karyawan termasuk nama

### Error: "Cloud ID tidak ditemukan"
**Penyebab:** Cloud ID salah atau tidak terdaftar
**Solusi:**
1. Cek Cloud ID mesin Anda di dashboard fingerspot.io
2. Pastikan Cloud ID di `.env` sama dengan di dashboard
3. Update `FINGERSPOT_CLOUD_ID` di file `.env`
4. Restart server

### Error: "API Token belum dikonfigurasi"
**Solusi:**
1. Pastikan sudah menambahkan `FINGERSPOT_API_TOKEN` di file `.env`
2. Token tidak boleh kosong
3. Restart Laravel server setelah edit `.env`

### Error: "Gagal terhubung ke Fingerspot.io API (HTTP 401)"
**Penyebab:** Token tidak valid atau expired
**Solusi:**
1. Generate token baru dari developer.fingerspot.io
2. Update token di file `.env`
3. Restart server

### Error: "Account ID tidak ditemukan"
**Penyebab:** Account ID salah atau tidak terdaftar di akun API Token
**Solusi:**
1. Cek Account ID di developer.fingerspot.io → Profile
2. Pastikan Account ID di `.env` sama dengan yang di profile
3. Update `FINGERSPOT_ACCOUNT_ID` di file `.env`
4. Restart server

### Sync Users: "Tidak ada user ditemukan di mesin"
**Penyebab:** 
- Belum ada user terdaftar di mesin
- API Token tidak memiliki akses ke mesin tersebut
**Solusi:**
1. Daftarkan user di mesin fingerprint (scan jari)
2. Pastikan mesin sudah terkoneksi ke Fingerspot.io cloud
3. Cek di web developer.fingerspot.io apakah user muncul

---

## 📝 Catatan Penting

1. **API Token bersifat rahasia** - Jangan share ke orang lain
2. **Cloud ID unik per mesin** - Pastikan sesuai dengan mesin Anda
3. **Webhook harus public** - Untuk localhost, gunakan ngrok
4. **Rate Limit** - API Fingerspot.io mungkin memiliki rate limit, jangan spam request

---

## 🎯 Next Steps

1. ✅ Setup API Token di `.env`
2. ✅ Test koneksi di halaman setup
3. ✅ Sync users dari mesin
4. ✅ Setup webhook URL di developer.fingerspot.io
5. ✅ Test absensi real-time

---

## 📞 Support

- **Dokumentasi API:** https://developer.fingerspot.io/docs
- **Forum Fingerspot:** https://forum.fingerspot.io
- **Email Support:** support@fingerspot.io
