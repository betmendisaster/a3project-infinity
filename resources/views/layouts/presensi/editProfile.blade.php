@extends('layouts.absensi')

@section('content')
    <div class="relative flex flex-col items-center bg-white shadow-lg rounded-lg p-4 w-full max-w-xs md:max-w-md lg:max-w-lg mx-auto pb-20"
        style="display: none;" id="content">
        <div>
            @php
                $messagesuccess = Session::get('success');
                $messageerror = Session::get('error');
            @endphp
            @if (Session::get('success'))
                <div class="p-4 mb-4 text-sm text-green-800 rounded-lg bg-green-50 dark:bg-gray-800 dark:text-green-400"
                    role="alert">
                    <span class="font-medium">{{ $messagesuccess }}</span>
                </div>
            @endif
            @if (Session::get('error'))
                <div class="p-4 mb-4 text-sm text-red-800 rounded-lg bg-red-50 dark:bg-gray-800 dark:text-red-400"
                    role="alert">
                    <span class="font-medium">{{ $messageerror }}</span>
                </div>
            @endif
        </div>
        <h1 class="text-2xl font-bold text-gray-700 mb-4 self-start">Edit Profile</h1>
        <form id="profileForm" action="/presensi/{{ $karyawan->nrp }}/updateProfile" method="POST"
            enctype="multipart/form-data" class="w-full">
            @csrf
            <div class="relative flex flex-col items-center bg-white shadow-lg rounded-lg p-4 w-full max-w-xs md:max-w-md lg:max-w-lg mx-auto"
                id="content">
                <div class="w-full p-4">
                    <div class="space-y-4">
                        <!-- Preview Foto Profile Saat Ini (Bulat, Klik untuk Full Size) -->
                        <div class="flex flex-col items-center">
                            <img id="currentFoto" 
                                src="{{ asset('storage/uploads/karyawan/fotoProfile/' . $karyawan->foto) }}" 
                                class="w-40 h-40 rounded-full object-cover border-4 border-white shadow-md cursor-pointer" 
                                alt="Foto Profile Saat Ini" 
                                onclick="openCurrentFotoModal()">
                            <p class="text-xs text-gray-500 mt-1">Klik untuk melihat full size</p>
                        </div>

                        <!-- Opsi Edit Foto Baru -->
                        <div class="flex flex-col items-center">
                            <img id="previewFoto"
                                class="hidden w-40 h-40 rounded-full object-cover border-4 border-white shadow-md cursor-pointer"
                                alt="Preview Foto Baru" 
                                onclick="openPreviewModal()">

                            <input type="file" id="fotoInput" accept="image/*" class="hidden">

                            <input type="hidden" name="foto_cropped" id="fotoCropped">

                            <div class="flex space-x-2 mt-3">
                                <button type="button"
                                    onclick="document.getElementById('fotoInput').click()"
                                    class="bg-blue-500 text-white px-4 py-1 rounded hover:bg-blue-700">
                                    Pilih Foto Baru
                                </button>
                            </div>
                            <p class="text-xs text-gray-500 mt-1">Geser & zoom untuk menyesuaikan</p>
                        </div>

                        <div>
                            <label for="nama" class="block text-sm font-medium text-gray-700">Nama</label>
                            <input type="text" name="nama" id="nama" value="{{ $karyawan->nama }}"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        </div>

                        <div>
                            <label for="department" class="block text-sm font-medium text-gray-700">NRP</label>
                            <input type="text" name="department" id="department"
                                value="{{ Auth::guard('karyawan')->user()->nrp }}"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                readonly>
                        </div>
                        <div>
                            <label for="nama" class="block text-sm font-medium text-gray-700">Nomor Telepon</label>
                            <input type="number" name="telp" id="telp" value="{{ $karyawan->telp }}"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        </div>
                        <div>
                            <label for="department" class="block text-sm font-medium text-gray-700">Department</label>
                            <input type="text" name="department" id="department"
                                value="{{ Auth::guard('karyawan')->user()->department }}"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                readonly>
                        </div>
                        <div>
                            <label for="jabatan" class="block text-sm font-medium text-gray-700">Jabatan</label>
                            <input type="text" name="jabatan" id="jabatan"
                                value="{{ Auth::guard('karyawan')->user()->jabatan }}"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                readonly>
                        </div>
                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                            <input type="password" name="password" id="password"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        </div>
                        <div class="flex justify-end">
                            <button type="button" onclick="confirmUpdate()"
                                class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition duration-300">Update
                                Profile</button>
                        </div>
                    </div>
                </div>
        </form>
    </div>

{{-- ================= UPDATE : MODAL PREVIEW FOTO BARU ================= --}}
<div id="modalPreviewFoto" class="hidden fixed inset-0 bg-black bg-opacity-70 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg overflow-hidden relative max-w-md w-full">
        <span class="absolute top-2 right-3 cursor-pointer text-xl font-bold" onclick="closePreviewModal()">&times;</span>
        <img id="modalFoto" class="w-full h-auto object-contain" alt="Foto Preview Baru">
    </div>
</div>
{{-- ================================================================ --}}

{{-- ================= MODAL FULL SIZE FOTO SAAT INI ================= --}}
<div id="modalCurrentFoto" class="hidden fixed inset-0 bg-black bg-opacity-70 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg overflow-hidden relative max-w-md w-full">
        <span class="absolute top-2 right-3 cursor-pointer text-xl font-bold" onclick="closeCurrentFotoModal()">&times;</span>
        <img id="modalCurrentFotoImg" class="w-full h-auto object-contain" alt="Foto Profile Saat Ini">
    </div>
</div>
{{-- ================================================================ --}}

    {{-- Notif / AUDIO MASTER --}}
    <audio id='notifikasi_in'>
        <source src="{{ asset('assets/sound/in.wav') }}" type="audio/wav">
    </audio>
    <audio id="notifikasi_out">
        <source src="{{ asset('assets/sound/out.wav') }}" type="audio/wav">
    </audio>
    <audio id="radius_sound">
        <source src="{{ asset('assets/sound/errorRadius.mp3') }}" type="audio/mpeg">
    </audio>

    {{-- ================= UPDATE : SCRIPT CROPPER + COMPRESS + PREVIEW ================= --}}
<script src="https://cdn.jsdelivr.net/npm/cropperjs@1.5.13/dist/cropper.min.js"></script>
<link  href="https://cdn.jsdelivr.net/npm/cropperjs@1.5.13/dist/cropper.min.css" rel="stylesheet"/>
    <script>
    let cropper;
    const fotoInput = document.getElementById('fotoInput');
    const previewFoto = document.getElementById('previewFoto');
    const fotoCropped = document.getElementById('fotoCropped');
    const modalPreview = document.getElementById('modalPreviewFoto');
    const modalFoto = document.getElementById('modalFoto');
    const modalCurrentFoto = document.getElementById('modalCurrentFoto');
    const modalCurrentFotoImg = document.getElementById('modalCurrentFotoImg');

    fotoInput.addEventListener('change', function (e) {
        const file = e.target.files[0];
        if (!file) return;

        // Validasi tipe file (hanya image) - tetap ada untuk keamanan
        if (!file.type.startsWith('image/')) {
            Swal.fire('Error', 'File harus berupa gambar!', 'error');
            return;
        }

        // Hapus validasi ukuran file di sini (tidak ada batasan awal)

        const reader = new FileReader();
        reader.onload = function (event) {
            previewFoto.src = event.target.result;
            previewFoto.classList.remove('hidden');

            if (cropper) cropper.destroy();

            cropper = new Cropper(previewFoto, {
                aspectRatio: 1,
                viewMode: 1,
                dragMode: 'move',
                zoomable: true,
                scalable: true,
                cropBoxResizable: false
            });
        };
        reader.readAsDataURL(file);
    });

    // compress + crop sebelum submit
    function beforeSubmitProfile() {
        if (cropper) {
            const canvas = cropper.getCroppedCanvas({
                width: 400,
                height: 400,
                imageSmoothingQuality: 'high'
            });

            // compress jadi jpeg 0.7
            fotoCropped.value = canvas.toDataURL('image/jpeg', 0.7);
        }
    }

    // modal preview foto baru
    function openPreviewModal() {
        if (previewFoto.src) {
            modalFoto.src = previewFoto.src;
            modalPreview.classList.remove('hidden');
        } else {
            Swal.fire('Belum ada foto yang dipilih', '', 'info');
        }
    }

    function closePreviewModal() {
        modalPreview.classList.add('hidden');
    }

    // modal full size foto saat ini
    function openCurrentFotoModal() {
        const currentFotoSrc = document.getElementById('currentFoto').src;
        modalCurrentFotoImg.src = currentFotoSrc;
        modalCurrentFoto.classList.remove('hidden');
    }

    function closeCurrentFotoModal() {
        modalCurrentFoto.classList.add('hidden');
    }

    // update confirm sweetalert
    function confirmUpdate() {
        // Validasi: Pastikan ada foto yang dipilih atau cropped
        if (!fotoInput.files[0] && !fotoCropped.value) {
            Swal.fire('Error', 'Pilih foto terlebih dahulu!', 'error');
            return;
        }

        Swal.fire({
            title: 'Apakah data yang di edit sudah benar?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, simpan!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                beforeSubmitProfile(); // 🔥 PENTING UNTUK FOTO + COMPRESS
                document.getElementById('profileForm').submit();
            }
        })
    }
    </script>
    {{-- ================================================================ --}}

@endsection