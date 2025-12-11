@extends('layouts.app')

@section('title', 'Mesin Absensi')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-gray-800">Mesin Absensi</h2>
            <div class="flex gap-2">
                <a href="{{ route('machines.push-setup') }}" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg flex items-center gap-2">
                    <i class="fas fa-server"></i>
                    Setup Push Server
                </a>
                <a href="{{ route('machines.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center gap-2">
                    <i class="fas fa-plus"></i>
                    Tambah Mesin
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                {{ session('error') }}
            </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($machines as $machine)
                <div class="border border-gray-200 rounded-lg p-6 hover:shadow-lg transition-shadow">
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <h3 class="text-xl font-bold text-gray-800">{{ $machine->name }}</h3>
                            <p class="text-sm text-gray-500">{{ $machine->location ?? 'Lokasi tidak tersedia' }}</p>
                        </div>
                        @if($machine->is_active)
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                Aktif
                            </span>
                        @else
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                Nonaktif
                            </span>
                        @endif
                    </div>

                    <div class="space-y-2 mb-4">
                        <div class="flex items-center text-sm">
                            <i class="fas fa-network-wired w-6 text-gray-500"></i>
                            <span class="text-gray-700">{{ $machine->ip_address }}:{{ $machine->port }}</span>
                        </div>
                        @if($machine->serial_number)
                            <div class="flex items-center text-sm">
                                <i class="fas fa-barcode w-6 text-gray-500"></i>
                                <span class="text-gray-700">{{ $machine->serial_number }}</span>
                            </div>
                        @endif
                    </div>

                    @if($machine->description)
                        <p class="text-sm text-gray-600 mb-4">{{ Str::limit($machine->description, 100) }}</p>
                    @endif

                    <div class="flex gap-2 flex-wrap">
                        <button onclick="testConnection({{ $machine->id }})" class="flex-1 bg-green-600 hover:bg-green-700 text-white px-3 py-2 rounded text-sm">
                            <i class="fas fa-plug mr-1"></i>Test
                        </button>
                        <a href="{{ route('machines.edit', $machine) }}" class="flex-1 bg-yellow-600 hover:bg-yellow-700 text-white px-3 py-2 rounded text-sm text-center">
                            <i class="fas fa-edit mr-1"></i>Edit
                        </a>
                        <form action="{{ route('machines.destroy', $machine) }}" method="POST" class="flex-1" onsubmit="return confirm('Apakah Anda yakin ingin menghapus mesin ini?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white px-3 py-2 rounded text-sm">
                                <i class="fas fa-trash mr-1"></i>Hapus
                            </button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="col-span-3 text-center py-12">
                    <i class="fas fa-fingerprint text-gray-300 text-6xl mb-4"></i>
                    <p class="text-gray-500">Belum ada mesin absensi terdaftar</p>
                </div>
            @endforelse
        </div>
    </div>
</div>

<script>
function testConnection(machineId) {
    const btn = event.target.closest('button');
    const originalHtml = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i>Testing...';

    fetch(`/machines/${machineId}/test-connection`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Koneksi Berhasil!\n\nVersion: ' + data.data.version + '\nPlatform: ' + data.data.platform);
            } else {
                alert('Koneksi Gagal!\n\n' + data.message);
            }
        })
        .catch(error => {
            alert('Error: ' + error.message);
        })
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = originalHtml;
        });
}
</script>
@endsection
