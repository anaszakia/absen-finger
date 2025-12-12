# PENTING: Cara Mendapatkan Nama Karyawan dari Fingerspot

## Masalah
Data karyawan menunjukkan "Employee 1", "Employee 2", bukan nama asli dari mesin.

## Penyebab
Fingerspot.io API **TIDAK MENDUKUNG** penarikan data (PULL) dari server.

API yang kami test:
- ✅ `get_all_pin` → Return: `{"success":true,"trans_id":"1"}` (tanpa data PIN)
- ✅ `get_userinfo` → Return: `{"success":true,"trans_id":"1"}` (tanpa data nama)

Kedua API hanya mengembalikan konfirmasi sukses, **BUKAN DATA**.

## Cara Kerja Fingerspot.io (100% PUSH System)

Fingerspot.io menggunakan sistem **WEBHOOK** (Push), bukan polling (Pull):

```
Mesin Fingerspot → Cloud Fingerspot → Webhook Server Kita
```

Data **HANYA** dikirim saat ada aktivitas di mesin:
1. Karyawan scan jari → Webhook kirim data attendance + personname
2. Admin tambah user di mesin → Webhook kirim data user + personname
3. Admin edit user di mesin → Webhook kirim data user update

## ✅ SOLUSI: 3 Cara Mendapatkan Nama Karyawan

### Cara 1: Scan Jari di Mesin (TERCEPAT & OTOMATIS)
```
1. Minta karyawan scan jari di mesin fingerspot
2. Mesin kirim data ke cloud
3. Cloud trigger webhook ke server kita
4. Webhook otomatis create/update employee dengan nama asli
5. Nama langsung muncul di database
```

**Ini cara PALING MUDAH dan RECOMMENDED!**

### Cara 2: Aktifkan Webhook Event "User/Person"
```
1. Login ke https://developer.fingerspot.io
2. Menu Webhook → Centang event "User / Person"
3. Saat admin tambah user di mesin
4. Webhook otomatis kirim data user + nama
```

### Cara 3: Input Manual
```
1. Buka menu Data Karyawan
2. Edit karyawan yang namanya "Employee X"
3. Ganti dengan nama asli
4. Save
```

## Update Webhook Receiver (Sudah Diperbaiki)

File: `app/Http/Controllers/FingerspotWebhookController.php`

Sekarang sudah support parsing nama dari berbagai field:
- `personname` (standard)
- `name`
- `person_name`
- `fullname`
- `full_name`
- `username`

Jadi saat webhook kirim data, nama otomatis terekstrak.

## Testing

### Test 1: Cek Webhook Aktif
```bash
curl http://192.168.0.118:8000/api/fingerspot/webhook/test
```

Expected:
```json
{
  "success": true,
  "message": "Fingerspot webhook endpoint is active"
}
```

### Test 2: Simulasi Webhook (Manual Test)
```bash
curl -X POST http://192.168.0.118:8000/api/fingerspot/webhook \
  -H "Content-Type: application/json" \
  -d '{
    "type": "attendance",
    "cloud_id": "C2630451071E1C26",
    "pin": "123",
    "personname": "John Doe",
    "scan_date": "2025-12-12 10:00:00"
  }'
```

### Test 3: Cek di Menu Karyawan
Setelah scan jari atau webhook test, cek di:
- Menu: **Data Karyawan**
- Nama harusnya berubah dari "Employee X" menjadi nama asli

## Logs untuk Debugging

Cek logs webhook:
```bash
tail -f storage/logs/laravel.log | grep Fingerspot
```

Look for:
- `Auto-created employee from fingerspot`
- `Updated employee name from fingerspot`

## Catatan Penting

❌ **TIDAK BISA:**
- Ambil semua nama karyawan sekaligus dari API
- Poll/pull data dari mesin via API
- Sinkronisasi manual tanpa webhook

✅ **BISA:**
- Terima data otomatis via webhook saat ada scan
- Auto-create/update employee saat webhook masuk
- Manual input jika diperlukan

## Rekomendasi

**Cara TERCEPAT mendapatkan semua nama karyawan:**

1. Pastikan webhook sudah setup di developer.fingerspot.io
2. Minta **semua karyawan scan jari 1x** di mesin hari ini
3. Saat mereka scan, webhook otomatis kirim data
4. Nama otomatis muncul di database
5. Done! ✅

**Estimasi waktu:** 5-10 menit untuk 50 karyawan
