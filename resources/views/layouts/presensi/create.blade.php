@extends('layouts.layoutNoFooter')
@section('header')
    <style>
        .fotoduls,
        .fotoduls video {
            display: inline-block;
            width: 100% !important;
            height: auto !important;
            margin: auto;
            border-radius: 15px;
        }

        #map {
            height: 200px;
        }

        .jam-digital-malasngoding {
            background-color: #46577683;
            width: 100px;
        }

        .jam-digital-malasngoding p {
            color: #fff;
            font-size: 16px;
            font-size: 0.625rem;
            text-align: center;
            margin-top: 0;
            margin-bottom: 0;
        }
    </style>
    {{-- leaflet map --}}
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    {{-- leaflet JS CDN --}}
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
@endsection

@section('content')
    <div class="pb-20">
        {{-- dashboard --}}
        <div class="relative flex flex-col items-center bg-white shadow-lg rounded-lg p-4 w-full max-w-xs md:max-w-md lg:max-w-lg mx-auto">
            <div class="flex justify-around w-full bg-white shadow-md rounded-t-lg py-2 text-[0.5rem] leading-tight">
                <div class="flex flex-col">
                    <h2 class="text-black text-base font-bold">
                        {{ Auth::guard('karyawan')->user()->nama }}</h2>
                    <h2 class="text-black">{{ Auth::guard('karyawan')->user()->nrp }}</h2>
                    <h2 class="text-black">{{ Auth::guard('karyawan')->user()->kode_dept }}</h2>
                    <h2 class="text-black">{{ Auth::guard('karyawan')->user()->jabatan }}</h2>
                    <h2 class="text-black text-[0.7rem] font-bold">Area Absen :
                        {{ Auth::guard('karyawan')->user()->kode_cabang }}</h2>
                </div>
                <div class="flex flex-col items-center mr-8 text-[0.5rem] leading-tight">
                    <div class="jam-digital-malasngoding">
                        <p>{{ date('D') }}</p>
                    </div>
                    <div class="jam-digital-malasngoding">
                        <p id="jam"></p>
                        <p>{{ $jamKerja->nama_jam_kerja }}</p>
                        <p>Jam In : {{ date('H:i', strtotime($jamKerja->jam_masuk)) }}</p>
                        <p>Jam Out : {{ date('H:i', strtotime($jamKerja->jam_pulang)) }}</p>
                        <p></p>
                    </div>
                    {{-- Tombol Ganti Shift dipindahkan ke sini, di bawah jam --}}
                    @if($cek == 0)
                        <button id="btnKonfirmasiShift" class="text-xs bg-red-500 text-white rounded px-2 py-1 mt-2 cursor-pointer hover:bg-red-700 transition duration-300 shadow-sm flex items-center gap-1">
                            <i class="fa-solid fa-arrows-alt-h"></i> Konfirmasi Shift
                        </button>
                    @endif
                </div>
            </div>

            {{-- Webcam js nya --}}
            <div class="bg-white text-gray-800 rounded-t-lg p-2 flex items-center justify-center w-full mx-1 shadow-md mt-3">
                <div class="fotoduls"></div>
            </div>

            <div class="items-center shadow-lg rounded-lg p-4 w-full max-w-xs md:max-w-md lg:max-w-lg mx-auto">
                <div class="col">
                    <div id="map"></div>
                </div>
            </div>
            <!-- lokasi-->
            <div class="bg-white text-gray-800 rounded-b-lg p-2 flex items-center justify-center w-full mx-1 shadow-md mt-3">
                <input type="text" id="lokasi">
            </div>
            {{-- button take absen --}}
            <div class="bg-slate-300 text-gray-800 rounded-b-lg p-1 flex items-center justify-center w-full mx-1 shadow-md">
                @if ($cek > 0)
                    <button class="bg-white text-black rounded-lg p-1 flex flex-col items-center w-1/5 mx-1 cursor-pointer hover:bg-red-800 transition duration-300 shadow-md hover:text-white" id="takeabsen"><i class="fa-solid fa-camera"></i>Out</button>
                @else
                    <button class="bg-white text-black rounded-lg p-1 flex flex-col items-center w-1/5 mx-1 cursor-pointer hover:bg-green-800 transition duration-300 shadow-md hover:text-white" id="takeabsen"><i class="fa-solid fa-camera"></i>In</button>
                @endif
            </div>
        </div>

        {{-- Modal Konfirmasi Shift --}}
        <div id="modalKonfirmasiShift" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
            <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                <div class="mt-3 text-center">
                    <h3 class="text-lg font-medium text-gray-900">Konfirmasi Shift Hari Ini</h3>
                    <p class="text-sm text-gray-600 mt-2">Hari: {{ app('App\Http\Controllers\PresensiController')->getHari() }}, {{ app('App\Http\Controllers\PresensiController')->getTanggalSekarang() }}</p>
                    <div class="mt-4 text-left">
                        <p><strong>Nama Shift:</strong> {{ $jamKerja->nama_jam_kerja }}</p>
                        <p><strong>Jam Masuk:</strong> {{ date('H:i', strtotime($jamKerja->jam_masuk)) }}</p>
                        <p><strong>Jam Pulang:</strong> {{ date('H:i', strtotime($jamKerja->jam_pulang)) }}</p>
                        <p class="mt-4 text-gray-700">Shift pian sudah sesuai kah ? mun belum silakan ganti dulu shift nya</p>
                    </div>
                    <div class="flex justify-center mt-4">
                        <button id="btnSudahBenar" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-700 mr-2">Ya, Sudah Benar</button>
                        <button id="btnBukaGantiShift" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-700">Ganti Shift</button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Modal Ganti Shift --}}
        <div id="modalGantiShift" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
            <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                <div class="mt-3 text-center">
                    <h3 class="text-lg font-medium text-gray-900">Ganti Shift (Hari Ini: {{ app('App\Http\Controllers\PresensiController')->getHari() }}, {{ app('App\Http\Controllers\PresensiController')->getTanggalSekarang() }})</h3>
                    <form id="formGantiShift" class="mt-4">
                        @csrf
                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2">Pilih Shift</label>
                            <select name="kode_jam_kerja" id="kode_jam_kerja" class="form-select w-full border border-gray-300 rounded px-3 py-2" required>
                                <option value="">Pilih Shift</option>
                                @php
                                    $hariSekarang = app('App\Http\Controllers\PresensiController')->getHari();
                                    $currentShift = DB::table('settings_jam_kerja')->where('nrp', Auth::guard('karyawan')->user()->nrp)->where('hari', $hariSekarang)->first();
                                    $allJamKerja = DB::table('jam_kerja')->orderBy('nama_jam_kerja')->get();  // Ambil semua shift yang tersedia
                                @endphp
                                @foreach($allJamKerja as $jk)  // Loop semua shift
                                    <option value="{{ $jk->kode_jam_kerja }}" {{ isset($currentShift) && $currentShift->kode_jam_kerja == $jk->kode_jam_kerja ? 'selected' : '' }}>
                                        {{ $jk->nama_jam_kerja }} ({{ $jk->jam_masuk }} - {{ $jk->jam_pulang }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="flex justify-center">
                            <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-700 mr-2">Simpan</button>
                            <button type="button" id="btnCloseModal" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-700">Lanjutkan Absen</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Notif / AUDIO MASTER --}}
        <audio id='notifikasi_in'>
            <source src="{{ asset('assets/sound/in.wav') }}" type="audio/wav">
        </audio>
        <audio id="notifikasi_out">
            <source src="{{ asset('assets/sound/out.wav') }}" type="audio/wav">
        </audio>
        <audio id="radius_sound">
            <source src="{{ asset('assets/sound/errorRadius.mp3') }}" type="audio/mpeg">
        </audio>
    </div>
@endsection

@push('myscript')
    <script>
        window.onload = function() {
            jam();
        }

        function jam() {
            var e = document.getElementById('jam'),
                d = new Date(),
                h, m, s;
            h = d.getHours();
            m = set(d.getMinutes());
            s = set(d.getSeconds());

            e.innerHTML = h + ':' + m + ':' + s;

            setTimeout('jam()', 1000);
        }

        function set(e) {
            e = e < 10 ? '0' + e : e;
            return e;
        }

        var notifikasi_in = document.getElementById('notifikasi_in');
        var notifikasi_out = document.getElementById('notifikasi_out');
        var radius_sound = document.getElementById('radius_sound');

        Webcam.set({
            height: 480,
            width: 640,
            image_format: 'jpeg',
            jpeg_quality: 80,
            mobileAutoAdvance: true,
            flip_horiz: true
        });
        Webcam.attach('.fotoduls');

        var lokasi = document.getElementById('lokasi');
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(successCallback, errorCallback);
        }

        function successCallback(posisi) {
            lokasi.value = posisi.coords.latitude + ',' + posisi.coords.longitude;
            var map = L.map('map').setView([posisi.coords.latitude, posisi.coords.longitude], 15);

            var lokasi_site = "{{ $lok_site->lokasi_cabang }}";
            var lok = lokasi_site.split(",");
            var lat_site = lok[0];
            var long_site = lok[1];
            var radius = {{ $lok_site->radius_cabang }};

            googleStreets = L.tileLayer('http://{s}.google.com/vt/lyrs=m&x={x}&y={y}&z={z}', {
                maxZoom: 20,
                subdomains: ['mt0', 'mt1', 'mt2', 'mt3']
            }).addTo(map);
            var marker = L.marker([posisi.coords.latitude, posisi.coords.longitude]).addTo(map);
            var circle = L.circle([lat_site, long_site], {
                color: 'red',
                fillColor: '#f03',
                fillOpacity: 0.5,
                radius: radius
            }).addTo(map);
        }

        function errorCallback() {}

        // take absen ajax
        $("#takeabsen").click(function(e) {
            Webcam.snap(function(uri) {
                image = uri;
            });
            var lokasi = $("#lokasi").val();
            $.ajax({
                type: 'POST',
                url: '/presensi/store',
                data: {
                    _token: "{{ csrf_token() }}",
                    image: image,
                    lokasi: lokasi
                },
                cache: false,
                success: function(respond) {
                    var status = respond.split("|");
                    if (status[0] == "success") {
                        if (status[2] == "in") {
                            notifikasi_in.play();
                        } else {
                            notifikasi_out.play()
                        }
                        Swal.fire({
                            title: 'Berhasil !',
                            text: status[1],
                            icon: 'success',
                            confirmButtonText: 'OK'
                        })
                        setTimeout("location.href='/dashboard'", 3000);
                    } else {
                        if (status[2] == "radius") {
                            radius_sound.play();
                        }
                        Swal.fire({
                            title: 'Tidak Berhasil !',
                            text: status[1],
                            icon: 'error',
                            confirmButtonText: 'OK',
                            footer: '<a href="/">Kembali ke Dashboard</a>'
                        })
                    }
                }
            });
        });

        // Tombol Konfirmasi Shift (di atas)
        $("#btnKonfirmasiShift").click(function() {
            $("#modalKonfirmasiShift").removeClass("hidden");
        });

        // Tombol di Modal Konfirmasi Shift
        $("#btnSudahBenar").click(function() {
            $("#modalKonfirmasiShift").addClass("hidden");
            // User bisa langsung absen sekarang
        });

        $("#btnBukaGantiShift").click(function() {
            $("#modalKonfirmasiShift").addClass("hidden");
            $("#modalGantiShift").removeClass("hidden");
        });

        // Modal Ganti Shift
        $("#btnCloseModal").click(function() {
            $("#modalGantiShift").addClass("hidden");
        });

        // Submit form modal via AJAX
        $("#formGantiShift").submit(function(e) {
            e.preventDefault();
            var kode_jam_kerja = $("#kode_jam_kerja").val();
            $.ajax({
                type: 'POST',
                url: '/presensi/update-shift-ajax',
                data: {
                    _token: "{{ csrf_token() }}",
                    kode_jam_kerja: kode_jam_kerja
                },
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            title: 'Berhasil!',
                            text: response.success,
                            icon: 'success',
                            confirmButtonText: 'OK'
                        });
                        $("#modalGantiShift").addClass("hidden");
                        setTimeout("location.reload()", 2000);
                    } else {
                        Swal.fire({
                            title: 'Error!',
                            text: response.error,
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                    }
                },
                error: function() {
                    Swal.fire({
                        title: 'Error!',
                        text: 'Terjadi kesalahan sistem.',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                }
            });
        });
    </script>

    <script>
        // Modal konfirmasi shift muncul otomatis jika belum absen in hari ini
        @if($cek == 0)
            window.onload = function() {
                jam();
                $("#modalKonfirmasiShift").removeClass("hidden");
            };
        @else
            window.onload = function() {
                jam();
            };
        @endif
    </script>
@endpush