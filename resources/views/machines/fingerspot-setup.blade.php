@extends('layouts.app')

@section('title', 'Setup Fingerspot Webhook')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-2xl font-bold text-gray-800 mb-6">
            <i class="fas fa-fingerprint text-blue-600 mr-2"></i>
            Setup Webhook Fingerspot.io
        </h2>

        <!-- Info Mesin -->
        <div id="machineStatus" class="bg-gray-50 border-l-4 border-gray-400 p-4 mb-6">
            <div class="flex items-start">
                <i class="fas fa-spinner fa-spin text-gray-600 mt-1 mr-3"></i>
                <div>
                    <h3 class="font-semibold text-gray-800 mb-2">Mengecek koneksi ke mesin...</h3>
                    <p class="text-sm text-gray-600">Mohon tunggu...</p>
                </div>
            </div>
        </div>

        <!-- Step by Step -->
        <div class="space-y-6">
            <!-- Step 1 -->
            <div class="border rounded-lg p-5">
                <div class="flex items-start">
                    <div class="bg-blue-600 text-white rounded-full w-8 h-8 flex items-center justify-center font-bold mr-4 flex-shrink-0">
                        1
                    </div>
                    <div class="flex-1">
                        <h3 class="text-lg font-semibold mb-3">Login ke Developer Fingerspot.io</h3>
                        <p class="text-gray-600 mb-3">Buka <a href="https://developer.fingerspot.io" target="_blank" class="text-blue-600 hover:underline font-semibold">developer.fingerspot.io</a> dan login dengan akun Anda</p>
                        <div class="bg-gray-50 p-3 rounded">
                            <p class="text-sm text-gray-700">Jika belum punya akun, daftar terlebih dahulu di website Fingerspot.io</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Step 2 -->
            <div class="border rounded-lg p-5">
                <div class="flex items-start">
                    <div class="bg-blue-600 text-white rounded-full w-8 h-8 flex items-center justify-center font-bold mr-4 flex-shrink-0">
                        2
                    </div>
                    <div class="flex-1">
                        <h3 class="text-lg font-semibold mb-3">Buka Menu Webhook</h3>
                        <p class="text-gray-600 mb-3">Di dashboard developer.fingerspot.io, klik menu <strong>Webhook</strong> di sidebar</p>
                    </div>
                </div>
            </div>

            <!-- Step 3 -->
            <div class="border rounded-lg p-5 border-green-300 bg-green-50">
                <div class="flex items-start">
                    <div class="bg-green-600 text-white rounded-full w-8 h-8 flex items-center justify-center font-bold mr-4 flex-shrink-0">
                        3
                    </div>
                    <div class="flex-1">
                        <h3 class="text-lg font-semibold mb-3">Konfigurasi Webhook URL</h3>
                        <p class="text-gray-600 mb-4">Masukkan URL webhook berikut:</p>
                        
                        <div class="bg-white border-2 border-green-400 rounded-lg p-4 mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Webhook URL:</label>
                            <div class="flex items-center gap-2">
                                <input type="text" readonly 
                                       value="{{ url('/api/fingerspot/webhook') }}" 
                                       id="webhookUrl"
                                       class="flex-1 px-4 py-2 bg-gray-50 border border-gray-300 rounded-lg font-mono text-sm">
                                <button onclick="copyWebhookUrl()" 
                                        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg whitespace-nowrap">
                                    <i class="fas fa-copy mr-2"></i>Copy
                                </button>
                            </div>
                        </div>

                <div class="bg-yellow-50 border border-yellow-300 rounded p-3">
                    <p class="text-sm text-yellow-800">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        <strong>Penting:</strong> 
                    </p>
                    <ul class="list-disc list-inside text-sm text-yellow-800 ml-4 mt-2">
                        <li>URL ini harus bisa diakses dari internet (public)</li>
                        <li>Jika menggunakan localhost, <strong>WAJIB gunakan ngrok</strong></li>
                        <li>Cloud ID harus terdaftar di akun API Token Anda</li>
                        <li>Cek di developer.fingerspot.io → Devices apakah mesin sudah muncul</li>
                    </ul>
                </div>
                    </div>
                </div>
            </div>

            <!-- Step 4 -->
            <div class="border rounded-lg p-5">
                <div class="flex items-start">
                    <div class="bg-blue-600 text-white rounded-full w-8 h-8 flex items-center justify-center font-bold mr-4 flex-shrink-0">
                        4
                    </div>
                    <div class="flex-1">
                        <h3 class="text-lg font-semibold mb-3">Pilih Event yang Akan Dikirim</h3>
                        <p class="text-gray-600 mb-3">Centang event berikut:</p>
                        <ul class="space-y-2">
                            <li class="flex items-center">
                                <i class="fas fa-check-circle text-green-600 mr-2"></i>
                                <span><strong>Attendance / Scanlog</strong> - Untuk data absensi</span>
                            </li>
                            <li class="flex items-center">
                                <i class="fas fa-check-circle text-green-600 mr-2"></i>
                                <span><strong>User / Person</strong> - Untuk data karyawan</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Step 5 -->
            <div class="border rounded-lg p-5 bg-purple-50">
                <div class="flex items-start">
                    <div class="bg-purple-600 text-white rounded-full w-8 h-8 flex items-center justify-center font-bold mr-4 flex-shrink-0">
                        5
                    </div>
                    <div class="flex-1">
                        <h3 class="text-lg font-semibold mb-3">Cara Mendapatkan Data User dari Mesin</h3>
                        
                        <div class="bg-white border border-purple-300 rounded p-4 mb-4">
                            <p class="font-semibold text-purple-800 mb-2">
                                <i class="fas fa-info-circle mr-2"></i>Penting: Fingerspot.io Menggunakan Sistem PUSH (Webhook)
                            </p>
                            <p class="text-sm text-gray-700 mb-3">
                                Berbeda dengan sistem konvensional, Fingerspot.io tidak menyediakan endpoint untuk "menarik" (pull) data user secara langsung. 
                                Data akan <strong>otomatis dikirim ke server Anda via webhook</strong> saat ada aktivitas di mesin.
                            </p>
                            
                            <div class="bg-purple-50 p-3 rounded">
                                <p class="font-semibold text-sm mb-2">3 Cara Mendapatkan Data User:</p>
                                <ol class="list-decimal list-inside text-sm text-gray-700 space-y-1">
                                    <li><strong>Scan Jari di Mesin</strong> - Saat karyawan absen, data otomatis masuk ke sistem</li>
                                    <li><strong>Trigger Webhook</strong> - Centang event "User/Person" di developer.fingerspot.io</li>
                                    <li><strong>Input Manual</strong> - Tambahkan karyawan di menu Data Karyawan</li>
                                </ol>
                            </div>
                        </div>
                        
                        <div class="flex gap-3 flex-wrap">
                            <button onclick="checkExistingData()" 
                                    id="btnCheckData"
                                    class="bg-purple-600 hover:bg-purple-700 text-white px-6 py-2 rounded-lg">
                                <i class="fas fa-database mr-2"></i>Cek Data yang Sudah Masuk
                            </button>
                            
                            <button onclick="testWebhook()" 
                                    class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg">
                                <i class="fas fa-vial mr-2"></i>Test Webhook
                            </button>
                            
                            <a href="{{ route('employees.index') }}" 
                               class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg inline-block">
                                <i class="fas fa-users mr-2"></i>Data Karyawan
                            </a>
                            
                            <a href="{{ route('attendances.index') }}" 
                               class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg inline-block">
                                <i class="fas fa-list mr-2"></i>Data Absensi
                            </a>
                        </div>
                        
                        <div id="syncResult" class="mt-4"></div>
                    </div>
                </div>
            </div>

            <!-- Step 6: Reset & Sync Fresh Data -->
            <div class="border rounded-lg p-5 bg-orange-50 border-orange-300">
                <div class="flex items-start">
                    <div class="bg-orange-600 text-white rounded-full w-8 h-8 flex items-center justify-center font-bold mr-4 flex-shrink-0">
                        6
                    </div>
                    <div class="flex-1">
                        <h3 class="text-lg font-semibold mb-3">
                            <i class="fas fa-sync-alt mr-2"></i>Reset Data Lama & Tarik Data Baru
                        </h3>
                        
                        <div class="bg-red-50 border border-red-300 rounded p-4 mb-4">
                            <p class="font-semibold text-red-800 mb-2">
                                <i class="fas fa-exclamation-triangle mr-2"></i>Kapan Perlu Reset Data?
                            </p>
                            <ul class="text-sm text-gray-700 space-y-1 list-disc list-inside ml-2">
                                <li>Setelah menghapus semua data di mesin fingerprint</li>
                                <li>Setelah registrasi ulang karyawan baru di mesin</li>
                                <li>Data lama masih muncul padahal sudah dihapus di mesin</li>
                                <li>Ingin mulai fresh dengan data baru</li>
                            </ul>
                        </div>

                        <div class="bg-white border border-orange-300 rounded p-4 mb-4">
                            <p class="font-semibold text-orange-800 mb-3">
                                <i class="fas fa-tasks mr-2"></i>Langkah-langkah Reset:
                            </p>
                            
                            <div class="space-y-3">
                                <div class="bg-gray-50 p-3 rounded border-l-4 border-orange-400">
                                    <p class="font-semibold text-sm mb-1">1️⃣ Hapus Semua Data Lama di Database</p>
                                    <p class="text-xs text-gray-600 mb-2">
                                        Menghapus semua data karyawan dan absensi yang tersimpan di aplikasi
                                    </p>
                                    <button onclick="confirmClearData()" 
                                            id="btnClearData"
                                            class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded text-sm">
                                        <i class="fas fa-trash mr-2"></i>Hapus Semua Data Lokal
                                    </button>
                                </div>

                                <div class="bg-gray-50 p-3 rounded border-l-4 border-green-400">
                                    <p class="font-semibold text-sm mb-1">2️⃣ Registrasi Karyawan Baru di Mesin</p>
                                    <p class="text-xs text-gray-600">
                                        Daftarkan karyawan baru di mesin fingerprint, pastikan registrasi berhasil
                                    </p>
                                </div>

                                <div class="bg-gray-50 p-3 rounded border-l-4 border-blue-400">
                                    <p class="font-semibold text-sm mb-1">3️⃣ Minta Karyawan Scan Jari</p>
                                    <p class="text-xs text-gray-600 mb-2">
                                        Karyawan baru scan jari → Data otomatis masuk via webhook (real-time)
                                    </p>
                                    <div class="bg-yellow-50 border border-yellow-300 rounded p-2 mt-2">
                                        <p class="text-xs font-semibold text-yellow-800">
                                            ⚠️ JANGAN gunakan "Sync Data Hari Ini" setelah clear!
                                        </p>
                                        <p class="text-xs text-gray-700 mt-1">
                                            API sync mengambil data dari cache server yang masih ada data lama. 
                                            <strong>Tunggu webhook</strong> saja untuk data real-time dari mesin.
                                        </p>
                                    </div>
                                </div>
                                
                                <div class="bg-gray-50 p-3 rounded border-l-4 border-purple-400">
                                    <p class="font-semibold text-sm mb-1">4️⃣ Cek & Bersihkan Data (Opsional)</p>
                                    <p class="text-xs text-gray-600 mb-2">
                                        Jika masih ada karyawan lama yang masuk, hapus manual dari menu Data Karyawan
                                    </p>
                                    <a href="{{ route('employees.index') }}" 
                                       class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded text-sm inline-block">
                                        <i class="fas fa-users mr-1"></i>Kelola Data Karyawan
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="bg-red-50 border border-red-300 rounded p-3">
                            <p class="text-sm font-semibold text-red-800 mb-1">
                                <i class="fas fa-exclamation-triangle mr-2"></i>Kenapa Sync Manual Tidak Direkomendasikan?
                            </p>
                            <ul class="text-xs text-gray-700 space-y-1 list-disc list-inside ml-2">
                                <li>Server Fingerspot.io menyimpan cache data beberapa hari</li>
                                <li>Data cache tidak bisa dibedakan mana yang lama, mana yang baru</li>
                                <li>Timestamp di cache adalah waktu server, bukan waktu scan di mesin</li>
                                <li><strong>Solusi:</strong> Tunggu webhook (real-time) atau hapus manual</li>
                            </ul>
                        </div>

                        <div id="resetResult" class="mt-4"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Testing Section -->
        <div class="mt-8 border-t pt-6">
            <h3 class="text-lg font-semibold mb-4">Test Koneksi</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="border rounded-lg p-4">
                    <h4 class="font-semibold mb-2">Webhook Endpoint Status</h4>
                    <div id="webhookStatus" class="text-sm text-gray-600">
                        Klik "Test Endpoint" untuk mengecek status
                    </div>
                </div>

                <div class="border rounded-lg p-4">
                    <h4 class="font-semibold mb-2">Webhook Logs (Latest)</h4>
                    <div class="text-sm">
                        <a href="{{ url('/admin/logs/fingerspot') }}" class="text-blue-600 hover:underline">
                            Lihat semua logs →
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Troubleshooting -->
        <div class="mt-8 bg-gray-50 rounded-lg p-6">
            <h3 class="text-lg font-semibold mb-4">
                <i class="fas fa-wrench mr-2"></i>Troubleshooting
            </h3>
            
            <div class="space-y-3 text-sm">
                <div>
                    <p class="font-semibold text-gray-800">Tidak ada data user yang masuk?</p>
                    <ul class="list-disc list-inside text-gray-600 ml-4 mt-1">
                        <li><strong>Cara tercepat:</strong> Minta 1 karyawan scan jari di mesin</li>
                        <li>Data user akan otomatis dibuat saat ada absensi masuk</li>
                        <li>Pastikan webhook event "Attendance" sudah aktif</li>
                        <li>Cek log webhook di tabel fingerspot_webhook_logs</li>
                        <li><strong>Alternatif:</strong> Input manual di menu Data Karyawan</li>
                    </ul>
                </div>

                <div>
                    <p class="font-semibold text-gray-800">Webhook tidak menerima data?</p>
                    <ul class="list-disc list-inside text-gray-600 ml-4 mt-1">
                        <li>Pastikan URL webhook sudah disimpan di developer.fingerspot.io</li>
                        <li>Cek apakah URL dapat diakses dari internet (tidak localhost)</li>
                        <li>Pastikan event "Attendance" sudah dicentang</li>
                        <li>Coba lakukan absensi di mesin untuk trigger webhook</li>
                    </ul>
                </div>

                <div>
                    <p class="font-semibold text-gray-800">Menggunakan localhost untuk development?</p>
                    <ul class="list-disc list-inside text-gray-600 ml-4 mt-1">
                        <li>Install ngrok: <code class="bg-white px-2 py-1 rounded">ngrok http 8000</code></li>
                        <li>Gunakan URL dari ngrok sebagai webhook URL</li>
                        <li>Contoh: <code class="bg-white px-2 py-1 rounded">https://abc123.ngrok.io/api/fingerspot/webhook</code></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function copyWebhookUrl() {
    const input = document.getElementById('webhookUrl');
    input.select();
    document.execCommand('copy');
    
    alert('URL webhook berhasil dicopy!\nPaste di developer.fingerspot.io → Webhook');
}

function checkMachineConnection() {
    const statusDiv = document.getElementById('machineStatus');
    statusDiv.innerHTML = `
        <div class="flex items-start">
            <i class="fas fa-spinner fa-spin text-gray-600 mt-1 mr-3"></i>
            <div>
                <h3 class="font-semibold text-gray-800 mb-2">Mengecek koneksi ke mesin...</h3>
                <p class="text-sm text-gray-600">Mohon tunggu...</p>
            </div>
        </div>
    `;
    
    fetch('{{ url("/api/fingerspot/check-connection") }}')
        .then(response => response.json())
        .then(data => {
            if (data.connected) {
                statusDiv.className = 'bg-green-50 border-l-4 border-green-600 p-4 mb-6';
                statusDiv.innerHTML = `
                    <div class="flex items-start">
                        <i class="fas fa-check-circle text-green-600 mt-1 mr-3"></i>
                        <div>
                            <h3 class="font-semibold text-green-800 mb-2">✅ Mesin Terkoneksi ke Fingerspot.io</h3>
                            <div class="text-sm text-green-700 space-y-1">
                                <p><strong>Model:</strong> Revo W-230N</p>
                                <p><strong>Cloud ID:</strong> <code class="bg-white px-2 py-1 rounded">${data.cloud_id}</code></p>
                                <p><strong>Status:</strong> <span class="text-green-600 font-semibold">● Online</span></p>
                            </div>
                        </div>
                    </div>
                `;
            } else {
                statusDiv.className = 'bg-red-50 border-l-4 border-red-600 p-4 mb-6';
                statusDiv.innerHTML = `
                    <div class="flex items-start">
                        <i class="fas fa-exclamation-circle text-red-600 mt-1 mr-3"></i>
                        <div>
                            <h3 class="font-semibold text-red-800 mb-2">❌ Gagal Terhubung ke Mesin</h3>
                            <div class="text-sm text-red-700 space-y-2">
                                <p><strong>Error:</strong> ${data.message}</p>
                                ${data.instructions ? `<p class="bg-white p-2 rounded mt-2"><code>${data.instructions}</code></p>` : ''}
                            </div>
                        </div>
                    </div>
                `;
            }
        })
        .catch(error => {
            // Jika pakai ngrok, CORS error adalah normal - skip saja
            statusDiv.className = 'bg-blue-50 border-l-4 border-blue-600 p-4 mb-6';
            statusDiv.innerHTML = `
                <div class="flex items-start">
                    <i class="fas fa-info-circle text-blue-600 mt-1 mr-3"></i>
                    <div>
                        <h3 class="font-semibold text-blue-800 mb-2">ℹ️ Menggunakan Ngrok</h3>
                        <div class="text-sm text-blue-700 space-y-1">
                            <p>Browser tidak bisa mengecek koneksi saat menggunakan ngrok (CORS issue).</p>
                            <p class="mt-2"><strong>Cara Test:</strong></p>
                            <ol class="list-decimal list-inside ml-2">
                                <li>Pastikan ngrok masih running</li>
                                <li>Update webhook URL di developer.fingerspot.io dengan URL ngrok</li>
                                <li>Scan jari di mesin untuk test</li>
                                <li>Cek menu Data Karyawan & Absensi</li>
                            </ol>
                        </div>
                    </div>
                </div>
            `;
        });
}

function checkExistingData() {
    const resultDiv = document.getElementById('syncResult');
    const btn = document.getElementById('btnCheckData');
    
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Checking...';
    
    resultDiv.innerHTML = `
        <div class="bg-blue-50 border border-blue-300 rounded p-3">
            <i class="fas fa-spinner fa-spin mr-2"></i>
            Mengecek data yang sudah masuk dari webhook...
        </div>
    `;
    
    fetch('{{ route("fingerspot.sync-users") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-database mr-2"></i>Cek Data yang Sudah Masuk';
        
        if (data.existing_data) {
            const emp = data.existing_data.total_employees;
            const att = data.existing_data.recent_attendances;
            
            resultDiv.innerHTML = `
                <div class="bg-blue-50 border border-blue-300 rounded p-4">
                    <h4 class="font-semibold mb-3">
                        <i class="fas fa-info-circle text-blue-600 mr-2"></i>
                        Status Data Saat Ini
                    </h4>
                    
                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div class="bg-white p-3 rounded">
                            <p class="text-2xl font-bold text-blue-600">${emp}</p>
                            <p class="text-sm text-gray-600">Total Karyawan</p>
                        </div>
                        <div class="bg-white p-3 rounded">
                            <p class="text-2xl font-bold text-green-600">${att}</p>
                            <p class="text-sm text-gray-600">Absensi Hari Ini</p>
                        </div>
                    </div>
                    
                    <div class="bg-yellow-50 border border-yellow-300 rounded p-3 mb-3">
                        <p class="text-sm font-semibold text-yellow-800 mb-2">
                            <i class="fas fa-lightbulb mr-2"></i>Cara Menambah Data User:
                        </p>
                        <ol class="text-sm text-gray-700 space-y-1 ml-4 list-decimal">
                            <li><strong>Paling Cepat:</strong> Minta karyawan scan jari di mesin → Data otomatis masuk</li>
                            <li><strong>Via Webhook:</strong> Pastikan event "User/Person" aktif di developer.fingerspot.io</li>
                            <li><strong>Manual:</strong> Tambah karyawan di menu <a href="{{ route('employees.index') }}" class="text-blue-600 hover:underline">Data Karyawan</a></li>
                        </ol>
                    </div>
                    
                    <div class="flex gap-2">
                        <a href="{{ route('employees.index') }}" class="text-blue-600 hover:underline text-sm">
                            <i class="fas fa-users mr-1"></i>Lihat Data Karyawan →
                        </a>
                        <a href="{{ route('attendances.index') }}" class="text-blue-600 hover:underline text-sm ml-3">
                            <i class="fas fa-clipboard-list mr-1"></i>Lihat Data Absensi →
                        </a>
                    </div>
                </div>
            `;
        } else {
            resultDiv.innerHTML = `
                <div class="bg-yellow-50 border border-yellow-300 rounded p-3">
                    <i class="fas fa-exclamation-triangle text-yellow-600 mr-2"></i>
                    <strong>${data.message}</strong>
                </div>
            `;
        }
    })
    .catch(error => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-database mr-2"></i>Cek Data yang Sudah Masuk';
        
        resultDiv.innerHTML = `
            <div class="bg-red-50 border border-red-300 rounded p-3">
                <i class="fas fa-times-circle text-red-600 mr-2"></i>
                <strong>Error:</strong> ${error.message}
            </div>
        `;
    });
}

function syncUsersFromMachine() {
    const resultDiv = document.getElementById('syncResult');
    const btn = document.getElementById('btnSyncUsers');
    
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Syncing...';
    
    resultDiv.innerHTML = `
        <div class="bg-blue-50 border border-blue-300 rounded p-3">
            <i class="fas fa-spinner fa-spin mr-2"></i>
            Mengambil data user dari mesin Fingerspot.io...
        </div>
    `;
    
    fetch('{{ route("fingerspot.sync-users") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-sync mr-2"></i>Sync User dari Mesin';
        
        if (data.success) {
            resultDiv.innerHTML = `
                <div class="bg-green-50 border border-green-300 rounded p-3">
                    <i class="fas fa-check-circle text-green-600 mr-2"></i>
                    <strong>${data.message}</strong><br>
                    <span class="text-sm text-gray-600">Total user: ${data.synced}</span><br>
                    <a href="{{ route('employees.index') }}" class="text-blue-600 hover:underline text-sm mt-2 inline-block">
                        Lihat Data Karyawan →
                    </a>
                </div>
            `;
        } else {
            resultDiv.innerHTML = `
                <div class="bg-red-50 border border-red-300 rounded p-3">
                    <i class="fas fa-times-circle text-red-600 mr-2"></i>
                    <strong>Gagal:</strong> ${data.message}
                </div>
            `;
        }
    })
    .catch(error => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-sync mr-2"></i>Sync User dari Mesin';
        
        resultDiv.innerHTML = `
            <div class="bg-red-50 border border-red-300 rounded p-3">
                <i class="fas fa-times-circle text-red-600 mr-2"></i>
                <strong>Error:</strong> ${error.message}
            </div>
        `;
    });
}

function confirmClearData() {
    if (!confirm('⚠️ PERINGATAN!\n\nAnda akan menghapus SEMUA data karyawan dan absensi yang ada di database lokal.\n\nData yang dihapus:\n• Semua data karyawan\n• Semua data absensi\n• Semua webhook logs\n\nData ini TIDAK BISA dikembalikan!\n\nLanjutkan?')) {
        return;
    }
    
    if (!confirm('Apakah Anda YAKIN?\n\nSetelah data dihapus, Anda harus:\n1. Pastikan mesin sudah ada data baru\n2. Sync data hari ini untuk tarik data baru\n\nKlik OK untuk menghapus data.')) {
        return;
    }
    
    clearLocalData();
}

function clearLocalData() {
    const resultDiv = document.getElementById('resetResult');
    const btn = document.getElementById('btnClearData');
    
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Menghapus...';
    
    resultDiv.innerHTML = `
        <div class="bg-yellow-50 border border-yellow-300 rounded p-3">
            <i class="fas fa-spinner fa-spin mr-2"></i>
            Menghapus semua data lokal...
        </div>
    `;
    
    fetch('{{ url("/api/fingerspot/clear-local-data") }}', {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-trash mr-2"></i>Hapus Semua Data Lokal';
        
        if (data.success) {
            resultDiv.innerHTML = `
                <div class="bg-green-50 border border-green-300 rounded p-4">
                    <div class="flex items-start mb-3">
                        <i class="fas fa-check-circle text-green-600 mt-1 mr-2"></i>
                        <div>
                            <p class="font-semibold text-green-800">${data.message}</p>
                            <div class="text-sm text-gray-600 mt-2">
                                <p>Data yang dihapus:</p>
                                <ul class="list-disc list-inside ml-2">
                                    <li>${data.deleted.employees} karyawan</li>
                                    <li>${data.deleted.attendances} data absensi</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="bg-blue-50 border border-blue-300 rounded p-3 mt-3">
                        <p class="text-sm font-semibold text-blue-800 mb-2">
                            <i class="fas fa-arrow-right mr-2"></i>Langkah Selanjutnya:
                        </p>
                        <ol class="text-sm text-gray-700 space-y-1 list-decimal list-inside ml-2">
                            <li>Registrasi karyawan baru di mesin fingerprint</li>
                            <li>Minta karyawan baru scan jari di mesin</li>
                            <li>Data otomatis masuk via webhook (real-time)</li>
                        </ol>
                        <div class="bg-yellow-50 border border-yellow-300 rounded p-2 mt-2">
                            <p class="text-xs font-semibold text-yellow-800">
                                ${data.important || '⚠️ JANGAN gunakan Sync Manual setelah clear'}
                            </p>
                            <p class="text-xs text-gray-700">
                                ${data.reason || 'Tunggu webhook saja untuk data real-time dari mesin'}
                            </p>
                        </div>
                        <div class="flex gap-2 mt-3">
                            <a href="{{ route('employees.index') }}" 
                               class="bg-purple-600 hover:bg-purple-700 text-white px-3 py-2 rounded text-xs">
                                <i class="fas fa-users mr-1"></i>Cek Data Karyawan
                            </a>
                            <a href="{{ route('attendances.index') }}" 
                               class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-2 rounded text-xs">
                                <i class="fas fa-clipboard-list mr-1"></i>Cek Data Absensi
                            </a>
                        </div>
                    </div>
                </div>
            `;
        } else {
            resultDiv.innerHTML = `
                <div class="bg-red-50 border border-red-300 rounded p-3">
                    <i class="fas fa-times-circle text-red-600 mr-2"></i>
                    <strong>Gagal:</strong> ${data.message}
                </div>
            `;
        }
    })
    .catch(error => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-trash mr-2"></i>Hapus Semua Data Lokal';
        
        resultDiv.innerHTML = `
            <div class="bg-red-50 border border-red-300 rounded p-3">
                <i class="fas fa-times-circle text-red-600 mr-2"></i>
                <strong>Error:</strong> ${error.message}
            </div>
        `;
    });
}

function syncTodayData() {
    const resultDiv = document.getElementById('resetResult');
    const btn = document.getElementById('btnSyncToday');
    
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Syncing...';
    
    resultDiv.innerHTML = `
        <div class="bg-blue-50 border border-blue-300 rounded p-3">
            <i class="fas fa-spinner fa-spin mr-2"></i>
            Mengambil data absensi hari ini dari mesin...
        </div>
    `;
    
    fetch('{{ url("/api/fingerspot/sync-today") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-download mr-2"></i>Sync Data Hari Ini';
        
        if (data.success) {
            const users = data.synced?.users || 0;
            const attendances = data.synced?.attendances || 0;
            
            resultDiv.innerHTML = `
                <div class="bg-green-50 border border-green-300 rounded p-4">
                    <div class="flex items-start mb-3">
                        <i class="fas fa-check-circle text-green-600 mt-1 mr-2"></i>
                        <div class="flex-1">
                            <p class="font-semibold text-green-800 mb-2">${data.message}</p>
                            
                            <div class="grid grid-cols-2 gap-3 mb-3">
                                <div class="bg-white p-3 rounded border">
                                    <p class="text-2xl font-bold text-blue-600">${users}</p>
                                    <p class="text-xs text-gray-600">Karyawan Baru</p>
                                </div>
                                <div class="bg-white p-3 rounded border">
                                    <p class="text-2xl font-bold text-green-600">${attendances}</p>
                                    <p class="text-xs text-gray-600">Data Absensi</p>
                                </div>
                            </div>
                            
                            <div class="text-xs text-gray-600 mb-2">
                                <p>📅 Tanggal: ${data.date}</p>
                                <p>📊 Total data diterima: ${data.total_data_received}</p>
                                ${data.skipped_old_data > 0 ? `<p class="text-orange-600">⚠️ Data lama di-skip: ${data.skipped_old_data}</p>` : ''}
                                ${data.info ? `<p class="text-blue-600">ℹ️ ${data.info}</p>` : ''}
                            </div>
                        </div>
                    </div>
                    
                    <div class="flex gap-2 mt-3">
                        <a href="{{ route('employees.index') }}" 
                           class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded text-sm">
                            <i class="fas fa-users mr-1"></i>Lihat Data Karyawan
                        </a>
                        <a href="{{ route('attendances.index') }}" 
                           class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded text-sm">
                            <i class="fas fa-clipboard-list mr-1"></i>Lihat Data Absensi
                        </a>
                    </div>
                </div>
            `;
        } else {
            resultDiv.innerHTML = `
                <div class="bg-red-50 border border-red-300 rounded p-3">
                    <i class="fas fa-times-circle text-red-600 mr-2"></i>
                    <strong>Gagal:</strong> ${data.message}
                </div>
            `;
        }
    })
    .catch(error => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-download mr-2"></i>Sync Data Hari Ini';
        
        resultDiv.innerHTML = `
            <div class="bg-red-50 border border-red-300 rounded p-3">
                <i class="fas fa-times-circle text-red-600 mr-2"></i>
                <strong>Error:</strong> ${error.message}
            </div>
        `;
    });
}

function testWebhook() {
    const statusDiv = document.getElementById('webhookStatus');
    statusDiv.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Testing...';
    
    fetch('{{ route("fingerspot.webhook.test") }}')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                statusDiv.innerHTML = `
                    <div class="text-green-600">
                        <i class="fas fa-check-circle mr-2"></i>
                        <strong>Endpoint Aktif!</strong><br>
                        <span class="text-xs text-gray-600">Server Time: ${data.server_time}</span>
                    </div>
                `;
            } else {
                statusDiv.innerHTML = `
                    <div class="text-red-600">
                        <i class="fas fa-times-circle mr-2"></i>
                        <strong>Error!</strong> ${data.message}
                    </div>
                `;
            }
        })
        .catch(error => {
            statusDiv.innerHTML = `
                <div class="text-red-600">
                    <i class="fas fa-times-circle mr-2"></i>
                    <strong>Connection Error!</strong><br>
                    <span class="text-xs">${error.message}</span>
                </div>
            `;
        });
}

// Auto check connection on page load
window.addEventListener('load', function() {
    checkMachineConnection();
    testWebhook();
});
</script>
@endsection
