@if ($presensi->count() == 0)
<tr>
    <td colspan="12" class="text-center text-muted">Tidak ada data</td>
</tr>
@else
@foreach ($presensi as $d)
<tr>
    {{-- NO --}}
    <td>{{ $loop->iteration }}</td>

    {{-- NRP --}}
    <td>{{ $d->nrp }}</td>

    {{-- NAMA --}}
    <td>{{ $d->nama }}</td>

    {{-- DEPARTMENT (KODE SAJA) --}}
    <td>{{ $d->kode_dept }}</td>

    {{-- ROSTER / SHIFT --}}
    <td>
        {{ $d->nama_jam_kerja ?? '-' }}
        @if($d->jam_masuk && $d->jam_pulang)
            <br>
            <small class="text-muted">
                {{ $d->jam_masuk }} - {{ $d->jam_pulang }}
            </small>
        @endif
    </td>

    {{-- JAM IN --}}
    <td>{{ $d->jam_in ?? '-' }}</td>

    {{-- FOTO IN --}}
    <td>
        @if ($d->foto_in)
            <img src="{{ asset('storage/uploads/absensi/'.$d->foto_in) }}"
                 width="40" class="rounded">
        @endif
    </td>

    {{-- JAM OUT --}}
    <td>
        {!! $d->jam_out
            ? $d->jam_out
            : '<span class="badge bg-warning">Belum Absen</span>' !!}
    </td>

    {{-- FOTO OUT --}}
    <td>
        @if ($d->foto_out)
            <img src="{{ asset('storage/uploads/absensi/'.$d->foto_out) }}"
                 width="40" class="rounded">
        @endif
    </td>

    {{-- KETERANGAN --}}
    <td>
        {{ $d->nama_cuti ?? '-' }}
    </td>

    {{-- STATUS --}}
    <td>
        @switch($d->status)
            @case('h') <span class="badge bg-success">Hadir</span> @break
            @case('i') <span class="badge bg-warning">Izin</span> @break
            @case('c') <span class="badge bg-info">Cuti</span> @break
            @case('s') <span class="badge bg-danger">Sakit</span> @break
        @endswitch
    </td>

    {{-- LOKASI --}}
    <td>
        <a href="#" class="btn btn-sm btn-primary location" id="{{ $d->id }}">
            📍
        </a>
    </td>
</tr>
@endforeach
@endif
