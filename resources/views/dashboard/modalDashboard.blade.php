@php
$user = Auth::guard('karyawan')->user();
$namaKaryawan = $user->nama ?? 'N/A';
$nrpKaryawan = $user->nrp ?? 'N/A';
@endphp

{{-- Perbaikan: Update watermark untuk menggunakan tanggal dan jam dari data presensi --}}
<!-- Modal In -->
<div id="imageModalIn"
    class="fixed inset-0 bg-black/70 hidden z-50 flex items-center justify-center px-3 transition-opacity duration-300">
    
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg overflow-hidden">
        
        <!-- Header -->
        <div class="flex justify-between items-center px-4 py-3 border-b">
            <h3 class="text-sm md:text-base font-semibold text-gray-800">
                Foto Absen Masuk Hari Ini
            </h3>
            <button id="closeModalBtnIn" class="text-gray-500 hover:text-gray-700 p-1">
                <i class="fas fa-times text-lg md:text-xl"></i>
            </button>
        </div>

        <!-- Content -->
        <div class="relative bg-black flex items-center justify-center">
            @if ($presensiHariIni && $presensiHariIni->foto_in)
                @php
                    $path = Storage::url('uploads/absensi/' . $presensiHariIni->foto_in);
                @endphp

                <!-- FRAME FOTO -->
                <div class="relative w-full aspect-[3/4] max-h-[55vh] sm:max-h-[60vh] md:max-h-[70vh] bg-black flex items-center justify-center p-2">
                    <img
                        src="{{ url($path) }}"
                        alt="Foto Absen Masuk"
                        class="max-w-full max-h-full object-contain rounded-lg"
                        style="image-orientation: from-image;"
                    >

                    <!-- WATERMARK -->
                    <div class="absolute bottom-2 left-2 right-2 bg-black/60 text-white text-[11px] md:text-xs px-3 py-2 rounded-lg backdrop-blur-sm leading-snug">
                        
                        <div class="font-semibold">{{ $namaKaryawan }}</div>
                        <div>NRP: {{ $nrpKaryawan }}</div>

                        <div class="mt-1 border-t border-white/30 pt-1">
                            {{-- PERBAIKAN: Gunakan tanggal dari tgl_presensi, bukan date('d-m-Y') --}}
                            <div><strong>Tanggal:</strong> {{ date('d-m-Y', strtotime($presensiHariIni->tgl_presensi)) }}</div>
                            <div><strong>Jam:</strong> {{ $presensiHariIni->jam_in }}</div>
                            <div><strong>Lokasi:</strong> {{ $presensiHariIni->lokasi_in ?? 'Lokasi Absen' }}</div>
                        </div>
                    </div>
                </div>
            @else
                <div class="py-20 text-gray-400 text-sm md:text-base text-center">
                    Belum ada foto absen masuk
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Modal Out -->
<div id="imageModalOut"
    class="fixed inset-0 bg-black/70 hidden z-50 flex items-center justify-center px-3 transition-opacity duration-300">
    
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg overflow-hidden">
        
        <!-- Header -->
        <div class="flex justify-between items-center px-4 py-3 border-b">
            <h3 class="text-sm md:text-base font-semibold text-gray-800">
                Foto Absen Pulang Hari Ini
            </h3>
            <button id="closeModalBtnOut" class="text-gray-500 hover:text-gray-700 p-1">
                <i class="fas fa-times text-lg md:text-xl"></i>
            </button>
        </div>
        
        <!-- Content -->
        <div class="relative bg-black flex items-center justify-center">
            @if ($presensiHariIni && $presensiHariIni->foto_out)
                @php
                    $path = Storage::url('uploads/absensi/' . $presensiHariIni->foto_out);
                @endphp

                <!-- FRAME FOTO -->
                <div class="relative w-full aspect-[3/4] max-h-[55vh] sm:max-h-[60vh] md:max-h-[70vh] bg-black flex items-center justify-center p-2">
                    <img
                        src="{{ url($path) }}"
                        alt="Foto Absen Pulang"
                        class="max-w-full max-h-full object-contain rounded-lg"
                        style="image-orientation: from-image;"
                    >
                    <!-- WATERMARK -->
                    <div class="absolute bottom-2 left-2 right-2 bg-black/60 text-white text-[11px] md:text-xs px-3 py-2 rounded-lg backdrop-blur-sm leading-snug">
                        
                        <div class="font-semibold">{{ $namaKaryawan }}</div>
                        <div>NRP: {{ $nrpKaryawan }}</div>

                        <div class="mt-1 border-t border-white/30 pt-1">
                            {{-- PERBAIKAN: Gunakan tanggal dari tgl_presensi, bukan date('d-m-Y') --}}
                            <div><strong>Tanggal:</strong> {{ date('d-m-Y', strtotime($presensiHariIni->tgl_presensi)) }}</div>
                            <div><strong>Jam:</strong> {{ $presensiHariIni->jam_out }}</div>
                            <div><strong>Lokasi:</strong> {{ $presensiHariIni->lokasi_out ?? 'Lokasi Absen' }}</div>
                        </div>
                    </div>
                </div>
            @else
                <div class="py-20 text-gray-400 text-sm md:text-base text-center">
                    Belum ada foto absen pulang
                </div>
            @endif
        </div>
    </div>
</div>

{{-- Modal Hasil Absensi --}}
<div id="hasilAbsen"
    class="fixed inset-0 bg-gray-800 bg-opacity-75 flex items-center justify-center px-3 invisible opacity-0 transition-opacity duration-300">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-lg max-h-[90vh] overflow-hidden">
        <div class="p-4 border-b flex justify-between items-center">
            <h3 class="text-lg md:text-xl font-semibold text-gray-800">Data Presensi Bulan Ini</h3>
            <button id="closeHasilAbsen" class="text-gray-500 hover:text-gray-700 p-1">
                <i class="fas fa-times text-lg md:text-xl"></i>
            </button>
        </div>
        <div class="p-4 max-h-[70vh] overflow-y-auto">
            <div class="space-y-3">
                @if(empty($historiBulanIni))
                    <!-- Pesan jika tidak ada data -->
                    <div class="bg-gray-100 rounded-md shadow-sm p-4 flex items-center space-x-3">
                        <div class="text-gray-600 flex-shrink-0">
                            <ion-icon name="information-circle-outline" style="font-size: 28px;"></ion-icon>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h3 class="text-sm md:text-base font-semibold leading-tight truncate">NA</h3>
                            <h4 class="text-gray-600 text-xs md:text-sm mb-0 truncate">Tidak ada data presensi bulan ini</h4>
                        </div>
                    </div>
                @else
                    @foreach ($historiBulanIni as $d)
                        @if ($d->status == 'h')
                            <div class="bg-white rounded-md shadow-sm p-3 border border-gray-200 flex items-center space-x-3">
                                <div class="text-green-600 flex-shrink-0">
                                    <ion-icon name="finger-print-outline" style="font-size: 28px;"></ion-icon>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h3 class="text-sm md:text-base font-semibold leading-tight truncate">{{ $d->nama_jam_kerja }}</h3>
                                    <h4 class="text-gray-600 text-xs md:text-sm mb-1 truncate">{{ date('d-m-Y', strtotime($d->tgl_presensi)) }}</h4>
                                    <div class="flex flex-col space-y-1 text-xs md:text-sm">
                                        <span>
                                            Absen In: {!! $d->jam_in != null
                                                ? '<span class="font-medium text-green-600">' . date('H:i', strtotime($d->jam_in)) . '</span>'
                                                : '<span class="text-red-600 font-semibold">Belum Absen</span>' !!}
                                        </span>
                                        <span>
                                            Absen Out: {!! $d->jam_out != null
                                                ? '<span class="font-medium text-green-600">' . date('H:i', strtotime($d->jam_out)) . '</span>'
                                                : '<span class="text-red-600 font-semibold">Belum Absen</span>' !!}
                                        </span>
                                    </div>
                                </div>
                                <div class="text-xs md:text-sm font-semibold text-right">
                                    @php
                                        $jam_in = date('H:i', strtotime($d->jam_in));
                                        $jam_masuk = date('H:i', strtotime($d->jam_masuk));
                                        $jadwal_jam_masuk = $d->tgl_presensi . ' ' . $jam_masuk;
                                        $jam_presensi = $d->tgl_presensi . ' ' . $jam_in;
                                    @endphp
                                    @if ($jam_in > $jam_masuk)
                                        @php
                                            $jmlterlambat = hitungjamterlambat($jadwal_jam_masuk, $jam_presensi);
                                        @endphp
                                        <span class="text-red-600">{{ $jmlterlambat }}</span>
                                    @else
                                        <span class="text-green-600">Tepat Waktu</span>
                                    @endif
                                </div>
                            </div>
                        @elseif($d->status == 'c')
                            <div class="bg-white rounded-md shadow-sm p-3 border border-gray-200 flex items-center space-x-3">
                                <div class="text-yellow-600 flex-shrink-0">
                                    <ion-icon name="alert-circle-outline" style="font-size: 28px;"></ion-icon>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h3 class="text-sm md:text-base font-semibold leading-tight truncate">Cuti - {{ $d->kode_izin }}</h3>
                                    <h4 class="text-gray-600 text-xs md:text-sm mb-1 truncate">{{ date('d-m-Y', strtotime($d->tgl_presensi)) }}</h4>
                                    <span class="text-green-600 text-xs md:text-sm">{{ $d->nama_cuti }}</span>
                                    <br>
                                    <span class="text-gray-600 text-xs md:text-sm">{{ $d->keterangan }}</span>
                                </div>
                            </div>
                        @elseif($d->status == 's')
                            <div class="bg-white rounded-md shadow-sm p-3 border border-gray-200 flex items-center space-x-3">
                                <div class="text-red-600 flex-shrink-0">
                                    <ion-icon name="medkit-outline" style="font-size: 28px;"></ion-icon>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h3 class="text-sm md:text-base font-semibold leading-tight truncate">Sakit - {{ $d->kode_izin }}</h3>
                                    <h4 class="text-gray-600 text-xs md:text-sm mb-1 truncate">{{ date('d-m-Y', strtotime($d->tgl_presensi)) }}</h4>
                                    <span class="text-gray-600 text-xs md:text-sm">{{ $d->keterangan }}</span>
                                    <br>
                                    @if (!empty($d->doc_cis))
                                        <span class="text-blue-600 text-xs md:text-sm">
                                            <ion-icon name="document-attach-outline"></ion-icon> Lihat Doc
                                        </span>
                                    @endif
                                </div>
                            </div>
                        @endif
                    @endforeach
                @endif
            </div>
        </div>
    </div>
</div>

<script>
    const modalIn = document.getElementById('imageModalIn');
    const modalOut = document.getElementById('imageModalOut');

    document.getElementById('openModalBtnIn').onclick = () => modalIn.classList.remove('hidden');
    document.getElementById('closeModalBtnIn').onclick = () => modalIn.classList.add('hidden');

    document.getElementById('openModalBtnOut').onclick = () => modalOut.classList.remove('hidden');
    document.getElementById('closeModalBtnOut').onclick = () => modalOut.classList.add('hidden');

    window.addEventListener('click', e => {
        if (e.target === modalIn) modalIn.classList.add('hidden');
        if (e.target === modalOut) modalOut.classList.add('hidden');
    });

    // Modal Hasil Absen
    document.getElementById('openHasilAbsen').addEventListener('click', function() {
        const modalHasilAbsen = document.getElementById('hasilAbsen');
        modalHasilAbsen.classList.remove('invisible', 'opacity-0');
        modalHasilAbsen.classList.add('visible', 'opacity-100');
    });

    document.getElementById('closeHasilAbsen').addEventListener('click', function() {
        const modalHasilAbsen = document.getElementById('hasilAbsen');
        modalHasilAbsen.classList.remove('visible', 'opacity-100');
        modalHasilAbsen.classList.add('invisible', 'opacity-0');
    });
</script>