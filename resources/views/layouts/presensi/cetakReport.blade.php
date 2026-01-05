<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Report Presensi {{ $karyawan->nama }} Periode {{ $namaBulan[$bulan] }} {{ $tahun }}</title>

    <!-- Normalize or reset CSS with your favorite library -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/normalize/7.0.0/normalize.min.css">

    <!-- Load paper.css for happy printing -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/paper-css/0.4.1/paper.css">

    <!-- Set page size here: A5, A4 or A3 -->
    <!-- Set also "landscape" if you need -->
    <style>
        @page {
            size: A4
        }

        #title {
            font-family: ;
            font-weight: bold;
            font-size: 14px;
        }

        #alamat {
            font-family: ;
            font-size: 12px;
            font-style: italic;
        }

        .tableDataKaryawan {
            margin-top: 40px;
        }

        .tablePresensi {
            width: 100%;
            margin-top: 20px;
            border-collapse: collapse;

        }

        .tablePresensi tr th {
            border: 1px solid black;
            padding: 8px;
            background-color: #baafaf;
        }

        #th_tablePresensi {
            border: 1px solid black;
            padding: 3px;
            font-size: 11px;
        }

        .foto {
            width: 40px;
            height: 30px;
        }
    </style>
</head>

<!-- Set "A5", "A4" or "A3" for class name -->
<!-- Set also "landscape" if you need -->

<body class="A4">
    <?php
    function selisih($jam_in, $jam_out)
    {
        [$h, $m, $s] = explode(':', $jam_in);
        $dtAwal = mktime($h, $m, $s, '1', '1', '1');
        [$h, $m, $s] = explode(':', $jam_out);
        $dtAkhir = mktime($h, $m, $s, '1', '1', '1');
        $dtSelisih = $dtAkhir - $dtAwal;
        $totalmenit = $dtSelisih / 60;
        $jam = explode('.', $totalmenit / 60);
        $sisamenit = $totalmenit / 60 - $jam[0];
        $sisamenit2 = $sisamenit * 60;
        $jml_jam = $jam[0];
        return $jml_jam . ':' . round($sisamenit2);
    }
    ?>
    <!-- Each sheet element should have the class "sheet" -->
    <!-- "padding-**mm" is optional: you can set 10, 15, 20 or 25 -->
    <section class="sheet padding-10mm">

        <!-- Write HTML just like a web page -->
        {{-- <article>This is an A4 document.</article> --}}

        <table style="width: 100%;">
            <tr>
                <td>
                    <img src="{{ asset('assets/img/logo.png') }}" width="80" height="70" alt="">
                </td>
                <td>
                    <span id="title">
                        REPORT PRESENSI KARYAWAN<br>
                        PERIODE {{ strtoupper($namaBulan[$bulan]) }} {{ $tahun }} <br>
                        PT. HASNUR RIUNG SINERGI SITE AGM<br>
                    </span>
                    <span id="alamat">Jln. Office site AGM Block IV, Jl. A. Yani, Desa Tatakan, Kec.Tapin Selatan,
                        Kab.Tapin,
                        Kalimantan Selatan, Indonesia</span>
                </td>
            </tr>
        </table>
        <table class="tableDataKaryawan">
            <tr>
                <td>NRP</td>
                <td>:</td>
                <td>{{ $karyawan->nrp }}</td>
            </tr>
            <tr>
                <td>Nama</td>
                <td>:</td>
                <td>{{ $karyawan->nama }}</td>
            </tr>
            <tr>
                <td>Department</td>
                <td>:</td>
                <td>{{ $karyawan->nama_dept }}</td>
            </tr>
            <tr>
                <td>Jabatan</td>
                <td>:</td>
                <td>{{ $karyawan->jabatan }}</td>
            </tr>
        </table>
        <table class="tablePresensi">
            <tr>
                <th>No.</th>
                <th>Tanggal</th>
                <th>Jam In</th>
                <th>Foto</th>
                <th>Jam Out</th>
                <th>Foto</th>
                <th>Status</th>
                <th>Keterangan</th>
                <th>Jumlah Jam Kerja</th>
            </tr>
            @foreach ($presensi as $d)
                @if ($d->status == 'h')
                    @php
                        $path_in = Storage::url('uploads/absensi/' . $d->foto_in);
                        $path_out = Storage::url('uploads/absensi/' . $d->foto_out);
                        $jamterlambat = selisih($d->jam_masuk, $d->jam_in);
                        // Untuk shift malam, pastikan out ditampilkan meskipun dilakukan di hari berikutnya
                        $jam_out_display = $d->jam_out ?? 'Belum Absen';
                    @endphp
                    <tr style="text-align: center">
                        <td id="th_tablePresensi">{{ $loop->iteration }}</td>
                        <td id="th_tablePresensi"> {{ date('d-m-Y', strtotime($d->tgl_presensi)) }} </td>
                        <td id="th_tablePresensi">{{ $d->jam_in }}</td>
                        <td id="th_tablePresensi"><img src="{{ url($path_in) }}" alt="" class="foto"></td>
                        <td id="th_tablePresensi">{{ $jam_out_display }}</td>
                        <td id="th_tablePresensi"><img src="{{ url($path_out) }}" alt="" class="foto"></td>
                        <td id="th_tablePresensi">{{ $d->status }}</td>
                        <td id="th_tablePresensi">
                            @if ($d->jam_in > $d->jam_masuk)
                                Terlambat {{ $jamterlambat }} (Jam:Menit)
                            @else
                                Tepat Waktu
                            @endif
                        </td>
                        <td id="th_tablePresensi">
                            @if ($d->jam_out != null)
                                @php
                                    $jmljamkerja = selisih($d->jam_in, $d->jam_out);
                                @endphp
                            @else
                                @php
                                    $jmljamkerja = 0;
                                @endphp
                            @endif
                            {{ $jmljamkerja }} (Jam:Menit)
                        </td>
                    </tr>
                @else
                    <tr style="text-align: center">
                        <td id="th_tablePresensi">{{ $loop->iteration }}</td>
                        <td id="th_tablePresensi"> {{ date('d-m-Y', strtotime($d->tgl_presensi)) }} </td>
                        <td id="th_tablePresensi"></td>
                        <td id="th_tablePresensi"></td>
                        <td id="th_tablePresensi"></td>
                        <td id="th_tablePresensi"></td>
                        <td id="th_tablePresensi">{{ $d->status }}</td>
                        <td id="th_tablePresensi">{{ $d->keterangan }}</td>
                        <td id="th_tablePresensi"></td>
                    </tr>
                @endif
            @endforeach
        </table>

        <table width="100%" style="margin-top:100px;">
            <tr>
                <td></td>
                <td style="text-align: center;">Tapin, {{ date('d-m-Y') }}</td>
            </tr>

            <tr>
                <td style="text-align: center; vertical-align: bottom" height="100px">
                    <u>Farrel Hebat</u><br>
                    <i><b>HC Group Leader</b></i>
                </td>
                <td style="text-align: center; vertical-align: bottom;">
                    <u>Widodo Pranto</u><br>
                    <i><b>Dept. Head HCGS</b></i>
                </td>
            </tr>
        </table>
    </section>

</body>

</html>
