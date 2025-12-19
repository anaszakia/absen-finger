@extends('layouts.app')

@section('title', 'Setup Fingerspot Webhook')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-2xl font-bold text-gray-800 mb-6">
            <i class="fas fa-fingerprint text-blue-600 mr-2"></i>
            Fingerspot Setup
        </h2>

        <!-- Status Koneksi -->
        <div id="machineStatus" class="bg-gray-50 border-l-4 border-gray-400 p-4 mb-6">
            <div class="flex items-start">
                <i class="fas fa-spinner fa-spin text-gray-600 mt-1 mr-3"></i>
                <div>
                    <h3 class="font-semibold text-gray-800 mb-2">Mengecek koneksi...</h3>
                    <p class="text-sm text-gray-600">Mohon tunggu...</p>
                </div>
            </div>
        </div>

        <!-- Webhook URL -->
        <div class="bg-blue-50 border border-blue-300 rounded-lg p-4 mb-6">
            <label class="block text-sm font-semibold text-gray-700 mb-2">
                <i class="fas fa-link mr-2"></i>Webhook URL:
            </label>
            <div class="flex items-center gap-2">
                <input type="text" readonly 
                       value="{{ url('/api/fingerspot/webhook') }}" 
                       id="webhookUrl"
                       class="flex-1 px-3 py-2 bg-white border border-gray-300 rounded font-mono text-sm">
                <button onclick="copyWebhookUrl()" 
                        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded whitespace-nowrap">
                    <i class="fas fa-copy mr-1"></i>Copy
                </button>
            </div>
            <p class="text-xs text-gray-600 mt-2">
                Paste URL ini di <a href="https://developer.fingerspot.io" target="_blank" class="text-blue-600 hover:underline font-semibold">developer.fingerspot.io</a> → Webhook
            </p>
        </div>

        <!-- Action Buttons -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
            <!-- Reset Data -->
            <div class="border rounded-lg p-4 bg-orange-50 border-orange-300">
                <h3 class="font-semibold text-gray-800 mb-2">
                    <i class="fas fa-trash text-red-600 mr-2"></i>Reset Data
                </h3>
                <p class="text-sm text-gray-600 mb-3">Hapus semua data lama</p>
                <button onclick="confirmClearData()" 
                        id="btnClearData"
                        class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded w-full">
                    <i class="fas fa-trash mr-2"></i>Hapus Semua Data
                </button>
            </div>

            <!-- Sync After Reset -->
            <div class="border rounded-lg p-4 bg-green-50 border-green-300">
                <h3 class="font-semibold text-gray-800 mb-2">
                    <i class="fas fa-sync text-green-600 mr-2"></i>Sync Data Baru
                </h3>
                <p class="text-sm text-gray-600 mb-3">Hanya karyawan baru (filter ketat)</p>
                <button onclick="syncAfterReset()" 
                        id="btnSyncAfterReset"
                        class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded w-full">
                    <i class="fas fa-sync mr-2"></i>Sync Setelah Reset
                </button>
            </div>

            <!-- Sync Today -->
            <div class="border rounded-lg p-4 bg-blue-50 border-blue-300">
                <h3 class="font-semibold text-gray-800 mb-2">
                    <i class="fas fa-download text-blue-600 mr-2"></i>Sync Hari Ini
                </h3>
                <p class="text-sm text-gray-600 mb-3">Ambil semua data hari ini</p>
                <button onclick="syncTodayData()" 
                        id="btnSyncToday"
                        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded w-full">
                    <i class="fas fa-download mr-2"></i>Sync Data Hari Ini
                </button>
            </div>

            <!-- Data Karyawan -->
            <div class="border rounded-lg p-4 bg-purple-50 border-purple-300">
                <h3 class="font-semibold text-gray-800 mb-2">
                    <i class="fas fa-users text-purple-600 mr-2"></i>Lihat Data
                </h3>
                <p class="text-sm text-gray-600 mb-3">Kelola data karyawan & absensi</p>
                <div class="flex gap-2">
                    <a href="{{ route('employees.index') }}" 
                       class="flex-1 bg-purple-600 hover:bg-purple-700 text-white px-3 py-2 rounded text-center text-sm">
                        <i class="fas fa-users mr-1"></i>Karyawan
                    </a>
                    <a href="{{ route('attendances.index') }}" 
                       class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white px-3 py-2 rounded text-center text-sm">
                        <i class="fas fa-clipboard-list mr-1"></i>Absensi
                    </a>
                </div>
            </div>
        </div>

        <!-- Result Area -->
        <div id="resetResult" class="mb-4"></div>

        <!-- Quick Tips -->
        <div class="bg-yellow-50 border border-yellow-300 rounded p-4">
            <p class="text-sm font-semibold text-yellow-800 mb-2">
                <i class="fas fa-lightbulb mr-2"></i>Alur Kerja:
            </p>
            <ol class="text-sm text-gray-700 space-y-1 list-decimal list-inside ml-2">
                <li><strong>Hapus Semua Data</strong> → Clear database lokal</li>
                <li><strong>Registrasi karyawan baru</strong> di mesin fingerprint</li>
                <li><strong>Karyawan scan jari</strong> → Data tercatat di mesin</li>
                <li><strong>Sync Setelah Reset</strong> → Hanya ambil data karyawan BARU</li>
            </ol>
        </div>
    </div>
</div>

<script>
function copyWebhookUrl() {
    const input = document.getElementById('webhookUrl');
    input.select();
    document.execCommand('copy');
    alert('✅ URL webhook berhasil dicopy!');
}

function checkMachineConnection() {
    const statusDiv = document.getElementById('machineStatus');
    
    fetch('{{ url("/api/fingerspot/check-connection") }}')
        .then(response => response.json())
        .then(data => {
            if (data.connected) {
                statusDiv.className = 'bg-green-50 border-l-4 border-green-600 p-4 mb-6';
                statusDiv.innerHTML = `
                    <div class="flex items-start">
                        <i class="fas fa-check-circle text-green-600 mt-1 mr-3"></i>
                        <div>
                            <h3 class="font-semibold text-green-800">✅ Mesin Terkoneksi</h3>
                            <p class="text-sm text-green-700">Cloud ID: <code class="bg-white px-2 py-1 rounded">${data.cloud_id}</code></p>
                        </div>
                    </div>
                `;
            } else {
                statusDiv.className = 'bg-red-50 border-l-4 border-red-600 p-4 mb-6';
                statusDiv.innerHTML = `
                    <div class="flex items-start">
                        <i class="fas fa-exclamation-circle text-red-600 mt-1 mr-3"></i>
                        <div>
                            <h3 class="font-semibold text-red-800">❌ Gagal Terhubung</h3>
                            <p class="text-sm text-red-700">${data.message}</p>
                        </div>
                    </div>
                `;
            }
        })
        .catch(error => {
            statusDiv.className = 'bg-blue-50 border-l-4 border-blue-600 p-4 mb-6';
            statusDiv.innerHTML = `
                <div class="flex items-start">
                    <i class="fas fa-info-circle text-blue-600 mt-1 mr-3"></i>
                    <div>
                        <h3 class="font-semibold text-blue-800">ℹ️ Cek Koneksi Manual</h3>
                        <p class="text-sm text-blue-700">Pastikan mesin aktif dan webhook sudah dikonfigurasi</p>
                    </div>
                </div>
            `;
        });
}

function confirmClearData() {
    if (!confirm('⚠️ PERINGATAN!\n\nHapus SEMUA data karyawan & absensi?\n\nData TIDAK BISA dikembalikan!\n\nLanjutkan?')) {
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
            <i class="fas fa-spinner fa-spin mr-2"></i>Menghapus semua data...
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
        btn.innerHTML = '<i class="fas fa-trash mr-2"></i>Hapus Semua Data';
        
        if (data.success) {
            resultDiv.innerHTML = `
                <div class="bg-green-50 border border-green-300 rounded p-4">
                    <i class="fas fa-check-circle text-green-600 mr-2"></i>
                    <strong>${data.message}</strong>
                    <p class="text-sm text-gray-600 mt-2">
                        Dihapus: ${data.deleted.employees} karyawan, ${data.deleted.attendances} absensi
                    </p>
                    <div class="mt-3 text-sm text-blue-700">
                        ${data.important}
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
        btn.innerHTML = '<i class="fas fa-trash mr-2"></i>Hapus Semua Data';
        resultDiv.innerHTML = `
            <div class="bg-red-50 border border-red-300 rounded p-3">
                <i class="fas fa-times-circle text-red-600 mr-2"></i>
                <strong>Error:</strong> ${error.message}
            </div>
        `;
    });
}

function syncAfterReset() {
    const resultDiv = document.getElementById('resetResult');
    const btn = document.getElementById('btnSyncAfterReset');
    
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Syncing...';
    
    resultDiv.innerHTML = `
        <div class="bg-blue-50 border border-blue-300 rounded p-3">
            <i class="fas fa-spinner fa-spin mr-2"></i>Mengambil data karyawan BARU (filter ketat)...
        </div>
    `;
    
    fetch('{{ url("/api/fingerspot/sync-after-reset") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-sync mr-2"></i>Sync Setelah Reset';
        
        if (data.success) {
            const users = data.synced?.new_users || 0;
            const attendances = data.synced?.new_attendances || 0;
            const skipped = (data.filtered?.skipped_old_pins || 0) + (data.filtered?.skipped_old_data || 0);
            
            resultDiv.innerHTML = `
                <div class="bg-green-50 border border-green-300 rounded p-4">
                    <i class="fas fa-check-circle text-green-600 mr-2"></i>
                    <strong>${data.message}</strong>
                    <div class="grid grid-cols-3 gap-2 mt-3">
                        <div class="bg-white p-2 rounded text-center">
                            <p class="text-xl font-bold text-green-600">${users}</p>
                            <p class="text-xs">Karyawan Baru</p>
                        </div>
                        <div class="bg-white p-2 rounded text-center">
                            <p class="text-xl font-bold text-blue-600">${attendances}</p>
                            <p class="text-xs">Absensi</p>
                        </div>
                        <div class="bg-white p-2 rounded text-center">
                            <p class="text-xl font-bold text-orange-600">${skipped}</p>
                            <p class="text-xs">Data Lama Di-skip</p>
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
        btn.innerHTML = '<i class="fas fa-sync mr-2"></i>Sync Setelah Reset';
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
            <i class="fas fa-spinner fa-spin mr-2"></i>Mengambil data hari ini...
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
                    <i class="fas fa-check-circle text-green-600 mr-2"></i>
                    <strong>${data.message}</strong>
                    <div class="grid grid-cols-2 gap-2 mt-3">
                        <div class="bg-white p-2 rounded text-center">
                            <p class="text-xl font-bold text-blue-600">${users}</p>
                            <p class="text-xs">Karyawan</p>
                        </div>
                        <div class="bg-white p-2 rounded text-center">
                            <p class="text-xl font-bold text-green-600">${attendances}</p>
                            <p class="text-xs">Absensi</p>
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
        btn.innerHTML = '<i class="fas fa-download mr-2"></i>Sync Data Hari Ini';
        resultDiv.innerHTML = `
            <div class="bg-red-50 border border-red-300 rounded p-3">
                <i class="fas fa-times-circle text-red-600 mr-2"></i>
                <strong>Error:</strong> ${error.message}
            </div>
        `;
    });
}

// Auto check connection on page load
window.addEventListener('load', function() {
    checkMachineConnection();
});
</script>
@endsection
