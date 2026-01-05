<form action="/settings/updateJamKerja" method="POST" id="frmJK">
    @csrf
    {{-- Kode Cabang --}}
    <div class="row">
        <div class="col-12">
            <label for="kode_jam_kerja" class="form-label">Kode Jam Kerja</label>
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
                <input type="text" value="{{ $jamkerja->kode_jam_kerja }}" class="form-control"
                    placeholder="Masukkan Kode Jam Kerja" name="kode_jam_kerja" id="kode_jam_kerja" readonly>
            </div>
        </div>

        <div class="col-12">
            <label for="nama_jam_kerja" class="form-label">Nama Jam Kerja</label>
            <div class="input-icon mb-3">
                <span class="input-icon-addon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                        fill="currentColor" class="icon icon-tabler icons-tabler-filled icon-tabler-label">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                        <path
                            d="M16.52 6a2 2 0 0 1 1.561 .75l3.7 4.625a1 1 0 0 1 0 1.25l-3.7 4.624a2 2 0 0 1 -1.561 .751h-10.52a3 3 0 0 1 -3 -3v-6a3 3 0 0 1 3 -3z" />
                    </svg>
                </span>
                <input type="text" value="{{ $jamkerja->nama_jam_kerja }}" class="form-control"
                    placeholder="Masukkan Nama Jam Kerja" name="nama_jam_kerja" id="nama_jam_kerja">
            </div>
        </div>

        <div class="col-12">
            <label for="awal_jam_masuk" class="form-label">Awal Jam Masuk (In)</label>
            <div class="input-icon mb-3">
                <span class="input-icon-addon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-clock-24">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                        <path d="M3 12a9 9 0 0 0 5.998 8.485m12.002 -8.485a9 9 0 1 0 -18 0" />
                        <path d="M12 7v5" />
                        <path d="M12 15h2a1 1 0 0 1 1 1v1a1 1 0 0 1 -1 1h-1a1 1 0 0 0 -1 1v1a1 1 0 0 0 1 1h2" />
                        <path d="M18 15v2a1 1 0 0 0 1 1h1" />
                        <path d="M21 15v6" />
                    </svg>
                </span>
                <input type="time" value="{{ $jamkerja->awal_jam_masuk }}" class="form-control"
                    placeholder="Masukkan Awal Jam Masuk" name="awal_jam_masuk" id="awal_jam_masuk">
            </div>
        </div>

        <div class="col-12">
            <label for="jam_masuk" class="form-label">Jam Masuk (In)</label>
            <div class="input-icon mb-3">
                <span class="input-icon-addon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                        fill="currentColor" class="icon icon-tabler icons-tabler-filled icon-tabler-player-record">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                        <path d="M8 5.072a8 8 0 1 1 -3.995 7.213l-.005 -.285l.005 -.285a8 8 0 0 1 3.995 -6.643z" />
                    </svg>
                </span>
                <input type="time" value="{{ $jamkerja->jam_masuk }}" class="form-control"
                    placeholder="Masukkan Jam Masuk" name="jam_masuk" id="jam_masuk">
            </div>
        </div>

        <div class="col-12">
            <label for="akhir_jam_masuk" class="form-label">Akhir Jam Masuk (In)</label>
            <div class="input-icon mb-3">
                <span class="input-icon-addon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-clock-24">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                        <path d="M3 12a9 9 0 0 0 5.998 8.485m12.002 -8.485a9 9 0 1 0 -18 0" />
                        <path d="M12 7v5" />
                        <path d="M12 15h2a1 1 0 0 1 1 1v1a1 1 0 0 1 -1 1h-1a1 1 0 0 0 -1 1v1a1 1 0 0 0 1 1h2" />
                        <path d="M18 15v2a1 1 0 0 0 1 1h1" />
                        <path d="M21 15v6" />
                    </svg>
                </span>
                <input type="time" value="{{ $jamkerja->akhir_jam_masuk }}" class="form-control"
                    placeholder="Masukkan Akhir Jam Masuk" name="akhir_jam_masuk" id="akhir_jam_masuk">
            </div>
        </div>

        {{-- BARU: Kolom untuk Awal Jam Pulang --}}
        <div class="col-12">
            <label for="awal_jam_pulang" class="form-label">Awal Jam Pulang (Out)</label>
            <div class="input-icon mb-3">
                <span class="input-icon-addon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-clock-24">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                        <path d="M3 12a9 9 0 0 0 5.998 8.485m12.002 -8.485a9 9 0 1 0 -18 0" />
                        <path d="M12 7v5" />
                        <path d="M12 15h2a1 1 0 0 1 1 1v1a1 1 0 0 1 -1 1h-1a1 1 0 0 0 -1 1v1a1 1 0 0 0 1 1h2" />
                        <path d="M18 15v2a1 1 0 0 0 1 1h1" />
                        <path d="M21 15v6" />
                    </svg>
                </span>
                <input type="time" value="{{ $jamkerja->awal_jam_pulang ?? '' }}" class="form-control"
                    placeholder="Masukkan Awal Jam Pulang" name="awal_jam_pulang" id="awal_jam_pulang">
            </div>
        </div>

        {{-- BARU: Kolom untuk Jam Pulang (sudah ada, tapi label diperjelas) --}}
        <div class="col-12">
            <label for="jam_pulang" class="form-label">Jam Pulang (Out)</label>
            <div class="input-icon mb-3">
                <span class="input-icon-addon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round"
                        class="icon icon-tabler icons-tabler-outline icon-tabler-player-record">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                        <path d="M12 12m-7 0a7 7 0 1 0 14 0a7 7 0 1 0 -14 0" />
                    </svg>
                </span>
                <input type="time" value="{{ $jamkerja->jam_pulang }}" class="form-control"
                    placeholder="Masukkan Jam Pulang" name="jam_pulang" id="jam_pulang">
            </div>
        </div>

        {{-- BARU: Kolom untuk Akhir Jam Pulang --}}
        <div class="col-12">
            <label for="akhir_jam_pulang" class="form-label">Akhir Jam Pulang (Out)</label>
            <div class="input-icon mb-3">
                <span class="input-icon-addon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-clock-24">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                        <path d="M3 12a9 9 0 0 0 5.998 8.485m12.002 -8.485a9 9 0 1 0 -18 0" />
                        <path d="M12 7v5" />
                        <path d="M12 15h2a1 1 0 0 1 1 1v1a1 1 0 0 1 -1 1h-1a1 1 0 0 0 -1 1v1a1 1 0 0 0 1 1h2" />
                        <path d="M18 15v2a1 1 0 0 0 1 1h1" />
                        <path d="M21 15v6" />
                    </svg>
                </span>
                <input type="time" value="{{ $jamkerja->akhir_jam_pulang ?? '' }}" class="form-control"
                    placeholder="Masukkan Akhir Jam Pulang" name="akhir_jam_pulang" id="akhir_jam_pulang">
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-12">
            <div class="form-group">
                <div class="modal-footer">
                    <button type="button" class="btn me-auto" data-bs-dismiss="modal">Tutup</button>
                    <button class="btn btn-primary" type="submit">Simpan</button>
                </div>
            </div>
        </div>
    </div>
</form>

<script>
    // Validasi shift malam: Jika jam_pulang < jam_masuk, tampilkan peringatan dan update nama
    document.getElementById('frmJK').addEventListener('submit', function(e) {
        const jamMasuk = document.getElementById('jam_masuk').value;
        const jamPulang = document.getElementById('jam_pulang').value;
        const namaJamKerja = document.getElementById('nama_jam_kerja');

        if (jamPulang && jamMasuk && jamPulang < jamMasuk) {
            // Tambahkan indikator shift malam
            if (!namaJamKerja.value.includes('(Shift Malam)')) {
                namaJamKerja.value += ' (Shift Malam)';
            }
            alert('Ini adalah shift malam (jam pulang lebih awal dari jam masuk). Pastikan pengaturan absen mendukung out di hari berikutnya.');
        }
    });

    // Real-time check saat input berubah
    document.getElementById('jam_pulang').addEventListener('change', function() {
        const jamMasuk = document.getElementById('jam_masuk').value;
        const jamPulang = this.value;
        const namaJamKerja = document.getElementById('nama_jam_kerja');

        if (jamPulang && jamMasuk && jamPulang < jamMasuk) {
            if (!namaJamKerja.value.includes('(Shift Malam)')) {
                namaJamKerja.value += ' (Shift Malam)';
            }
        } else {
            // Hapus indikator jika tidak lagi shift malam
            namaJamKerja.value = namaJamKerja.value.replace(' (Shift Malam)', '');
        }
    });
</script>