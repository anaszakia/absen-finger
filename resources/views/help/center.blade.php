@extends('layouts.app')

@section('title', 'Pusat Bantuan')

@section('content')
<div class="max-w-6xl mx-auto">
    <div class="text-center mb-12">
        <h1 class="text-4xl font-bold text-gray-900 mb-4">Pusat Bantuan</h1>
        <p class="text-xl text-gray-600">Bagaimana kami bisa membantu Anda hari ini?</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-12">
        <div class="bg-white rounded-2xl shadow-lg p-8 text-center hover:shadow-xl transition-shadow">
            <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-book text-blue-600 text-2xl"></i>
            </div>
            <h3 class="text-xl font-bold text-gray-900 mb-2">Dokumentasi</h3>
            <p class="text-gray-600">Panduan lengkap penggunaan sistem</p>
        </div>
        
        <div class="bg-white rounded-2xl shadow-lg p-8 text-center hover:shadow-xl transition-shadow">
            <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-video text-green-600 text-2xl"></i>
            </div>
            <h3 class="text-xl font-bold text-gray-900 mb-2">Tutorial Video</h3>
            <p class="text-gray-600">Pelajari melalui video tutorial</p>
        </div>
        
        <div class="bg-white rounded-2xl shadow-lg p-8 text-center hover:shadow-xl transition-shadow">
            <div class="w-16 h-16 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-headset text-purple-600 text-2xl"></i>
            </div>
            <h3 class="text-xl font-bold text-gray-900 mb-2">Live Support</h3>
            <p class="text-gray-600">Hubungi tim support kami</p>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-lg p-8">
        <h2 class="text-2xl font-bold text-gray-900 mb-6">Pertanyaan Umum (FAQ)</h2>
        
        <div class="space-y-4">
            <details class="group">
                <summary class="flex items-center justify-between p-4 bg-gray-50 rounded-lg cursor-pointer hover:bg-gray-100 transition-colors">
                    <span class="font-semibold text-gray-900">Bagaimana cara mengubah password?</span>
                    <i class="fas fa-chevron-down text-gray-600 group-open:rotate-180 transition-transform"></i>
                </summary>
                <div class="p-4 text-gray-600">
                    Anda dapat mengubah password melalui menu Keamanan di halaman profile Anda.
                </div>
            </details>
            
            <details class="group">
                <summary class="flex items-center justify-between p-4 bg-gray-50 rounded-lg cursor-pointer hover:bg-gray-100 transition-colors">
                    <span class="font-semibold text-gray-900">Bagaimana cara mengupdate profil saya?</span>
                    <i class="fas fa-chevron-down text-gray-600 group-open:rotate-180 transition-transform"></i>
                </summary>
                <div class="p-4 text-gray-600">
                    Klik menu Profile di sidebar, kemudian edit informasi yang ingin diubah.
                </div>
            </details>
            
            <details class="group">
                <summary class="flex items-center justify-between p-4 bg-gray-50 rounded-lg cursor-pointer hover:bg-gray-100 transition-colors">
                    <span class="font-semibold text-gray-900">Bagaimana jika lupa password?</span>
                    <i class="fas fa-chevron-down text-gray-600 group-open:rotate-180 transition-transform"></i>
                </summary>
                <div class="p-4 text-gray-600">
                    Gunakan fitur Lupa Password di halaman login, sistem akan mengirimkan kode OTP ke email Anda.
                </div>
            </details>
        </div>
    </div>
</div>
@endsection
