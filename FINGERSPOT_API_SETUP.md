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
2. Cari baris `FINGERSPOT_API_TOKEN=`
3. Paste token yang sudah dicopy:
   ```
   FINGERSPOT_API_TOKEN=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...
   ```
4. Save file `.env`

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
- **Fungsi:** Mengecek apakah web bisa terhubung ke Fingerspot.io API
- **Response:**
  ```json
  {
    "success": true,
    "message": "Koneksi ke Fingerspot.io API berhasil!",
    "connected": true,
    "cloud_id": "C263045107E1C26"
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
    "message": "Berhasil sinkronisasi 15 user dari mesin Fingerspot",
    "synced": 15
  }
  ```

### 3. Webhook Receiver
- **URL:** http://192.168.0.118:8000/api/fingerspot/webhook
- **Method:** ANY (GET/POST)
- **Fungsi:** Menerima data real-time dari Fingerspot.io cloud
- **Data yang diterima:**
  - Attendance/Scanlog (data absensi)
  - User/Person (data karyawan)

---

## 🔧 Troubleshooting

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

### Error: "Gagal terhubung ke Fingerspot.io API (HTTP 404)"
**Penyebab:** Endpoint API salah atau Cloud ID tidak valid
**Solusi:**
1. Pastikan Cloud ID benar: `C263045107E1C26`
2. Cek apakah mesin sudah terdaftar di developer.fingerspot.io

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
