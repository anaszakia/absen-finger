@extends('layouts.app')

@section('title', 'Import Data Absensi')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="mb-6">
            <h2 class="text-2xl font-bold text-gray-800 mb-2">Import Data Absensi dari Mesin</h2>
            <p class="text-gray-600">Upload file hasil export dari mesin absensi Revo W230-N</p>
        </div>

        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                <strong>Berhasil!</strong> {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <strong>Error!</strong> {{ session('error') }}
            </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Form Upload -->
            <div class="border-2 border-dashed border-gray-300 rounded-lg p-6">
                <h3 class="text-lg font-semibold mb-4">1. Upload File</h3>
                
                <form action="{{ route('attendances.import.process') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Pilih File (ATT.DAT, .txt, .csv, .xlsx)
                        </label>
                        <input type="file" name="file" required accept=".dat,.txt,.csv,.xlsx,.xls"
                               class="block w-full text-sm text-gray-500
                                      file:mr-4 file:py-2 file:px-4
                                      file:rounded-md file:border-0
                                      file:text-sm file:font-semibold
                                      file:bg-blue-50 file:text-blue-700
                                      hover:file:bg-blue-100">
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Format File
                        </label>
                        <select name="format" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                            <option value="auto">Auto Detect</option>
                            <option value="zkteco">ZKTeco Format (ATT.DAT)</option>
                            <option value="csv">CSV Standard</option>
                            <option value="excel">Excel (XLSX)</option>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="flex items-center">
                            <input type="checkbox" name="create_employee" value="1" checked class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                            <span class="ml-2 text-sm text-gray-600">Auto-create karyawan baru jika belum ada</span>
                        </label>
                    </div>

                    <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                        <i class="fas fa-upload mr-2"></i>
                        Import Data
                    </button>
                </form>
            </div>

            <!-- Panduan -->
            <div class="bg-blue-50 rounded-lg p-6">
                <h3 class="text-lg font-semibold mb-4 text-blue-800">
                    <i class="fas fa-info-circle mr-2"></i>
                    Cara Export dari Mesin
                </h3>
                
                <ol class="space-y-3 text-sm text-gray-700">
                    <li class="flex items-start">
                        <span class="bg-blue-600 text-white rounded-full w-6 h-6 flex items-center justify-center mr-2 flex-shrink-0">1</span>
                        <span>Siapkan flashdisk format FAT32</span>
                    </li>
                    <li class="flex items-start">
                        <span class="bg-blue-600 text-white rounded-full w-6 h-6 flex items-center justify-center mr-2 flex-shrink-0">2</span>
                        <span>Di mesin, tekan <strong>MENU</strong></span>
                    </li>
                    <li class="flex items-start">
                        <span class="bg-blue-600 text-white rounded-full w-6 h-6 flex items-center justify-center mr-2 flex-shrink-0">3</span>
                        <span>Pilih <strong>USB Disk</strong> → <strong>Download Attendance</strong></span>
                    </li>
                    <li class="flex items-start">
                        <span class="bg-blue-600 text-white rounded-full w-6 h-6 flex items-center justify-center mr-2 flex-shrink-0">4</span>
                        <span>Colokkan flashdisk ke mesin</span>
                    </li>
                    <li class="flex items-start">
                        <span class="bg-blue-600 text-white rounded-full w-6 h-6 flex items-center justify-center mr-2 flex-shrink-0">5</span>
                        <span>Tunggu hingga selesai, ambil flashdisk</span>
                    </li>
                    <li class="flex items-start">
                        <span class="bg-blue-600 text-white rounded-full w-6 h-6 flex items-center justify-center mr-2 flex-shrink-0">6</span>
                        <span>Upload file <code class="bg-white px-1 rounded">ATT.DAT</code> di form sebelah kiri</span>
                    </li>
                </ol>

                <div class="mt-4 p-3 bg-yellow-50 border border-yellow-200 rounded">
                    <p class="text-xs text-yellow-800">
                        <i class="fas fa-exclamation-triangle mr-1"></i>
                        <strong>Catatan:</strong> Jika menu berbeda, cari menu yang mirip seperti "Export Data" atau "Download Data"
                    </p>
                </div>
            </div>
        </div>

        <!-- Format File yang Didukung -->
        <div class="mt-6 border-t pt-6">
            <h3 class="text-lg font-semibold mb-3">Format File yang Didukung</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="border rounded-lg p-4">
                    <h4 class="font-semibold text-sm mb-2">ZKTeco Format (.DAT)</h4>
                    <pre class="text-xs bg-gray-100 p-2 rounded overflow-x-auto">1    2025-12-11 08:00:00    0    0
2    2025-12-11 08:15:00    0    0</pre>
                    <p class="text-xs text-gray-600 mt-2">Format: UserID, DateTime, DeviceID, Status</p>
                </div>

                <div class="border rounded-lg p-4">
                    <h4 class="font-semibold text-sm mb-2">CSV Standard</h4>
                    <pre class="text-xs bg-gray-100 p-2 rounded overflow-x-auto">employee_id,datetime,type
001,2025-12-11 08:00:00,in
001,2025-12-11 17:00:00,out</pre>
                    <p class="text-xs text-gray-600 mt-2">Format: employee_id, datetime, type</p>
                </div>

                <div class="border rounded-lg p-4">
                    <h4 class="font-semibold text-sm mb-2">Excel (.XLSX)</h4>
                    <p class="text-xs text-gray-600">
                        Kolom: <strong>employee_id</strong>, <strong>name</strong>, <strong>date</strong>, <strong>time</strong>
                    </p>
                    <p class="text-xs text-gray-500 mt-2">Support export dari ZKTime software</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
