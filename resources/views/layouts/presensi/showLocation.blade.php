<div class="container-fluid">
    <!-- MAP -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Lokasi Presensi</h5>
                </div>
                <div class="card-body">
                    <div id="map" style="height:400px"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- INFO -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Informasi Presensi</h5>
                </div>
                <div class="card-body">

                    @php
                        $rows = [
                            'NRP' => $presensi->nrp,
                            'Nama' => $presensi->nama,
                            'Tanggal Presensi' => \Carbon\Carbon::parse($presensi->tgl_presensi)->format('d-m-Y'),
                            'Jam Masuk' => $presensi->jam_in ?? 'Tidak Ada',
                        ];
                    @endphp

                    @foreach ($rows as $label => $value)
                    <div class="row mb-2">
                        <div class="col-sm-4 fw-bold">{{ $label }}</div>
                        <div class="col-sm-8">{{ $value }}</div>
                    </div>
                    @endforeach

                    <div class="row mb-3">
                        <div class="col-sm-4 fw-bold">Foto (IN)</div>
                        <div class="col-sm-8">
                            @if($presensi->foto_in)
                                <img src="{{ asset('storage/uploads/absensi/'.$presensi->foto_in) }}"
                                     class="img-fluid rounded"
                                     style="max-width:150px">
                            @else
                                <span class="text-muted">Tidak ada foto</span>
                            @endif
                        </div>
                    </div>

                    @if($presensi->jam_out)
                        <hr>
                        <div class="row mb-2">
                            <div class="col-sm-4 fw-bold">Jam Keluar</div>
                            <div class="col-sm-8">{{ $presensi->jam_out }}</div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-sm-4 fw-bold">Foto (OUT)</div>
                            <div class="col-sm-8">
                                @if($presensi->foto_out)
                                    <img src="{{ asset('storage/uploads/absensi/'.$presensi->foto_out) }}"
                                         class="img-fluid rounded"
                                         style="max-width:150px">
                                @else
                                    <span class="text-muted">Tidak ada foto</span>
                                @endif
                            </div>
                        </div>
                    @endif

                </div>
            </div>
        </div>
    </div>
</div>

<script>
(function () {

    const lokasi = {
        in: "{{ $presensi->lokasi_in }}",
        out: "{{ $presensi->lokasi_out }}"
    };

    const parseLokasi = (lokasi) => {
        if (!lokasi || !lokasi.includes(',')) return null;
        const [lat, lng] = lokasi.split(',').map(Number);
        return isNaN(lat) || isNaN(lng) ? null : [lat, lng];
    };

    const inLoc  = parseLokasi(lokasi.in);
    const outLoc = parseLokasi(lokasi.out);

    if (!inLoc && !outLoc) {
        document.getElementById('map').innerHTML =
            '<p class="text-muted text-center">Lokasi tidak tersedia</p>';
        return;
    }

    const center = inLoc || outLoc;
    const map = L.map('map').setView(center, 15);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap'
    }).addTo(map);

    let markers = [];

    if (inLoc) {
        const mIn = L.marker(inLoc).addTo(map)
            .bindPopup("<b>{{ $presensi->nama }}</b><br>Presensi IN");
        markers.push(mIn);
        L.circle(inLoc, { radius:20 }).addTo(map);
    }

    if (outLoc) {
        const mOut = L.marker(outLoc, {
            icon: L.icon({
                iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-blue.png',
                iconSize: [25,41],
                iconAnchor: [12,41]
            })
        }).addTo(map).bindPopup("<b>{{ $presensi->nama }}</b><br>Presensi OUT");

        markers.push(mOut);
        L.circle(outLoc, { radius:20, color:'blue' }).addTo(map);
    }

    if (markers.length > 1) {
        map.fitBounds(L.featureGroup(markers).getBounds());
    }

})();
</script>
