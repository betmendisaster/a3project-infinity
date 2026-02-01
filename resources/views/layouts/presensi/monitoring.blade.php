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
                        Monitoring Presensi
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
                                    <div class="input-icon mb-3">
                                        <span class="input-icon-addon">
                                            <!-- Download SVG icon from http://tabler-icons.io/i/user -->
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                                stroke-linecap="round" stroke-linejoin="round"
                                                class="icon icon-tabler icons-tabler-outline icon-tabler-calendar-check">
                                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                <path
                                                    d="M11.5 21h-5.5a2 2 0 0 1 -2 -2v-12a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v6" />
                                                <path d="M16 3v4" />
                                                <path d="M8 3v4" />
                                                <path d="M4 11h16" />
                                                <path d="M15 19l2 2l4 -4" />
                                            </svg>
                                        </span>
                                        <input type="text"
                                            id="tanggal"
                                            class="form-control"
                                            placeholder="Tanggal Presensi"
                                            autocomplete="off">
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-12">
                                    <table class="table table-striped table-hover">
                                        <thead>
                                            <tr>
                                                <th>No</th>
                                                <th>NRP</th>
                                                <th>Nama</th>
                                                <th>Department</th>
                                                <th>Roster</th>
                                                <th>Jam Masuk</th>
                                                <th>Foto</th>
                                                <th>Jam Pulang</th>
                                                <th>Foto</th>
                                                <th>Keterangan</th>
                                                <th>Status</th>
                                                <th></th>
                                            </tr>
                                        </thead>
                                        <tbody id="loadPresensi">
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
    <div class="modal modal-blur fade" id="modal-location" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Lokasi Presensi User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="loadLocation"></div>
            </div>
        </div>
    </div>
@endsection
@push('myscript')
<script>
document.addEventListener('DOMContentLoaded', function () {

    if (typeof flatpickr === "undefined") {
        console.error("Flatpickr BELUM ter-load");
        return;
    }

    flatpickr("#tanggal", {
        dateFormat: "Y-m-d",
        defaultDate: "{{ date('Y-m-d') }}",
        onReady: function(selectedDates, dateStr) {
            loadPresensi(dateStr);
        },
        onChange: function(selectedDates, dateStr) {
            loadPresensi(dateStr);
        }
    });

    function loadPresensi(tanggal) {
        console.log("Request presensi:", tanggal);

        $.ajax({
            type: 'POST',
            url: '/getpresensi',
            data: {
                _token: "{{ csrf_token() }}",
                tanggal: tanggal
            },
            success: function(respond) {
                if (respond.trim() === '') {
                    $('#loadPresensi').html(
                        '<tr><td colspan="12" class="text-center text-muted">Tidak ada data</td></tr>'
                    );
                } else {
                    $('#loadPresensi').html(respond);
                }
            },
            error: function(xhr) {
                console.error(xhr.responseText);
                $('#loadPresensi').html(
                    '<tr><td colspan="12" class="text-danger text-center">Error load data</td></tr>'
                );
            }
        });
    }

    // tombol lokasi
    $(document).on('click', '.location', function(e) {
        e.preventDefault();
        let id = $(this).attr('id');

        $.ajax({
            type: 'POST',
            url: '/showlocation',
            data: {
                _token: "{{ csrf_token() }}",
                id: id
            },
            success: function(respond) {
                $('#loadLocation').html(respond);
                $('#modal-location').modal('show');
            }
        });
    });

});
</script>
@endpush

