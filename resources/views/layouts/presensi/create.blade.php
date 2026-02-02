@extends('layouts.layoutNoFooter')
@section('header')
    <style>
        .fotoduls {
            display: inline-block;
            width: 100% !important;
            height: 0 !important;  /* Gunakan padding-bottom untuk aspect ratio */
            padding-bottom: 133.33% !important;  /* 4/3 * 100% ≈ 133.33% untuk aspect 3:4 */
            position: relative;
            margin: auto;
            border-radius: 15px;
            overflow: hidden;  /* Pastikan video tidak keluar dari border */
        }

        .fotoduls video {
            position: absolute;
            top: 0;
            left: 0;
            width: 100% !important;
            height: 100% !important;
            object-fit: cover;
            border-radius: 15px;

            /* ⬇️ TAMBAHAN INI */
            transform: scaleX(-1);
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
    <style>
    /* ================= TOMBOL ABSEN INTERAKTIF ================= */
    #btnCapture {
        animation: pulse 1.8s infinite;
    }

    @keyframes pulse {
        0% { box-shadow: 0 0 0 0 rgba(34,197,94,0.6); }
        70% { box-shadow: 0 0 0 14px rgba(34,197,94,0); }
        100% { box-shadow: 0 0 0 0 rgba(34,197,94,0); }
    }

    .btn-disabled {
        opacity: 0.6;
        cursor: not-allowed;
        animation: none;
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
                    {{-- PERBAIKAN: Tambah cek shift malam kemarin untuk tombol konfirmasi shift --}}
                    {{-- PERBAIKAN: Jika shift malam kemarin belum out, jangan tampilkan tombol konfirmasi --}}
                    @php
                        // PERBAIKAN: Cek apakah ada presensi hari kemarin yang belum out dan shift malam
                        $kemarin = date("Y-m-d", strtotime("-1 day", strtotime(date("Y-m-d"))));
                        $presensiKemarin = DB::table('presensi')->where('tgl_presensi', $kemarin)->where('nrp', Auth::guard('karyawan')->user()->nrp)->whereNull('jam_out')->first();
                        $isShiftMalamKemarin = $presensiKemarin && $presensiKemarin->is_shift_malam == 1;
                        $showKonfirmasiShift = !$presensiHariIni && !$isShiftMalamKemarin; // Jangan tampilkan jika shift malam kemarin belum out
                    @endphp
                    @if($showKonfirmasiShift)
                        <button id="btnKonfirmasiShift" class="text-xs bg-red-500 text-white rounded px-2 py-1 mt-2 cursor-pointer hover:bg-red-700 transition duration-300 shadow-sm flex items-center gap-1">
                            <i class="fa-solid fa-arrows-alt-h"></i> Konfirmasi Shift
                        </button>
                    @endif
                </div>
            </div>

            {{-- Camera --}}
            <div class="bg-white text-gray-800 rounded-t-lg p-2 flex items-center justify-center w-full mx-1 shadow-md mt-3">
                <div class="fotoduls">
                    <video id="video" autoplay playsinline></video>
                    <canvas id="canvas" class="hidden"></canvas>
                </div>
            </div>
            <div class="flex gap-2 justify-center mt-2">
                <button id="btnCapture"
                    class="bg-green-500 text-white px-4 py-3 rounded-full text-sm font-bold 
                        shadow-lg hidden flex items-center gap-2 
                        hover:scale-105 active:scale-95 transition duration-300">
                    <span id="btnIcon">📸</span>
                    <span id="btnText">ABSEN</span>
                </button>
            </div>

            <div class="items-center shadow-lg rounded-lg p-4 w-full max-w-xs md:max-w-md lg:max-w-lg mx-auto">
                <div class="col">
                    <div id="map"></div>
                </div>
            </div>
            <!-- lokasi-->
            <div class="bg-white text-gray-800 rounded-b-lg p-2 flex items-center justify-center w-full mx-1 shadow-md mt-3">
                <input type="hidden" id="lokasi">
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

{{-- Bagian @push('myscript') di view create.blade.php --}}
@push('myscript')
<script>
/* ================= STATUS ABSEN ================= */
let presensiHariIni = @json($presensiHariIni); // Jadikan variabel global untuk update
let sudahAbsenIn = presensiHariIni !== null;
let sudahAbsenOut = presensiHariIni && presensiHariIni.jam_out !== null;
// PERBAIKAN: Tambah cek shift malam kemarin untuk status absen
const isShiftMalamKemarin = @json($isShiftMalamKemarin);
let sudahAbsenInMalam = sudahAbsenIn || isShiftMalamKemarin; // Jika shift malam kemarin, anggap sudah absen in

/* ================= GLOBAL ================= */
let cameraStarted = false; 
let lokasiReady = false;
let currentLat = null;
let currentLng = null;
let mapAbsensi = null;
let markerUser = null;
let watchId = null;

/* ================= HAPTIC ================= */
function haptic(type="light") {
    if (!navigator.vibrate) return;
    if (type==="success") navigator.vibrate([40,50,40]);
    else if (type==="error") navigator.vibrate([120,50,120]);
    else navigator.vibrate(30);
}

/* ================= DOM READY ================= */
document.addEventListener("DOMContentLoaded", function () {
    jam();
    setupButtonLabel();
    startCameraAuto();
    startGeofenceRealtime();

    // PERBAIKAN: Cek dan tampilkan modal berdasarkan status terbaru
    checkAndShowModal();
});

/* ================= JAM ================= */
function jam() {
    const e = document.getElementById('jam');
    const d = new Date();
    e.innerHTML =
        d.getHours() + ':' +
        (d.getMinutes()<10?'0':'') + d.getMinutes() + ':' +
        (d.getSeconds()<10?'0':'') + d.getSeconds();
    setTimeout(jam, 1000);
}

/* ================= AUDIO ================= */
const notifikasi_in = document.getElementById('notifikasi_in');
const notifikasi_out = document.getElementById('notifikasi_out');
const radius_sound = document.getElementById('radius_sound');

/* ================= GEOLOCATION REALTIME ================= */
const lokasi = document.getElementById('lokasi');

function startGeofenceRealtime() {
    if (!navigator.geolocation) {
        Swal.fire('Error', 'Browser tidak mendukung GPS', 'error');
        return;
    }

    watchId = navigator.geolocation.watchPosition(
        successRealtime,
        () => Swal.fire('GPS Gagal', 'Aktifkan GPS & coba lagi', 'error'),
        { enableHighAccuracy: true, maximumAge: 0, timeout: 10000 }
    );
}

function successRealtime(pos) {
    currentLat = pos.coords.latitude;
    currentLng = pos.coords.longitude;
    lokasi.value = currentLat + ',' + currentLng;
    lokasiReady = true;

    let site = "{{ $lok_site->lokasi_cabang }}".split(",");
    let radius = {{ $lok_site->radius_cabang }};
    let jarak = hitungJarak(currentLat, currentLng, site[0], site[1]);

    // Cek lokasi2 jika ada
    let site2 = null;
    let jarak2 = null;
    @if($lok_site->lokasi2_cabang)
        site2 = "{{ $lok_site->lokasi2_cabang }}".split(",");
        jarak2 = hitungJarak(currentLat, currentLng, site2[0], site2[1]);
    @endif

    if (!mapAbsensi) {
        mapAbsensi = L.map('map').setView([currentLat, currentLng], 16);
        L.tileLayer(
            'http://{s}.google.com/vt/lyrs=m&x={x}&y={y}&z={z}',
            { maxZoom: 20, subdomains: ['mt0', 'mt1', 'mt2', 'mt3'] }
        ).addTo(mapAbsensi);

        // Circle untuk lokasi utama
        L.circle([site[0], site[1]], {
            color: 'red', fillColor: '#f03', fillOpacity: 0.3, radius: radius
        }).addTo(mapAbsensi);

        // Circle untuk lokasi2 jika ada
        @if($lok_site->lokasi2_cabang)
            L.circle([site2[0], site2[1]], {
                color: 'blue', fillColor: '#03f', fillOpacity: 0.3, radius: radius
            }).addTo(mapAbsensi);
        @endif
    }

    if (!markerUser) {
        markerUser = L.marker([currentLat, currentLng]).addTo(mapAbsensi);
    } else {
        markerUser.setLatLng([currentLat, currentLng]);
    }

    /* ===== GEOFENCE LIVE ===== */
    let isInRadius = jarak <= radius;
    @if($lok_site->lokasi2_cabang)
        isInRadius = isInRadius || (jarak2 <= radius);
    @endif

    if (isInRadius) {
        $("#btnCapture").prop("disabled", false).removeClass("btn-disabled");
    } else {
        $("#btnCapture").prop("disabled", true).addClass("btn-disabled");
        haptic("error");
    }
}

async function detectFace() {
    if (!faceDetector) return true;
    const faces = await faceDetector.detect(video);
    return faces.length > 0;
}

function setupButtonLabel() {
    // PERBAIKAN: Gunakan sudahAbsenInMalam untuk mendukung shift malam
    if (!sudahAbsenInMalam) {
        $("#btnText").text("ABSEN IN");
        $("#btnCapture").addClass("bg-green-500").removeClass("bg-blue-500");
    } else if (!sudahAbsenOut) {
        $("#btnText").text("ABSEN OUT");
        $("#btnCapture").addClass("bg-blue-500").removeClass("bg-green-500");
    } else {
        $("#btnCapture").addClass("hidden");
    }
}

/* ================= PERBAIKAN: Fungsi untuk cek dan tampilkan modal ================= */
function checkAndShowModal() {
    // PERBAIKAN: Jika belum absen in hari baru DAN shift malam kemarin sudah out, tampilkan modal
    const showKonfirmasiShift = !sudahAbsenInMalam && !isShiftMalamKemarin; // Sesuaikan logika dengan backend
    if (showKonfirmasiShift) {
        $("#modalKonfirmasiShift").removeClass("hidden");
    }
}

/* ================= CAMERA AUTO ================= */
async function startCameraAuto() {
    // PERBAIKAN: Gunakan sudahAbsenOut untuk mencegah kamera jika sudah out
    if (cameraStarted || sudahAbsenOut) return;
    cameraStarted = true;

    try {
        stream = await navigator.mediaDevices.getUserMedia({
            video:{ facingMode:'user' }, audio:false
        });
        video.srcObject = stream;
        $("#btnCapture").removeClass("hidden");
        haptic();
    } catch {
        cameraStarted = false;
        Swal.fire('Kamera Ditolak', 'Izinkan kamera untuk absen', 'warning');
    }
}

/* ================= ABSEN ================= */
$("#btnCapture").on("click", async function () {
    $("#btnCapture").prop("disabled", true).addClass("btn-disabled");
    $("#btnIcon").text("⏳");
    $("#btnText").text("MEMPROSES...");
    haptic();

    if (!lokasiReady) {
        Swal.fire('Lokasi Belum Siap', 'Tunggu GPS aktif', 'warning');
        resetButton(); return;
    }

    if (!await detectFace()) {
        Swal.fire('Wajah Tidak Terdeteksi', 'Hadapkan wajah ke kamera', 'warning');
        haptic("error"); resetButton(); return;
    }

    canvas.width = video.videoWidth;
    canvas.height = video.videoHeight;
    canvas.getContext('2d').drawImage(video,0,0);

    $.post('/presensi/store', {
        _token: "{{ csrf_token() }}",
        image: canvas.toDataURL('image/jpeg',0.85),
        lokasi: lokasi.value
    }, function(res){
        let status = res.split("|");
        if (status[0]==="success") {
            haptic("success");
            status[2]==="in" ? notifikasi_in.play() : notifikasi_out.play();
            // PERBAIKAN: Update status absen setelah berhasil, lalu reload halaman untuk refresh modal dan status
            sudahAbsenInMalam = (status[2] === "in") || sudahAbsenInMalam;
            sudahAbsenOut = (status[2] === "out") || sudahAbsenOut;
            setupButtonLabel(); // Update tombol
            checkAndShowModal(); // Update modal
            Swal.fire('Berhasil', status[1], 'success').then(() => {
                // PERBAIKAN: Reload halaman untuk memastikan status terbaru dari backend
                location.reload();
            });
        } else {
            haptic("error");
            Swal.fire('Gagal', status[1], 'error');
            resetButton();
        }
    });
});

/* ================= SHIFT MODAL ================= */
$("#btnKonfirmasiShift").on("click", ()=>$("#modalKonfirmasiShift").removeClass("hidden"));
$("#btnSudahBenar").on("click", ()=>$("#modalKonfirmasiShift").addClass("hidden"));
$("#btnBukaGantiShift").on("click", ()=>{
    $("#modalKonfirmasiShift").addClass("hidden");
    $("#modalGantiShift").removeClass("hidden");
});
$("#btnCloseModal").on("click", ()=>$("#modalGantiShift").addClass("hidden"));

// Tambahan: Handler untuk submit form ganti shift
$("#formGantiShift").on("submit", function(e) {
    e.preventDefault();
    $.post('/presensi/update-shift-ajax', $(this).serialize(), function(res) {
        if (res.success) {
            Swal.fire('Berhasil', res.success, 'success').then(() => location.reload()); // Reload untuk update shift
        } else {
            Swal.fire('Gagal', res.error, 'error');
        }
    }).fail(function() {
        Swal.fire('Error', 'Terjadi kesalahan jaringan.', 'error');
    });
});

/* ================= UTIL ================= */
function hitungJarak(lat1, lon1, lat2, lon2) {
    const R=6371000,toRad=x=>x*Math.PI/180;
    const dLat=toRad(lat2-lat1), dLon=toRad(lon2-lon1);
    const a=Math.sin(dLat/2)**2 +
        Math.cos(toRad(lat1))*Math.cos(toRad(lat2))*
        Math.sin(dLon/2)**2;
    return R*(2*Math.atan2(Math.sqrt(a),Math.sqrt(1-a)));
}
</script>
@endpush