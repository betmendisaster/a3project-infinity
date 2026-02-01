@extends('layouts.absensi')
@section('content')

    <div class="pb-20">
        <div
            class="relative flex flex-col items-center bg-white shadow-lg rounded-lg p-4 w-full max-w-xs md:max-w-md lg:max-w-lg mx-auto">
            <div
                class="bg-gradient-to-r from-teal-500 to-blue-500 w-full rounded-t-lg p-4 flex flex-col items-center relative">
                @if (!empty(Auth::guard('karyawan')->user()->foto))
                    @php
                        $path = Storage::url('uploads/karyawan/fotoProfile/' . Auth::guard('karyawan')->user()->foto);
                    @endphp
                    <img alt="Profile picture of Adhy Wira Pratama"
                        class="object-cover h-32 w-32 rounded-full mb-2 border-4 border-white shadow-md" height="100"
                        src="{{ url($path) }}" width="100" />
                @else
                    <img alt="Profile picture of {{ Auth::guard('karyawan')->user()->nama }}"
                        class="rounded-full mb-2 border-4 border-white shadow-md" height="100"
                        src="{{ asset('assets/img/default-profile.jpg') }}" width="100" />
                @endif
                <h2 class="text-white text-lg font-bold">{{ Auth::guard('karyawan')->user()->nama }}</h2>
                <h2 class="text-white text-lg font-bold">{{ Auth::guard('karyawan')->user()->nrp }}</h2>
                <h2 class="text-teal-200 font-bold">{{ Auth::guard('karyawan')->user()->kode_dept }}</h2>
                <p class="text-teal-200">{{ Auth::guard('karyawan')->user()->jabatan }}</p>
                {{-- <p class="text-teal-200 font-bold">Area Presensi : {{ Auth::guard('karyawan')->user()->kode_cabang }}</p> --}}
            </div>
            <div class="flex justify-around w-full bg-white shadow-md rounded-t-lg py-2">
                <a href="/editProfile">
                    <div class="flex flex-col items-center cursor-pointer hover:text-teal-500 transition duration-300">
                        <i class="fas fa-user text-teal-500 text-2xl"></i>
                        <p class="text-sm font-semibold">Profile</p>
                    </div>
                </a>
                <div class="relative">
                    <div class="flex flex-col items-center cursor-pointer hover:text-purple-500 transition duration-300"
                        onclick="toggleDropdown()">
                        <i class="fas fa-calendar-alt text-purple-500 text-2xl"></i>
                        <p class="text-sm font-semibold">CIS</p>
                    </div>
                    <div class="absolute hidden bg-white shadow-lg rounded-lg mt-2 w-40" id="dropdownMenu">
                        {{-- <a href="presensi/maintenance" class="block px-4 py-2 text-gray-800 hover:bg-gray-200">Ajukan
                            Cuti</a> --}}
                        <a href="presensi/cis/izin" class="block px-4 py-2 text-gray-800 hover:bg-gray-200 text-sm">Ajukan
                            Cuti/Izin/Sakit</a>
                        {{-- <a href="presensi/maintenance" class="block px-4 py-2 text-gray-800 hover:bg-gray-200">Ajukan Izin
                            Sakit</a> --}}
                    </div>
                </div>
                <div class="flex flex-col items-center cursor-pointer hover:text-yellow-500 transition duration-300"
                    id="openHasilAbsen">
                    <i class="fas fa-history text-yellow-500 text-2xl"></i>
                    <p class="text-sm font-semibold">Histori</p>
                </div>
                <div class="flex flex-col items-center cursor-pointer hover:text-pink-500 transition duration-300">
                    <i class="fas fa-map-marker-alt text-pink-500 text-2xl"></i>
                    <a href="/presensi/maintenance">
                        <p class="text-sm font-semibold">Lokasi</p>
                    </a>
                </div>
            </div>
            <!-- today -->
            <div class="bg-white text-gray-800 rounded-b-lg p-2 flex items-center justify-center w-full mx-1 shadow-md">
                <i class="fas fa-clock text-xl mr-2"></i>
                <div class="flex flex-col items-center mr-8">
                    <p class="text-md font-bold">Time</p>
                    <p class="text-xs" id="time">--:--</p>
                </div>
                <i class="fas fa-calendar-day text-xl ml-4 mr-2"></i>
                <div class="flex flex-col items-center">
                    <p class="text-md font-bold">Date</p>
                    <p class="text-xs" id="date">--/--/----</p>
                </div>
            </div>
            <!-- bugar -->
            <a href="/presensi/bugar-selamat"
                class="bg-gradient-to-r from-yellow-500 to-orange-500 text-white rounded-lg p-2 flex items-center justify-center w-full mx-1 cursor-pointer hover:from-yellow-600 hover:to-orange-600 transition duration-300 shadow-md mt-4">
                <i class="fas fa-heartbeat text-xl mr-2"></i>
                <div class="flex flex-col items-center">
                    <p class="text-md font-bold">Bugar</p>
                    <p class="text-xs">Selamat</p>
                </div>
            </a>
            <div class="flex justify-around w-full mt-4">
                <!-- MASUK -->
                <button
                    type="button"
                    id="openModalBtnIn"
                    class="bg-gradient-to-r from-green-700 to-lime-700 text-white rounded-lg p-3 flex items-center w-1/2 mx-1 hover:from-green-600 hover:to-lime-600 transition duration-300 shadow-md relative focus:outline-none">
                    <i class="fas fa-sign-in-alt text-xl"></i>
                    <div class="flex flex-col items-center ml-2">
                        <p class="text-md font-bold">Masuk</p>
                        <p class="text-xs">
                            {{ $presensiHariIni != null ? $presensiHariIni->jam_in : 'Belum Absen' }}
                        </p>
                    </div>
                    <i class="fas fa-camera text-xl absolute right-3 top-1/2 -translate-y-1/2"></i>
                </button>

                <!-- PULANG -->
                <button
                    type="button"
                    id="openModalBtnOut"
                    class="bg-gradient-to-r from-red-700 to-pink-700 text-white rounded-lg p-3 flex items-center w-1/2 mx-1 hover:from-red-600 hover:to-pink-600 transition duration-300 shadow-md relative focus:outline-none">
                    <i class="fas fa-sign-out-alt text-xl"></i>
                    <div class="flex flex-col items-center ml-2">
                        <p class="text-md font-bold">Pulang</p>
                        <p class="text-xs">
                            {{ $presensiHariIni != null && $presensiHariIni->jam_out != null ? $presensiHariIni->jam_out : 'Belum Absen' }}
                        </p>
                    </div>
                    <i class="fas fa-camera text-xl absolute right-3 top-1/2 -translate-y-1/2"></i>
                </button>
            </div>

            <div class="bg-white text-black w-full rounded-t-lg pt-4 mt-3 flex flex-col items-center relative shadow-md">
                <h2 class="text-center font-semibold mb-4 text-sm">
                    Gadget Presensi Bulan {{ $namaBulan[$bulanIni] }} Tahun {{ $tahunIni }}
                </h2>
                <div class="flex justify-around w-full mx-4 mb-3">
                    <div
                        class="relative bg-slate-200 text-black rounded-lg p-2 flex flex-col items-center w-1/5 mx-1 cursor-pointer hover:bg-slate-600 hover:text-white transition duration-300 shadow-md">
                        <ion-icon name="pie-chart-outline" class="text-2xl"></ion-icon>
                        <span
                            class="absolute top-0 right-0 inline-flex items-center justify-center px-1 py-0.5 text-xs font-bold leading-none text-red-100 bg-red-600 rounded-full">
                            {{ $rekapPresensi->totHadir ?? 0 }}
                        </span>
                        <p class="font-semibold text-xs mt-1">Hadir</p>
                    </div>
                    <div
                        class="relative bg-slate-200 text-black rounded-lg p-2 flex flex-col items-center w-1/5 mx-1 cursor-pointer hover:bg-slate-600 hover:text-white transition duration-300 shadow-md">
                        <ion-icon name="document-text-outline" class="text-2xl"></ion-icon>
                        <span
                            class="absolute top-0 right-0 inline-flex items-center justify-center px-1 py-0.5 text-xs font-bold leading-none text-red-100 bg-red-600 rounded-full">
                            {{ $rekapCis->jmlIzin ?? 0 }}
                        </span>
                        <p class="font-semibold text-xs mt-1">Izin</p>
                    </div>
                    <div
                        class="relative bg-slate-200 text-black rounded-lg p-2 flex flex-col items-center w-1/5 mx-1 cursor-pointer hover:bg-slate-600 hover:text-white transition duration-300 shadow-md">
                        <ion-icon name="medkit-outline" class="text-2xl"></ion-icon>
                        <span
                            class="absolute top-0 right-0 inline-flex items-center justify-center px-1 py-0.5 text-xs font-bold leading-none text-red-100 bg-red-600 rounded-full">
                            {{ $rekapCis->jmlSakit ?? 0 }}
                        </span>
                        <p class="font-semibold text-xs mt-1">Sakit</p>
                    </div>
                    <div
                        class="relative bg-gray-200 text-black rounded-lg p-2 flex flex-col items-center w-1/5 mx-1 cursor-pointer hover:bg-gray-800 hover:text-white transition duration-300 shadow-md">
                        <ion-icon name="timer-outline" class="text-2xl"></ion-icon>
                        <span
                            class="absolute top-0 right-0 inline-flex items-center justify-center px-1 py-0.5 text-xs font-bold leading-none text-red-100 bg-red-600 rounded-full">
                            {{ $rekapPresensi->totLate ?? 0 }}
                        </span>
                        <p class="font-semibold text-xs mt-1">Telat</p>
                    </div>
                </div>
            </div>

        </div>
        @include('dashboard.modalDashboard')


        @push('myscript')
            <script>
                function updateTimeAndDate() {
                    const now = new Date();
                    const time = now.toLocaleTimeString([], {
                        hour: "2-digit",
                        minute: "2-digit",
                        hour12: false // Menambahkan opsi ini untuk format 24 jam
                    });
                    const date = now.toLocaleDateString();
                    document.getElementById("time").textContent = time;
                    document.getElementById("date").textContent = date;
                }
                setInterval(updateTimeAndDate, 1000);
                updateTimeAndDate();

                // dropdownMenu dari Cuti
                function toggleDropdown() {
                    const dropdownMenu = document.getElementById('dropdownMenu');
                    dropdownMenu.classList.toggle('hidden');
                }
            </script>
        @endpush

    </div>
@endsection
