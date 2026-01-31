<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    {{-- FavIcons --}}
    <link rel="icon" type="image/png" href={{ asset('assets/img/favicon.png') }} sizes="32x32">
    {{-- tailwindcss vite local --}}
    @vite('resources/css/app.css')
    <link href="./style.css" rel="stylesheet" />
    <!-- Font Google -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@200..700&display=swap" rel="stylesheet" />
    <!-- Tailwindcss CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://unpkg.com/@themesberg/flowbite@1.2.0/dist/flowbite.min.css" />
    {{-- leaflet map --}}
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title>Infinity</title>
    <link rel="manifest" href="/manifest.json">

    <meta name="theme-color" content="#0d6efd">

    <!-- iOS -->
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-title" content="A3 Project">
    <link rel="apple-touch-icon" href="/assets/img/logo-192.png">
    <style>
        /* Menambahkan gaya untuk latar belakang loader */
        .loader-bg {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0, 2, 3, 1.5);
            /* Latar belakang gelap dengan transparansi */
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 50;
            /* Pastikan loader berada di atas konten lainnya */
            opacity: 1;
            /* Awalnya terlihat */
            transition: opacity 0.5s ease, visibility 0.5s ease;
            /* Transisi untuk efek menghilang */
            visibility: visible;
            /* Awalnya terlihat */
        }

        .hidden-loader {
            opacity: 0;
            /* Saat disembunyikan, atur opacity menjadi 0 */
            visibility: hidden;
            /* Sembunyikan elemen */
            pointer-events: none;
            /* Nonaktifkan interaksi saat tersembunyi */
        }

        .zoom-in {
            transform: scale(1.2);
            /* Efek zoom in */
            transition: transform 0.5s ease;
            /* Transisi untuk efek zoom in */
        }

        .fade-out {
            transform: scale(0.8);
            /* Efek zoom out */
            opacity: 0;
            /* Mengurangi opacity */
            transition: transform 0.5s ease, opacity 0.5s ease;
            /* Transisi untuk efek zoom out */
        }
    </style>
</head>

<script>
    if ('serviceWorker' in navigator) {
    navigator.serviceWorker.register('/service-worker.js');
    }
</script>
<body class="font-Oswald">
    {{-- Loader --}}
    <div class="loader-bg" id="loader">
        <img src="{{ asset('assets/img/loader.gif') }}" alt="Loading..." class="w-64 h-64" id="loaderImage">
    </div>
    <div class="">
        <section class="bg-gray-50 min-h-screen flex items-center justify-center">
            <!-- login container -->
            <div class="bg-gray-100 flex rounded-2xl shadow-lg max-w-3xl p-5 items-center">
                <!-- form -->
                <div class="md:w-1/2 px-8 md:px-16">
                    <a href=""><img src="{{ asset('assets/img/logo/logo1.png') }}" alt="logo hrs 1"
                            class="size-50 animate-pulse hover:scale-110 duration-700" /></a>
                    <h2 class="font-bold text-2xl text-[#040404]">Login</h2>
                    <p class="text-xs mt-4 text-[#040404]">
                        PT.Hasnur Riung Sinergi
                    </p>
                    @php
                        $messagewarning = Session::get('warning');
                    @endphp
                    @if (Session::get('warning'))
                        <div class="bg-orange-100 border-l-4 border-orange-500 text-orange-700 p-2 mt-4 -mb-3"
                            role="alert">
                            {{-- <p class="font-bold">Be Warned</p>
                            <p>This is an error alert — check it out!</p> --}}
                            {{ $messagewarning }}
                        </div>
                    @endif
                    <form action="/proseslogin" method="POST" class="flex flex-col gap-4">
                        @csrf
                        <input class="p-2 mt-8 rounded-xl border" type="text" name="nrp" id="nrp"
                            placeholder="NRP" />
                        <div class="relative">
                            <input class="p-2 rounded-xl border w-full" type="password" id="password" name="password"
                                placeholder="Password" />
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="gray"
                                class="bi bi-eye absolute top-1/2 right-3 -translate-y-1/2" viewBox="0 0 16 16">
                                <path
                                    d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8zM1.173 8a13.133 13.133 0 0 1 1.66-2.043C4.12 4.668 5.88 3.5 8 3.5c2.12 0 3.879 1.168 5.168 2.457A13.133 13.133 0 0 1 14.828 8c-.058.087-.122.183-.195.288-.335.48-.83 1.12-1.465 1.755C11.879 11.332 10.119 12.5 8 12.5c-2.12 0-3.879-1.168-5.168-2.457A13.134 13.134 0 0 1 1.172 8z" />
                                <path
                                    d="M8 5.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5zM4.5 8a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0z" />
                            </svg>
                        </div>
                        <button
                            class="bg-[#000000] rounded-xl text-white py-2 hover:scale-105 duration-300 hover:bg-green-700 dark:hover:text-black">
                            Login
                        </button>
                    </form>
                    {{-- <div class="mt-3 text-xs flex justify-between items-center text-[#000000]">
                        <p>Butuh bantuan ?</p>
                        <a href="#">
                            <button
                                class="py-2 px-3 bg-white border rounded-xl hover:scale-110 duration-300 hover:bg-black dark:hover:text-white"
                                type="button">
                                Contact Support
                            </button>
                        </a>
                    </div> --}}
                </div>

                <!-- image -->
                <div class="md:block hidden w-1/2">
                    <img class="rounded-2xl" src="{{ asset('assets/img/logoA3.png') }}" />
                </div>
            </div>
        </section>
    </div>

    <script>
        // Simulasi waktu loading
        window.onload = function() {
            // Sembunyikan loader setelah 3 detik
            setTimeout(function() {
                const loader = document.getElementById('loader');
                const loaderImage = document.getElementById('loaderImage');

                // Tambahkan efek zoom in sebelum menyembunyikan loader
                loaderImage.classList.add('zoom-in');

                // Setelah efek zoom in selesai, tambahkan efek zoom out
                setTimeout(function() {
                    loaderImage.classList.add(
                        'fade-out'); // Tambahkan kelas fade-out untuk efek transisi
                }, 500); // Waktu yang sama dengan durasi efek zoom in

                // Setelah efek zoom out selesai, sembunyikan loader
                setTimeout(function() {
                    loader.classList.add('hidden-loader'); // Tambahkan kelas hidden untuk efek transisi
                }, 1000); // Waktu yang sama dengan durasi efek zoom out
            }, 1500);
        };
    </script>
    <!-- JS -->
    <script src="/script.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flowbite@2.5.2/dist/flowbite.min.js"></script>
    {{-- JS --}}
    <script src="{{ asset('app.js') }}"></script>
    <script src="https://unpkg.com/@themesberg/flowbite@1.2.0/dist/flowbite.bundle.js"></script>
    {{-- ionicons js --}}
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
    {{-- webcam min js cdn --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/webcamjs/1.0.26/webcam.min.js"></script>
    {{-- Jquery --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    {{-- leaflet JS CDN --}}
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    {{-- gsap cdn --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>

</body>

</html>
