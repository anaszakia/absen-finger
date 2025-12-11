@extends('layouts.app')

@section('title', 'Push Server Setup')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-2xl font-bold text-gray-800 mb-4">
                <i class="fas fa-server mr-2"></i>Push Server Configuration
            </h2>
            <p class="text-gray-600">
                Sistem ini menggunakan metode <strong>Push</strong> dimana mesin fingerprint mengirim data absensi secara otomatis ke server web.
            </p>
        </div>

        <!-- Server Information -->
        <div class="bg-blue-50 border-l-4 border-blue-500 rounded-lg p-6 mb-6">
            <h3 class="text-lg font-semibold text-blue-900 mb-4">
                <i class="fas fa-info-circle mr-2"></i>Informasi Server
            </h3>
            
            <div class="space-y-3">
                <div class="bg-white rounded p-4">
                    <label class="text-sm font-medium text-gray-700">Server URL (untuk setting di mesin):</label>
                    <div class="flex items-center mt-2">
                        <input type="text" id="server-url" value="{{ url('/api/attendance-push/receive') }}" 
                               class="flex-1 px-4 py-2 border border-gray-300 rounded-l-lg bg-gray-50 font-mono text-sm"
                               readonly>
                        <button onclick="copyToClipboard('server-url')" 
                                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-r-lg">
                            <i class="fas fa-copy"></i>
                        </button>
                    </div>
                </div>

                <div class="bg-white rounded p-4">
                    <label class="text-sm font-medium text-gray-700">Server IP Address:</label>
                    <div class="flex items-center mt-2">
                        <input type="text" id="server-ip" value="{{ request()->server('SERVER_ADDR') ?? '192.168.0.118' }}" 
                               class="flex-1 px-4 py-2 border border-gray-300 rounded-l-lg bg-gray-50 font-mono text-sm"
                               readonly>
                        <button onclick="copyToClipboard('server-ip')" 
                                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-r-lg">
                            <i class="fas fa-copy"></i>
                        </button>
                    </div>
                </div>

                <div class="bg-white rounded p-4">
                    <label class="text-sm font-medium text-gray-700">Server Port:</label>
                    <div class="mt-2">
                        <input type="text" value="8000" 
                               class="px-4 py-2 border border-gray-300 rounded-lg bg-gray-50 font-mono text-sm"
                               readonly>
                    </div>
                </div>

                <div class="bg-white rounded p-4">
                    <label class="text-sm font-medium text-gray-700">Test Endpoint:</label>
                    <div class="mt-2">
                        <a href="{{ route('attendance.push.test') }}" target="_blank"
                           class="text-blue-600 hover:text-blue-800 font-mono text-sm">
                            {{ route('attendance.push.test') }}
                            <i class="fas fa-external-link-alt ml-1 text-xs"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Setup Instructions -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">
                <i class="fas fa-cog mr-2"></i>Cara Setting di Mesin Fingerprint
            </h3>

            <div class="space-y-4">
                <div class="border-l-4 border-green-500 pl-4">
                    <h4 class="font-semibold text-gray-800 mb-2">Step 1: Masuk ke Menu Setting Mesin</h4>
                    <p class="text-gray-600 text-sm">Tekan tombol <kbd class="px-2 py-1 bg-gray-200 rounded">MENU</kbd> di mesin fingerprint</p>
                </div>

                <div class="border-l-4 border-green-500 pl-4">
                    <h4 class="font-semibold text-gray-800 mb-2">Step 2: Pilih Communication / Comm</h4>
                    <p class="text-gray-600 text-sm">Cari menu "Communication" atau "Comm" atau "Jaringan"</p>
                </div>

                <div class="border-l-4 border-green-500 pl-4">
                    <h4 class="font-semibold text-gray-800 mb-2">Step 3: Setting Server IP</h4>
                    <ul class="text-gray-600 text-sm space-y-1 ml-4 list-disc">
                        <li><strong>Server IP</strong>: <code class="bg-gray-100 px-2 py-1 rounded">{{ request()->server('SERVER_ADDR') ?? '192.168.0.118' }}</code></li>
                        <li><strong>Server Port</strong>: <code class="bg-gray-100 px-2 py-1 rounded">8000</code></li>
                        <li><strong>Server Req</strong>: Aktifkan/Ya/Enable</li>
                    </ul>
                </div>

                <div class="border-l-4 border-green-500 pl-4">
                    <h4 class="font-semibold text-gray-800 mb-2">Step 4: Setting Upload Mode</h4>
                    <p class="text-gray-600 text-sm">Cari opsi "Upload Mode" atau "Push Mode" dan pilih:</p>
                    <ul class="text-gray-600 text-sm space-y-1 ml-4 list-disc mt-2">
                        <li>Real-time (data langsung terkirim saat absen)</li>
                        <li>Atau Interval (data terkirim setiap X menit)</li>
                    </ul>
                </div>

                <div class="border-l-4 border-green-500 pl-4">
                    <h4 class="font-semibold text-gray-800 mb-2">Step 5: Simpan & Restart</h4>
                    <p class="text-gray-600 text-sm">Simpan setting dan restart mesin fingerprint</p>
                </div>

                <div class="border-l-4 border-yellow-500 pl-4">
                    <h4 class="font-semibold text-gray-800 mb-2">
                        <i class="fas fa-exclamation-triangle text-yellow-600 mr-1"></i>
                        Catatan Penting
                    </h4>
                    <ul class="text-gray-600 text-sm space-y-1 ml-4 list-disc">
                        <li>Pastikan mesin dan server dalam satu jaringan yang sama</li>
                        <li>Mesin harus bisa ping ke IP server: <code class="bg-gray-100 px-2 py-1 rounded">{{ request()->server('SERVER_ADDR') ?? '192.168.0.118' }}</code></li>
                        <li>Port 8000 harus terbuka di firewall server</li>
                        <li>Karyawan harus sudah terdaftar di sistem sebelum absen di mesin</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Test Connection -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">
                <i class="fas fa-check-circle mr-2"></i>Test Koneksi Push Server
            </h3>
            
            <div class="space-y-4">
                <button onclick="testPushServer()" class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg w-full">
                    <i class="fas fa-play mr-2"></i>Test Push Server
                </button>

                <div id="test-result" class="hidden"></div>
            </div>
        </div>

        <!-- Recent Logs -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">
                <i class="fas fa-list mr-2"></i>Status & Monitoring
            </h3>
            
            <div class="bg-gray-50 rounded p-4">
                <p class="text-sm text-gray-600 mb-2">
                    <i class="fas fa-info-circle text-blue-500 mr-1"></i>
                    Server push aktif dan siap menerima data dari mesin fingerprint.
                </p>
                <p class="text-sm text-gray-600">
                    Data yang masuk akan otomatis disimpan ke database dan bisa dilihat di menu <strong>Rekap Absensi</strong>.
                </p>
            </div>

            <div class="mt-4">
                <a href="{{ route('attendances.index') }}" class="text-blue-600 hover:text-blue-800">
                    <i class="fas fa-arrow-right mr-1"></i>Lihat Rekap Absensi
                </a>
            </div>
        </div>
    </div>
</div>

<script>
function copyToClipboard(elementId) {
    const element = document.getElementById(elementId);
    element.select();
    document.execCommand('copy');
    
    // Show feedback
    const btn = element.nextElementSibling;
    const originalHTML = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-check"></i>';
    setTimeout(() => {
        btn.innerHTML = originalHTML;
    }, 2000);
}

function testPushServer() {
    const resultDiv = document.getElementById('test-result');
    resultDiv.innerHTML = '<div class="text-center py-4"><i class="fas fa-spinner fa-spin text-blue-600"></i> Testing...</div>';
    resultDiv.classList.remove('hidden');

    fetch('{{ route('attendance.push.test') }}')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                resultDiv.innerHTML = `
                    <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                        <div class="flex items-center mb-2">
                            <i class="fas fa-check-circle text-green-600 text-2xl mr-3"></i>
                            <h4 class="font-semibold text-green-900">Server Aktif!</h4>
                        </div>
                        <div class="ml-11 text-sm text-gray-700">
                            <p>Server Time: ${data.server_time}</p>
                            <p>Server IP: ${data.server_ip}</p>
                            <p>Your IP: ${data.client_ip}</p>
                        </div>
                    </div>
                `;
            } else {
                resultDiv.innerHTML = `
                    <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                        <div class="flex items-center">
                            <i class="fas fa-times-circle text-red-600 text-2xl mr-3"></i>
                            <h4 class="font-semibold text-red-900">Server Error</h4>
                        </div>
                    </div>
                `;
            }
        })
        .catch(error => {
            resultDiv.innerHTML = `
                <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                    <div class="flex items-center">
                        <i class="fas fa-times-circle text-red-600 text-2xl mr-3"></i>
                        <div>
                            <h4 class="font-semibold text-red-900">Connection Failed</h4>
                            <p class="text-sm text-red-700 ml-0 mt-1">${error.message}</p>
                        </div>
                    </div>
                </div>
            `;
        });
}
</script>

<style>
kbd {
    font-family: monospace;
    font-size: 0.875rem;
}
</style>
@endsection
