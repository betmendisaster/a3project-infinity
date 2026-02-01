<form action="/settings/cabang/update" method="POST" id="frmCabangEdit">
    @csrf
    {{-- Kode Cabang --}}
    <div class="row">
        <div class="col-12">
            <div class="input-icon mb-3">
                <span class="input-icon-addon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-id">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                        <path d="M3 4m0 3a3 3 0 0 1 3 -3h12a3 3 0 0 1 3 3v10a3 3 0 0 1 -3 3h-12a3 3 0 0 1 -3 -3z" />
                        <path d="M9 10m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0" />
                        <path d="M15 8l2 0" />
                        <path d="M15 12l2 0" />
                        <path d="M7 16l10 0" />
                    </svg>
                </span>
                <input type="text" value="{{ $cabang->kode_cabang }}" readonly class="form-control"
                    placeholder="Kode Cabang" name="kode_cabang" id="kode_cabang">
            </div>
        </div>
    </div>
    {{-- NAMA CABANG --}}
    <div class="input-icon mb-3">
        <span class="input-icon-addon">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                class="icon icon-tabler icons-tabler-outline icon-tabler-access-point">
                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                <path d="M12 12l0 .01" />
                <path d="M14.828 9.172a4 4 0 0 1 0 5.656" />
                <path d="M17.657 6.343a8 8 0 0 1 0 11.314" />
                <path d="M9.168 14.828a4 4 0 0 1 0 -5.656" />
                <path d="M6.337 17.657a8 8 0 0 1 0 -11.314" />
            </svg>
        </span>
        <input type="text" value="{{ $cabang->nama_cabang }}" class="form-control" placeholder="Cabang Area Presensi"
            name="nama_cabang" id="nama_cabang">
    </div>
    {{-- LOKASI AREA CABANG --}}
    <div class="input-icon mb-3">
        <span class="input-icon-addon">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                class="icon icon-tabler icons-tabler-outline icon-tabler-pin">
                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                <path d="M15 4.5l-4 4l-4 1.5l-1.5 1.5l7 7l1.5 -1.5l1.5 -4l4 -4" />
                <path d="M9 15l-4.5 4.5" />
                <path d="M14.5 4l5.5 5.5" />
            </svg>
        </span>
        <input type="text" value="{{ $cabang->lokasi_cabang }}" class="form-control"
            placeholder="Area Lokasi Presensi Cabang" name="lokasi_cabang" id="lokasi_cabang">
    </div>
    {{-- LOKASI AREA CABANG 2 (Opsional untuk Double Lokasi) --}}
    <div class="input-icon mb-3">
        <span class="input-icon-addon">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-pin">
                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                <path d="M15 4.5l-4 4l-4 1.5l-1.5 1.5l7 7l1.5 -1.5l1.5 -4l4 -4" />
                <path d="M9 15l-4.5 4.5" />
                <path d="M14.5 4l5.5 5.5" />
            </svg>
        </span>
        <input type="text" value="{{ $cabang->lokasi2_cabang ?? '' }}" class="form-control" placeholder="Lokasi Cabang 2 (Opsional)" name="lokasi2_cabang" id="lokasi2_cabang">
    </div>
    {{-- RADIUS AREA CABANG --}}
    <div class="input-icon mb-3">
        <span class="input-icon-addon">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                class="icon icon-tabler icons-tabler-outline icon-tabler-gradienter">
                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                <path d="M3.227 14c.917 4 4.497 7 8.773 7c4.277 0 7.858 -3 8.773 -7" />
                <path d="M20.78 10a9 9 0 0 0 -8.78 -7a8.985 8.985 0 0 0 -8.782 7" />
                <path d="M12 12m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0" />
            </svg>
        </span>
        <input type="text" value="{{ $cabang->radius_cabang }}" class="form-control" placeholder="Radius"
            name="radius_cabang" id="radius_cabang">
    </div>
    <div class="row">
        <div class="col-12">
            <div class="form-group">
                <div class="modal-footer">
                    <button type="button" class="btn me-auto" data-bs-dismiss="modal">Tutup</button>
                    <button class="btn btn-primary">Simpan</button>
                </div>
            </div>
        </div>
    </div>
</form>

<script>
    $(function() {
        $('#frmCabangEdit').submit(function() {
            var kode_cabang = $('#frmCabangEdit').find('#kode_cabang').val();
            var nama_cabang = $('#frmCabangEdit').find('#nama_cabang').val();
            var lokasi_cabang = $('#frmCabangEdit').find('#lokasi_cabang').val();
            var lokasi2_cabang = $('#frmCabangEdit').find('#lokasi2_cabang').val(); // Tambahkan validasi jika perlu, tapi opsional
            var radius_cabang = $('#frmCabangEdit').find('#radius_cabang').val();

            if (kode_cabang == "") {
                Swal.fire({
                    title: "Warning!",
                    text: "Kode Cabang belum terisi",
                    icon: "warning",
                    confirmButtonText: 'Ok'
                }).then((result) => {
                    $('#kode_cabang').focus();
                });
                return false;
            } else if (nama_cabang == "") {
                Swal.fire({
                    title: "Warning!",
                    text: "Nama Cabang belum terisi",
                    icon: "warning",
                    confirmButtonText: 'Ok'
                }).then((result) => {
                    $('#nama_cabang').focus();
                });
                return false;
            } else if (lokasi_cabang == "") {
                Swal.fire({
                    title: "Warning!",
                    text: "Lokasi Cabang belum terisi",
                    icon: "warning",
                    confirmButtonText: 'Ok'
                }).then((result) => {
                    $('#lokasi_cabang').focus();
                });
                return false;
            } else if (radius_cabang == "") {
                Swal.fire({
                    title: "Warning!",
                    text: "Radius Cabang belum terisi",
                    icon: "warning",
                    confirmButtonText: 'Ok'
                }).then((result) => {
                    $('#radius_cabang').focus();
                });
                return false;
            }
            // Jika lokasi2_cabang diisi, bisa tambahkan validasi format koordinat jika perlu, tapi untuk sekarang opsional
        });
    });
</script>