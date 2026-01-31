<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Report Rekap Presensi Harian {{ date('d-m-Y', strtotime($tanggal)) }}</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/normalize/7.0.0/normalize.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/paper-css/0.4.1/paper.css">
    <style>
        @page { size: A4 }
        #title { font-weight: bold; font-size: 14px; }
        #alamat { font-size: 12px; font-style: italic; }
        .tablePresensi { width: 100%; margin-top: 20px; border-collapse: collapse; font-size: 10px; text-align: center; }
        .tablePresensi th, .tablePresensi td { border: 1px solid black; padding: 8px; }
        .tablePresensi th { background-color: #baafaf; }
    </style>
</head>
<body class="A4">
    <section class="sheet padding-10mm">
        <table style="width: 100%;">
            <tr>
                <td><img src="{{ asset('assets/img/logo.png') }}" width="80" height="70" alt=""></td>
                <td>
                    <span id="title">REPORT REKAP PRESENSI KARYAWAN HARIAN<br>PERIODE {{ date('d-m-Y', strtotime($tanggal)) }}<br>PT. HASNUR RIUNG SINERGI SITE AGM</span>
                    <span id="alamat">Jln. Office site AGM Block IV, Jl. A. Yani, Desa Tatakan, Kec.Tapin Selatan, Kab.Tapin, Kalimantan Selatan, Indonesia</span>
                </td>
            </tr>
        </table>
        <table class="tablePresensi">
            <tr>
                <th>Tanggal</th>
                <th>NRP</th>
                <th>Nama</th>
                <th>Department</th>
                <th>Jabatan</th>
                <th>Jam In</th>
                <th>Jam Out</th>
                <th>Status</th>
                <th>Jam Kerja</th>
                <th>Kode Izin</th>
                <th>Keterangan</th>
            </tr>
            @php
                $totalHadir = 0;
                $totalTidakAbsen = 0;
            @endphp
            @forelse($rekap as $r)
            <tr>
                <td>{{ date('d-m-Y', strtotime($tanggal)) }}</td>
                <td>{{ $r->nrp }}</td>
                <td>{{ $r->nama }}</td>
                <td>{{ $r->kode_dept ?? 'NA' }}</td> <!-- Jika ada join department, ganti dengan nama_dept -->
                <td>{{ $r->jabatan }}</td>
                <td>{{ $r->jam_in ? date('H:i', strtotime($r->jam_in)) : 'Tidak Absen In' }}</td>
                <td>{{ $r->jam_out ? date('H:i', strtotime($r->jam_out)) : 'Tidak Absen Out' }}</td>
                <td>{{ $r->status ?? 'NA' }}</td>
                <td>{{ $r->nama_jam_kerja ?? 'NA' }}</td>
                <td>{{ $r->kode_izin ?? 'NA' }}</td>
                <td>{{ $r->keterangan ?? 'NA' }}</td>
            </tr>
            @php
                if ($r->status == 'h') $totalHadir++;
                if (!$r->jam_in) $totalTidakAbsen++;
            @endphp
            @empty
            <tr>
                <td colspan="11" style="text-align: center; color: red;">Tidak ada data absen untuk tanggal ini.</td>
            </tr>
            @endforelse
        </table>
        <!-- Tambah Summary Total (Opsional) -->
        <table class="tablePresensi" style="margin-top: 20px;">
            <tr>
                <th colspan="10" style="text-align: right;">Total Hadir</th>
                <th>{{ $totalHadir }}</th>
            </tr>
            <tr>
                <th colspan="10" style="text-align: right;">Total Tidak Absen In</th>
                <th>{{ $totalTidakAbsen }}</th>
            </tr>
        </table>
        <table width="100%" style="margin-top:100px;">
            <tr>
                <td></td>
                <td style="text-align: center;">Tapin, {{ date('d-m-Y') }}</td>
            </tr>
            <tr>
                <td style="text-align: center; vertical-align: bottom" height="100px"><u>Infinity</u><br><i><b>A3 Project</b></i></td>
                <td style="text-align: center; vertical-align: bottom;"><u>Widodo Pranoto</u><br><i><b>Dept. Head HCGS</b></i></td>
            </tr>
        </table>
    </section>
</body>
</html>