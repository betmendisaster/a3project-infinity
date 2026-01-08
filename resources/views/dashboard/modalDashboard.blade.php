<!-- Modal In -->
<div class="fixed inset-0 bg-gray-900 bg-opacity-50 items-center justify-center" id="imageModalIn" style="display: none;">
    <div class="bg-white rounded-lg overflow-hidden shadow-lg max-w-md w-full mx-auto mt-20">
        <div class="flex justify-between items-center p-4 border-b">
            <h3 class="text-lg font-semibold">
                Foto Absen Masuk Hari ini
            </h3>
            <button class="text-gray-500 hover:text-gray-700" id="closeModalBtnIn">
                <i class="fas fa-times">
                </i>
            </button>
        </div>
        <div class="p-4">
            @if ($presensiHariIni != null)
                @php
                    $path = Storage::url('uploads/absensi/' . $presensiHariIni->foto_in);
                @endphp
                <img src="{{ url($path) }}" alt="Foto Absen Masuk" class="w-full h-auto max-h-96 object-contain" style="max-width: 100%; height: auto;">
            @else
                <img alt="Placeholder: Tidak ada foto absen masuk" class="w-full h-auto max-h-96 object-contain" style="max-width: 100%; height: auto;" src="{{ asset('assets/img/loader.gif') }}" />
            @endif
        </div>
    </div>
</div>

<!-- Modal Out -->
<div class="fixed inset-0 bg-gray-900 bg-opacity-50 items-center justify-center" id="imageModalOut" style="display: none;">
    <div class="bg-white rounded-lg overflow-hidden shadow-lg max-w-md w-full mx-auto mt-20">
        <div class="flex justify-between items-center p-4 border-b">
            <h3 class="text-lg font-semibold">
                Foto Absen Pulang Hari ini
            </h3>
            <button class="text-gray-500 hover:text-gray-700" id="closeModalBtnOut">
                <i class="fas fa-times">
                </i>
            </button>
        </div>
        <div class="p-4">
            @if ($presensiHariIni != null && $presensiHariIni->jam_out != null)
                @php
                    $path = Storage::url('uploads/absensi/' . $presensiHariIni->foto_out);
                @endphp
                <img src="{{ url($path) }}" alt="Foto Absen Pulang" class="w-full h-auto max-h-96 object-contain" style="max-width: 100%; height: auto;">
            @else
                <img alt="Placeholder: Tidak ada foto absen pulang" class="w-full h-auto max-h-96 object-contain" style="max-width: 100%; height: auto;" src="{{ asset('assets/img/loader.gif') }}" />
            @endif
        </div>
    </div>
</div>

{{-- Modal Hasil Absensi --}}
<!-- Modal -->
<div id="hasilAbsen"
    class="fixed inset-0 bg-gray-800 bg-opacity-75 flex items-center justify-center invisible opacity-0 transition-opacity duration-300">
    <div class="bg-white rounded-lg shadow-lg w-11/12 md:w-3/4 lg:w-1/2 max-h-96 overflow-y-auto">
        <div class="p-4 border-b flex justify-between items-center">
            <h3 class="text-xl font-semibold">Data Presensi Bulan Ini</h3>
            <button id="closeHasilAbsen" class="text-gray-500 hover:text-gray-700">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="p-4">
            <table class="min-w-full bg-white">
                <style>
                    .historicontent {
                        display: flex;
                    }

                    .datapresensi {
                        margin-left: 10px;
                    }
                </style>
                <div class="max-w-md mx-auto space-y-3">
                    @if(empty($historiBulanIni))
                        <!-- Pesan jika tidak ada data -->
                        <div class="bg-white rounded-md shadow-sm p-3 flex items-center space-x-3">
                            <div class="text-gray-600 flex-shrink-0">
                                <ion-icon name="information-circle-outline" style="font-size: 36px;"></ion-icon>
                            </div>
                            <div class="flex-1 min-w-0">
                                <h3 class="text-sm font-semibold leading-tight truncate">NA</h3>
                                <h4 class="text-gray-600 text-xs mb-0 truncate">Tidak ada data presensi bulan ini</h4>
                            </div>
                        </div>
                    @else
                        @foreach ($historiBulanIni as $d)
                            @if ($d->status == 'h')
                                <div class="bg-white rounded-md shadow-sm p-3 flex items-center space-x-3">
                                    <div class="text-green-600 flex-shrink-0">
                                        <ion-icon name="finger-print-outline" style="font-size: 36px;"></ion-icon>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <h3 class="text-sm font-semibold leading-tight truncate">{{ $d->nama_jam_kerja }}</h3>
                                        <h4 class="text-gray-600 text-xs mb-0 truncate">{{ date('d-m-Y', strtotime($d->tgl_presensi)) }}</h4>
                                        <div class="flex space-x-1 text-xs mt-0.5">
                                            <span>
                                                Absen In {!! $d->jam_in != null
                                                    ? '<span class="font-medium">' . date('H:i', strtotime($d->jam_in)) . '</span>'
                                                    : '<span class="text-red-600 font-semibold">Belum Absen</span>' !!}
                                            </span>
                                            
                                            <span>
                                                Absen Out{!! $d->jam_out != null
                                                    ? '<span>- ' . date('H:i', strtotime($d->jam_out)) . '</span>'
                                                    : '<span class="text-red-600 font-semibold">- Belum Absen</span>' !!}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="mt-05 text-xs font-semibold" id="keterangan">
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
                                            <span class="text-red-600">Terlambat {{ $jmlterlambat }}</span>
                                        @else
                                            <span class="text-green-600">Tepat Waktu</span>
                                        @endif
                                    </div>
                                </div>
                            @elseif($d->status == 'c')
                                <div class="bg-white rounded-md shadow-sm p-3 flex items-center space-x-3">
                                    <div class="text-yellow-600 flex-shrink-0">
                                        <ion-icon name="alert-circle-outline" style="font-size: 36px;"></ion-icon>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <h3 class="text-sm font-semibold leading-tight truncate">Cuti - {{ $d->kode_izin }}</h3>
                                        <h4 class="text-gray-600 text-xs mb-0 truncate">{{ date('d-m-Y', strtotime($d->tgl_presensi)) }}</h4>
                                        <span class="text-green-600">{{ $d->nama_cuti }}</span>
                                        <span>{{ $d->keterangan }}</span>
                                    </div>
                                </div>
                            @elseif($d->status == 's')
                                <div class="bg-white rounded-md shadow-sm p-3 flex items-center space-x-3">
                                    <div class="text-red-600 flex-shrink-0">
                                        <ion-icon name="heart-half" style="font-size: 36px;"></ion-icon>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <h3 class="text-sm font-semibold leading-tight truncate">Sakit - {{ $d->kode_izin }}</h3>
                                        <h4 class="text-gray-600 text-xs mb-0 truncate">{{ date('d-m-Y', strtotime($d->tgl_presensi)) }}</h4>
                                        <span>{{ $d->keterangan }}</span>
                                        <br>
                                        @if (!empty($d->doc_cis))
                                            <span style="color: blue">
                                                <ion-icon name="document-attach-outline"></ion-icon> Lihat Doc
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    @endif
                </div>
            </table>
        </div>
    </div>
</div>
{{-- End Hasil Absensi --}}

<script>
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

    // Modal in Script 
    const openModalBtnIn = document.getElementById('openModalBtnIn');
    const closeModalBtnIn = document.getElementById('closeModalBtnIn');
    const imageModalIn = document.getElementById('imageModalIn');

    openModalBtnIn.addEventListener('click', () => {
        imageModalIn.style.display = 'flex';
    });

    closeModalBtnIn.addEventListener('click', () => {
        imageModalIn.style.display = 'none';
    });

    window.addEventListener('click', (e) => {
        if (e.target === imageModalIn) {
            imageModalIn.style.display = 'none';
        }
    });
    // End Modal in

    // Modal out Script 
    const openModalBtnOut = document.getElementById('openModalBtnOut');
    const closeModalBtnOut = document.getElementById('closeModalBtnOut');
    const imageModalOut = document.getElementById('imageModalOut');

    openModalBtnOut.addEventListener('click', () => {
        imageModalOut.style.display = 'flex';
    });

    closeModalBtnOut.addEventListener('click', () => {
        imageModalOut.style.display = 'none';
    });

    window.addEventListener('click', (e) => {
        if (e.target === imageModalOut) {
            imageModalOut.style.display = 'none';
        }
    });
    // End Modal out
</script>