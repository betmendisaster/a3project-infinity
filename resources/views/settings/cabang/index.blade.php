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
                        Data Area Presensi
                    </h2>
                </div>
                {{-- <div class="col">
                    <div class="font-weight-medium">
                        {{ $totalUsers }}
                    </div>
                    <div class="text-secondary">
                        Karyawan
                    </div>
                </div> --}}
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
                                    <a href="#" class="btn btn-primary" id="add-cabang"><svg
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
                                    <form action="/settings/cabang" method="GET">
                                        <div class="row">
                                            <div class="col-10">
                                                <select name="kode_cabang" id="" class="form-select">
                                                    <option value="">Semua Cabang Area</option>
                                                </select>
                                            </div>

                                            <div class="col-2">
                                                <div class="form-group">
                                                    <button type="submit" class="btn btn-primary"><svg
                                                            xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                            viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                                            class="icon icon-tabler icons-tabler-outline icon-tabler-search">
                                                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                            <path d="M10 10m-7 0a7 7 0 1 0 14 0a7 7 0 1 0 -14 0" />
                                                            <path d="M21 21l-6 -6" />
                                                        </svg>Cari</button>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                            <div class="row mt-2">
                                <div class="col-12">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th>No</th>
                                                <th>Kode Area</th>
                                                <th>Nama Area</th>
                                                <th>Area Presensi/Titik Kordinat</th>
                                                <th>Area Presensi/Titik Kordinat 2</th>
                                                <th>Radius</th>
                                                <th>Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($cabang as $d)
                                                <tr>
                                                    <td>{{ $loop->iteration }}</td>
                                                    <td>{{ $d->kode_cabang }}</td>
                                                    <td>{{ $d->nama_cabang }}</td>
                                                    <td>{{ $d->lokasi_cabang }}</td>
                                                    <td>{{ $d->lokasi2_cabang }}</td>
                                                    <td>{{ $d->radius_cabang }} Meter</td>
                                                    <td>
                                                        <div class="btn-group">
                                                            <a href="#" class="edit btn btn-info btn-sm"
                                                                kode_cabang="{{ $d->kode_cabang }}">
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
                                                            <form action="/settings/cabang/{{ $d->kode_cabang }}/delete"
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
    {{-- MODAL ADD DATA CABANG AREA PRESENSI --}}
    <div class="modal modal-blur fade" id="modal-addCabang" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Data Cabang</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="/settings/cabang/store" method="POST" id="frmCabang">
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
                                    <input type="text" value="" class="form-control" placeholder="Kode Cabang"
                                        name="kode_cabang" id="kode_cabang">
                                </div>
                            </div>
                        </div>
                        {{-- NAMA CABANG --}}
                        <div class="input-icon mb-3">
                            <span class="input-icon-addon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round"
                                    class="icon icon-tabler icons-tabler-outline icon-tabler-access-point">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                    <path d="M12 12l0 .01" />
                                    <path d="M14.828 9.172a4 4 0 0 1 0 5.656" />
                                    <path d="M17.657 6.343a8 8 0 0 1 0 11.314" />
                                    <path d="M9.168 14.828a4 4 0 0 1 0 -5.656" />
                                    <path d="M6.337 17.657a8 8 0 0 1 0 -11.314" />
                                </svg>
                            </span>
                            <input type="text" value="" class="form-control" placeholder="Cabang Area Presensi"
                                name="nama_cabang" id="nama_cabang">
                        </div>
                        {{-- LOKASI AREA CABANG --}}
                        <div class="input-icon mb-3">
                            <span class="input-icon-addon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round"
                                    class="icon icon-tabler icons-tabler-outline icon-tabler-pin">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                    <path d="M15 4.5l-4 4l-4 1.5l-1.5 1.5l7 7l1.5 -1.5l1.5 -4l4 -4" />
                                    <path d="M9 15l-4.5 4.5" />
                                    <path d="M14.5 4l5.5 5.5" />
                                </svg>
                            </span>
                            <input type="text" value="" class="form-control"
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
                            <input type="text" value="" class="form-control" placeholder="Lokasi Cabang 2 (Opsional)" name="lokasi2_cabang" id="lokasi2_cabang">
                        </div>
                        {{-- RADIUS AREA CABANG --}}
                        <div class="input-icon mb-3">
                            <span class="input-icon-addon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round"
                                    class="icon icon-tabler icons-tabler-outline icon-tabler-gradienter">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                    <path d="M3.227 14c.917 4 4.497 7 8.773 7c4.277 0 7.858 -3 8.773 -7" />
                                    <path d="M20.78 10a9 9 0 0 0 -8.78 -7a8.985 8.985 0 0 0 -8.782 7" />
                                    <path d="M12 12m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0" />
                                </svg>
                            </span>
                            <input type="text" value="" class="form-control" placeholder="Radius"
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
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL UPDATE DATA CABANG AREA PRESENSI --}}

    <div class="modal modal-blur fade" id="modal-editCabang" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Data Cabang</h5>
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
            $('#add-cabang').click(function() {
                $("#modal-addCabang").modal("show");
            });

            $('.edit').click(function() {
                var kode_cabang = $(this).attr('kode_cabang');
                $.ajax({
                    type: 'POST',
                    url: '/settings/cabang/edit',
                    cache: false,
                    data: {
                        _token: "{{ csrf_token() }}",
                        kode_cabang: kode_cabang
                    },
                    success: function(respond) {
                        $("#loadEditForm").html(respond);
                    }
                });
                $("#modal-editCabang").modal("show");
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
            $('#frmCabang').submit(function() {
                var kode_cabang = $('#kode_cabang').val();
                var nama_cabang = $('#nama_cabang').val();
                var lokasi_cabang = $('#lokasi_cabang').val();
                var radius_cabang = $('#radius_cabang').val();

                if (kode_cabang == "") {
                    // alert('NRP Harus diIsi');
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
                    // alert('NRP Harus diIsi');
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
                    // alert('NRP Harus diIsi');
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
                    // alert('NRP Harus diIsi');
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
            });
        });
    </script>
@endpush
