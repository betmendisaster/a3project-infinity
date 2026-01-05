@extends('layouts.admin.tabler')
@section('content')
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row g-2 align-items-center">
                <div class="col">
                    <!-- Page pre-title -->
                    <div class="page-pretitle">
                        PT. Hasnur Riung Sinergi
                    </div>
                    <h2 class="page-title">
                        Setting Jam Kerja
                    </h2>
                </div>
            </div>
        </div>
    </div>
    <div class="page-body">
        <div class="container-xl">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-12">
                                    @if (Session::get('success'))
                                        <div class="alert alert-success">
                                            {{ Session::get('success') }}
                                        </div>
                                    @endif

                                    @if (Session::get('warning'))
                                        <div class="alert alert-warning">
                                            {{ Session::get('warning') }}
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-12">
                                    <a href="#" class="btn btn-primary" id="add-jk"><svg
                                            xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                            stroke-linecap="round" stroke-linejoin="round"
                                            class="icon icon-tabler icons-tabler-outline icon-tabler-square-rounded-plus">
                                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                            <path d="M12 3c7.2 0 9 1.8 9 9s-1.8 9 -9 9s-9 -1.8 -9 -9s1.8 -9 9 -9z" />
                                            <path d="M15 12h-6" />
                                            <path d="M12 9v6" />
                                        </svg>Tambah Data</a>
                                </div>
                            </div>

                            <div class="row mt-2">
                                <div class="col-12">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th>No</th>
                                                <th>Kode Jam Kerja</th>
                                                <th>Nama Jam Kerja</th>
                                                <th>Awal Jam In</th>
                                                <th>Jam In</th>
                                                <th>Akhir Jam In</th>
                                                <th>Awal Jam Out</th>
                                                <th>Jam Out</th>
                                                <th>Akhir Jam Out</th>
                                                <th>Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($jam_kerja as $d)
                                                <tr>
                                                    <td>{{ $loop->iteration }}</td>
                                                    <td>{{ $d->kode_jam_kerja }}</td>
                                                    <td>{{ $d->nama_jam_kerja }}</td>
                                                    <td>{{ $d->awal_jam_masuk }}</td>
                                                    <td>{{ $d->jam_masuk }}</td>
                                                    <td>{{ $d->akhir_jam_masuk }}</td>
                                                    <td>{{ $d->awal_jam_pulang }}</td>
                                                    <td>{{ $d->jam_pulang }}</td>
                                                    <td>{{ $d->akhir_jam_pulang }}</td>
                                                    <td>
                                                        <div class="btn-group">
                                                            <a href="#" class="edit btn btn-info btn-sm"
                                                                kode_jam_kerja="{{ $d->kode_jam_kerja }}">
                                                                <svg xmlns="http://www.w3.org/2000/svg" width="24"
                                                                    height="24" viewBox="0 0 24 24" fill="none"
                                                                    stroke="currentColor" stroke-width="2"
                                                                    stroke-linecap="round" stroke-linejoin="round"
                                                                    class="icon icon-tabler icons-tabler-outline icon-tabler-user-edit">
                                                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                                    <path d="M8 7a4 4 0 1 0 8 0a4 4 0 0 0 -8 0" />
                                                                    <path d="M6 21v-2a4 4 0 0 1 4 -4h3.5" />
                                                                    <path
                                                                        d="M18.42 15.61a2.1 2.1 0 0 1 2.97 2.97l-3.39 3.42h-3v-3l3.42 -3.39z" />
                                                                </svg>
                                                            </a>
                                                            <form
                                                                action="/settings/jamKerja/{{ $d->kode_jam_kerja }}/delete"
                                                                method="POST" style="margin-left:5px;">
                                                                @csrf
                                                                {{-- @method('DELETE') --}}
                                                                <a class="btn btn-danger btn-sm delete-confirm">
                                                                    <svg xmlns="http://www.w3.org/2000/svg" width="24"
                                                                        height="24" viewBox="0 0 24 24" fill="none"
                                                                        stroke="currentColor" stroke-width="2"
                                                                        stroke-linecap="round" stroke-linejoin="round"
                                                                        class="icon icon-tabler icons-tabler-outline icon-tabler-user-x">
                                                                        <path stroke="none" d="M0 0h24v24H0z"
                                                                            fill="none" />
                                                                        <path d="M8 7a4 4 0 1 0 8 0a4 4 0 0 0 -8 0" />
                                                                        <path d="M6 21v-2a4 4 0 0 1 4 -4h3.5" />
                                                                        <path d="M22 22l-5 -5" />
                                                                        <path d="M17 22l5 -5" />
                                                                    </svg>
                                                                </a>
                                                            </form>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal modal-blur fade" id="modal-addjk" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Data Jam Kerja</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="/settings/storeJamKerja" method="POST" id="frmJK">
                        @csrf
                        {{-- Kode Cabang --}}
                        <div class="row">
                            <div class="col-12">
                                <div class="input-icon mb-3">
                                    <span class="input-icon-addon">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                            stroke-linecap="round" stroke-linejoin="round"
                                            class="icon icon-tabler icons-tabler-outline icon-tabler-id">
                                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                            <path
                                                d="M3 4m0 3a3 3 0 0 1 3 -3h12a3 3 0 0 1 3 3v10a3 3 0 0 1 -3 3h-12a3 3 0 0 1 -3 -3z" />
                                            <path d="M9 10m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0" />
                                            <path d="M15 8l2 0" />
                                            <path d="M15 12l2 0" />
                                            <path d="M7 16l10 0" />
                                        </svg>
                                    </span>
                                    <input type="text" value="" class="form-control"
                                        placeholder="Kode Jam Kerja" name="kode_jam_kerja" id="kode_jam_kerja">
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="input-icon mb-3">
                                    <span class="input-icon-addon">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                            viewBox="0 0 24 24" fill="currentColor"
                                            class="icon icon-tabler icons-tabler-filled icon-tabler-label">
                                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                            <path
                                                d="M16.52 6a2 2 0 0 1 1.561 .75l3.7 4.625a1 1 0 0 1 0 1.25l-3.7 4.624a2 2 0 0 1 -1.561 .751h-10.52a3 3 0 0 1 -3 -3v-6a3 3 0 0 1 3 -3z" />
                                        </svg>
                                    </span>
                                    <input type="text" value="" class="form-control"
                                        placeholder="Nama Jam Kerja" name="nama_jam_kerja" id="nama_jam_kerja">
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="input-icon mb-3">
                                    <span class="input-icon-addon">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                            stroke-linecap="round" stroke-linejoin="round"
                                            class="icon icon-tabler icons-tabler-outline icon-tabler-clock-24">
                                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                            <path d="M3 12a9 9 0 0 0 5.998 8.485m12.002 -8.485a9 9 0 1 0 -18 0" />
                                            <path d="M12 7v5" />
                                            <path
                                                d="M12 15h2a1 1 0 0 1 1 1v1a1 1 0 0 1 -1 1h-1a1 1 0 0 0 -1 1v1a1 1 0 0 0 1 1h2" />
                                            <path d="M18 15v2a1 1 0 0 0 1 1h1" />
                                            <path d="M21 15v6" />
                                        </svg>
                                    </span>
                                    <input type="text" value="" class="form-control" placeholder="Awal Jam In"
                                        name="awal_jam_masuk" id="awal_jam_masuk">
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="input-icon mb-3">
                                    <span class="input-icon-addon">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                            viewBox="0 0 24 24" fill="currentColor"
                                            class="icon icon-tabler icons-tabler-filled icon-tabler-player-record">
                                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                            <path
                                                d="M8 5.072a8 8 0 1 1 -3.995 7.213l-.005 -.285l.005 -.285a8 8 0 0 1 3.995 -6.643z" />
                                        </svg>
                                    </span>
                                    <input type="text" value="" class="form-control" placeholder="Jam In"
                                        name="jam_masuk" id="jam_masuk">
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="input-icon mb-3">
                                    <span class="input-icon-addon">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                            stroke-linecap="round" stroke-linejoin="round"
                                            class="icon icon-tabler icons-tabler-outline icon-tabler-clock-24">
                                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                            <path d="M3 12a9 9 0 0 0 5.998 8.485m12.002 -8.485a9 9 0 1 0 -18 0" />
                                            <path d="M12 7v5" />
                                            <path
                                                d="M12 15h2a1 1 0 0 1 1 1v1a1 1 0 0 1 -1 1h-1a1 1 0 0 0 -1 1v1a1 1 0 0 0 1 1h2" />
                                            <path d="M18 15v2a1 1 0 0 0 1 1h1" />
                                            <path d="M21 15v6" />
                                        </svg>
                                    </span>
                                    <input type="text" value="" class="form-control" placeholder="Akhir Jam In"
                                        name="akhir_jam_masuk" id="akhir_jam_masuk">
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="input-icon mb-3">
                                    <span class="input-icon-addon">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                            stroke-linecap="round" stroke-linejoin="round"
                                            class="icon icon-tabler icons-tabler-outline icon-tabler-player-record">
                                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                            <path d="M12 12m-7 0a7 7 0 1 0 14 0a7 7 0 1 0 -14 0" />
                                        </svg>
                                    </span>
                                    <input type="text" value="" class="form-control" placeholder="Jam Out"
                                        name="jam_pulang" id="jam_pulang">
                                </div>
                            </div>
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
                </div>
            </div>
        </div>
    </div>
    <div class="modal modal-blur fade" id="modal-editJK" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Data Jam Kerja</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="loadEditForm">

                </div>
            </div>
        </div>
    </div>
@endsection
@push('myscript')
    <script>
        $(function() {

            $('#add-jk').click(function() {
                $("#modal-addjk").modal("show");
            });

            $(".delete-confirm").click(function(e) {
                var form = $(this).closest('form');
                e.preventDefault();
                Swal.fire({
                    title: "Apakah sudah yakin ingin menghapus Data ini ?",
                    text: "Jika ya maka Data akan terhapus Permanent",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#3085d6",
                    cancelButtonColor: "#d33",
                    confirmButtonText: "Yes, delete it!"
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                        Swal.fire({
                            title: "Deleted!",
                            text: "Data berhasil di Hapus.",
                            icon: "success"
                        });
                    }
                });
            });

            $('#frmJK').submit(function() {
                var kode_jam_kerja = $('#kode_jam_kerja').val();
                var nama_jam_kerja = $('#nama_jam_kerja').val();
                var awal_jam_masuk = $('#awal_jam_masuk').val();
                var jam_masuk = $('#jam_masuk').val();
                var akhir_jam_masuk = $('#akhir_jam_masuk').val();
                var jam_pulang = $('#jam_pulang').val();

                if (kode_jam_kerja == "") {
                    Swal.fire({
                        title: "Warning!",
                        text: "Kode Jam Kerja belum terisi",
                        icon: "warning",
                        confirmButtonText: 'Ok'
                    }).then((result) => {
                        $('#kode_jam_kerja').focus();
                    });

                    return false;
                } else if (nama_jam_kerja == "") {
                    Swal.fire({
                        title: "Warning!",
                        text: "Nama Jam Kerja belum terisi",
                        icon: "warning",
                        confirmButtonText: 'Ok'
                    }).then((result) => {
                        $('#nama_jam_kerja').focus();
                    });

                    return false;

                } else if (awal_jam_masuk == "") {
                    Swal.fire({
                        title: "Warning!",
                        text: "Awal Jam Masuk belum terisi",
                        icon: "warning",
                        confirmButtonText: 'Ok'
                    }).then((result) => {
                        $('#awal_jam_masuk').focus();
                    });

                    return false;
                } else if (jam_masuk == "") {
                    Swal.fire({
                        title: "Warning!",
                        text: "Jam In belum terisi",
                        icon: "warning",
                        confirmButtonText: 'Ok'
                    }).then((result) => {
                        $('#jam_masuk').focus();
                    });

                    return false;
                } else if (akhir_jam_masuk == "") {
                    Swal.fire({
                        title: "Warning!",
                        text: "Akhir Jam In belum terisi",
                        icon: "warning",
                        confirmButtonText: 'Ok'
                    }).then((result) => {
                        $('#akhir_jam_masuk').focus();
                    });

                    return false;
                } else if (jam_pulang == "") {
                    Swal.fire({
                        title: "Warning!",
                        text: "Jam Out belum terisi",
                        icon: "warning",
                        confirmButtonText: 'Ok'
                    }).then((result) => {
                        $('#jam_pulang').focus();
                    });

                    return false;
                }
            });

            $('.edit').click(function() {
                var kode_jam_kerja = $(this).attr('kode_jam_kerja');
                $.ajax({
                    type: 'POST',
                    url: '/settings/editJamKerja',
                    cache: false,
                    data: {
                        _token: "{{ csrf_token() }}",
                        kode_jam_kerja: kode_jam_kerja
                    },
                    success: function(respond) {
                        $("#loadEditForm").html(respond);
                    }
                });
                $("#modal-editJK").modal("show");
            });
        });
    </script>
@endpush
