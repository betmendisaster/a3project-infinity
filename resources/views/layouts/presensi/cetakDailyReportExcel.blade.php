<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Report Rekap Presensi Periode {{ $namaBulan[$bulan] }} {{ $tahun }}</title>

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
            font-size: 10px;
            text-align: center
        }

        .tablePresensi tr th {
            border: 1px solid black;
            padding: 8px;
            background-color: #baafaf;
        }

        #td_tablePresensi {
            border: 1px solid black;
        }

        #tr_tablePresensi {
            border: 1px solid black;
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

<body class="A4 landscape">
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
                        REPORT REKAP PRESENSI KARYAWAN<br>
                        PERIODE {{ strtoupper($namaBulan[$bulan]) }} {{ $tahun }} <br>
                        PT. HASNUR RIUNG SINERGI SITE AGM<br>
                    </span>
                    <span id="alamat">Jln. Office site AGM Block IV, Jl. A. Yani, Desa Tatakan, Kec.Tapin Selatan,
                        Kab.Tapin,
                        Kalimantan Selatan, Indonesia</span>
                </td>
            </tr>
        </table>
        <table class="tablePresensi">
            <tr>
                <th rowspan="2">NRP</th>
                <th rowspan="2">Nama</th>
                <th colspan="{{ $jmlHari }}">Bulan {{ $namaBulan[$bulan] }} {{ $tahun }} </th>
                <th rowspan="2">H</th>
                {{-- <th rowspan="2">Total Telat</th> --}}
                <th rowspan="2">C</th>
                <th rowspan="2">S</th>
                <th rowspan="2">A</th>
            </tr>
            <tr id="tr_tablePresensi">
                @foreach ($rangeTanggal as $d)
                    @if ($d != null)
                        <th>{{ date('d', strtotime($d)) }}</th>
                    @endif
                @endforeach
            </tr>
            @foreach ($rekap as $r)
                <tr id="tr_tablePresensi">
                    <td id="td_tablePresensi">{{ $r->nrp }}</td>
                    <td id="td_tablePresensi">{{ $r->nama }}</td>
                    <?php
                    $jml_hadir = 0;
                    $jml_izin = 0;
                    $jml_sakit = 0;
                    $jml_cuti = 0;
                    $jml_alpa = 0;
                    $shiftMalamOut = []; // Array untuk menyimpan out shift malam yang perlu dipindah ke hari in
                    for ($i = 1; $i <= $jmlHari; $i++) {
                        $tgl = "tgl_" . $i;     
                        $datapresensi = explode("|",$r->$tgl); 
                        if($r->$tgl != NULL){
                            $status = $datapresensi[2];
                            $jam_pulang = $datapresensi[5] ?? '';
                            $jam_masuk = $datapresensi[4] ?? '';
                            $jam_out = $datapresensi[1] ?? '';
                        } else {
                            $status = "";
                            $jam_pulang = "";
                            $jam_masuk = "";
                            $jam_out = "";
                        }

                        // Jika shift malam dan out ada, simpan untuk dipindah ke hari in
                        if ($jam_pulang < $jam_masuk && !empty($jam_out)) {
                            $shiftMalamOut[$i] = $jam_out; // Simpan out di array dengan index hari
                            $jam_out = ""; // Kosongkan out di hari ini untuk menghindari duplikasi
                        }

                        if($status == "h"){
                            $jml_hadir += 1;
                            $color = "white";
                        } elseif($status == "i"){
                            $jml_izin += 1;
                            $color = "yellow";
                        } elseif($status == "c"){
                            $jml_cuti += 1;
                            $color = "white";
                        } elseif($status == "s"){
                            $jml_sakit += 1;
                            $color = "green";
                        } else {
                            $jml_alpa += 1;
                            $color = "red";
                        }
                    ?>
                    <td id="td_tablePresensi" style="background-color: {{ $color }}">
                        {{ $status }}
                    </td>
                    <?php
                        }
                    ?>
                    <td id="td_tablePresensi">{{ !empty($jml_hadir) ? $jml_hadir : '' }}</td>
                    <td id="td_tablePresensi">{{ !empty($jml_cuti) ? $jml_cuti : '' }}</td>
                    <td id="td_tablePresensi">{{ !empty($jml_sakit) ? $jml_sakit : '' }}</td>
                    <td id="td_tablePresensi">{{ !empty($jml_alpa) ? $jml_alpa : '' }}</td>
                </tr>
            @endforeach
        </table>

        <table width="100%" style="margin-top:100px;">
            <tr>
                <td></td>
                <td style="text-align: center;">Tapin, {{ date('d-m-Y') }}</td>
            </tr>

            <tr>
                <td style="text-align: center; vertical-align: bottom" height="100px">
                    <u>Infinity</u><br>
                    <i><b>A3 Project</b></i>
                </td>
                <td style="text-align: center; vertical-align: bottom;">
                    <u>Widodo Pranoto</u><br>
                    <i><b>Dept. Head HCGS</b></i>
                </td>
            </tr>
        </table>
    </section>

</body>

</html>
