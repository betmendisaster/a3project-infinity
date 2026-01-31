@extends('layouts.absensi')

@section('content')
    <div class="pb-20">
        <div class="relative flex flex-col items-center bg-white shadow-lg rounded-lg p-4 w-full max-w-xs md:max-w-md lg:max-w-lg mx-auto">
            <!-- Page Header -->
            <div class="bg-gradient-to-r from-teal-500 to-blue-500 w-full rounded-t-lg p-4 flex flex-col items-center relative">
                <div class="text-white">
                    <p class="text-sm font-medium">PT. Hasnur Riung Sinergi</p>
                    <h2 class="text-lg font-bold">Bugar Selamat</h2>
                    <p class="text-sm">Isi data kesehatan Anda sebelum melakukan presensi masuk.</p>
                </div>
            </div>

            <!-- Page Body -->
            <div class="w-full p-4">
                <div class="bg-white shadow-md rounded-lg p-4">
                    <!-- Pesan Alerts -->
                    <div id="alert-container">
                        @if(session('success'))
                            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4 alert-message">
                                {{ session('success')}}
                            </div>
                        @endif
                        @if(session('warning'))
                            <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded mb-4 alert-message">
                                {{ session('warning') }}
                            </div>
                        @endif
                        @if(session('info'))
                            <div class="bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded mb-4 alert-message">
                                {{ session('info') }}
                            </div>
                        @endif
                        @if(session('error'))
                            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4 alert-message">
                                {{ session('error') }}
                            </div>
                        @endif
                    </div>

                    <form id="bugarForm" action="/presensi/store-bugar-selamat" method="POST" class="space-y-4">
                        @csrf
                        <div>
                            <label for="jam_tidur" class="block text-sm font-medium text-gray-700">Berapa jam tidur Anda semalam?</label>
                            <select name="jam_tidur" id="jam_tidur"
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm
                                focus:ring-teal-500 focus:border-teal-500"
                            required>
                            <option value="">-- Pilih Jam Tidur --</option>
                            @for ($i = 1; $i <= 24; $i++)
                                <option value="{{ $i }}">{{ $i }} Jam</option>
                            @endfor
                        </select>
                    </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Apakah Anda sedang minum obat?</label>
                            <div class="mt-2 space-y-2">
                                <div class="flex items-center">
                                    <input type="radio" name="minum_obat" value="ya" id="ya" class="focus:ring-teal-500 h-4 w-4 text-teal-600 border-gray-300" required>
                                    <label for="ya" class="ml-2 block text-sm text-gray-900">Ya</label>
                                </div>
                                <div class="flex items-center">
                                    <input type="radio" name="minum_obat" value="tidak" id="tidak" class="focus:ring-teal-500 h-4 w-4 text-teal-600 border-gray-300" required>
                                    <label for="tidak" class="ml-2 block text-sm text-gray-900">Tidak</label>
                                </div>
                            </div>
                        </div>

                        <div>
                            <button type="submit" class="w-full bg-gradient-to-r from-teal-500 to-blue-500 text-white font-bold py-2 px-4 rounded-lg hover:from-teal-600 hover:to-blue-600 transition duration-300">
                                Simpan dan Lanjut Presensi
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Audio Elements untuk Suara -->
    <audio id="success-sound">
        <source src="{{ asset('assets/sound/in.wav') }}" type="audio/mpeg">
    </audio>
    <audio id="error-sound">
        <source src="{{ asset('assets/sound/out.wav') }}" type="audio/mpeg">
    </audio>
@endsection

@push('myscript')
    <script>
        $(document).ready(function() {
            // AJAX Submit untuk Form Bugar Selamat
            $('#bugarForm').on('submit', function(e) {
                e.preventDefault(); // Mencegah reload halaman

                $.ajax({
                    type: 'POST',
                    url: '/presensi/store-bugar-selamat',
                    data: $('#bugarForm').serialize(), // Gunakan serialize untuk data form sederhana
                    success: function(response) {
                        if (response && response.success) {
                            $('#alert-container').html('<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4 alert-message">' + response.success + '</div>');
                            $('#success-sound')[0].play();
                            setTimeout(function() {
                                window.location.href = '/presensi/create';
                            }, 3000);
                        } else {
                            $('#alert-container').html('<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4 alert-message">Terjadi kesalahan: Response tidak valid. Silakan coba lagi.</div>');
                            $('#error-sound')[0].play();
                        }
                    },
                    error: function(xhr) {
                        var errorMessage = 'Terjadi kesalahan. Silakan coba lagi.';
                        if (xhr.responseJSON && xhr.responseJSON.error) {
                            errorMessage = xhr.responseJSON.error;
                        } else if (xhr.status === 422) {
                            errorMessage = 'Input tidak valid. Periksa kembali data Anda.';
                        } else if (xhr.status === 500) {
                            errorMessage = 'Kesalahan server. Silakan hubungi admin.';
                        }
                        $('#alert-container').html('<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4 alert-message">' + errorMessage + '</div>');
                        $('#error-sound')[0].play();
                    }
                });
            });
        });
    </script>
@endpush