<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    {{-- FavIcons --}}
    <link rel="icon" type="image/png" href="{{ asset('assets/img/favicon.png') }}" sizes="32x32">
    {{-- Tailwindcss Vite local --}}
    @vite('resources/css/app.css')
    <!-- Font Google (Montserrat untuk kesan lebih menarik) -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
    <!-- Tailwindcss CDN (untuk fallback jika Vite belum load) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://unpkg.com/@themesberg/flowbite@1.2.0/dist/flowbite.min.css" />
    {{-- Leaflet map (jika diperlukan di halaman lain) --}}
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title>Infinity - Login</title>
    <link rel="manifest" href="/manifest.json">

    <meta name="theme-color" content="#0d6efd">

    <!-- iOS PWA -->
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-title" content="A3 Project">
    <link rel="apple-touch-icon" href="/assets/img/logo-192.png">

    <style>
        /* Background body dengan kesan dunia pertambangan (gradient dusty) */
        .body-bg {
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 25%, #2c3e50 50%, #1a252f 75%, #0f1419 100%);
            background-attachment: fixed; /* Efek parallax ringan untuk kesan depth */
            background-size: cover;
        }

        /* Loader tetap hitam, transparansi berangsur-angsur saat fade out */
        .loader-bg {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0, 0, 0, 1); /* Hitam solid tanpa transparan awal */
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 50;
            opacity: 1;
            visibility: visible;
            transition: opacity 0.5s ease, visibility 0.5s ease; /* Transparansi berangsur-angsur */
        }

        .hidden-loader {
            opacity: 0; /* Transparansi berubah ke 0 secara smooth */
            visibility: hidden;
            pointer-events: none;
        }

        .zoom-in {
            transform: scale(1.1);
            transition: transform 0.5s ease;
        }

        .fade-out {
            transform: scale(0.9);
            opacity: 0;
            transition: transform 0.5s ease, opacity 0.5s ease;
        }

        /* Custom styles untuk input dan tombol (mirip Tabler) */
        .form-input {
            border: 1px solid #d1d5db;
            border-radius: 0.5rem;
            padding: 0.75rem;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }

        .form-input:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
            outline: none;
        }

        .btn-primary {
            background: linear-gradient(135deg, #1f2937, #374151);
            color: white;
            border-radius: 0.5rem;
            padding: 0.75rem 1.5rem;
            font-weight: 600;
            transition: transform 0.2s ease, background 0.2s ease;
        }

        .btn-primary:hover {
            transform: scale(1.05);
            background: linear-gradient(135deg, #059669, #10b981);
        }
    </style>
</head>

<script>
    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.register('/service-worker.js');
    }
</script>

<body class="font-Montserrat body-bg min-h-screen flex items-center justify-center p-4">
    {{-- Loader --}}
    <div class="loader-bg" id="loader">
        <img src="{{ asset('assets/img/loader.gif') }}" alt="Loading..." class="w-32 h-32 md:w-48 md:h-48" id="loaderImage">
    </div>

    <!-- Login Container (Card Style seperti Tabler) -->
    <div class="bg-white/90 backdrop-blur-sm rounded-lg shadow-lg max-w-md md:max-w-4xl w-full p-6 md:p-8 flex flex-col md:flex-row items-center">
        <!-- Form Section -->
        <div class="w-full md:w-1/2 md:pr-8">
            <!-- Logo dan Header -->
            <div class="text-center mb-6">
                <a href="#" class="inline-block">
                    <img src="{{ asset('assets/img/logo/logo1.png') }}" alt="Logo HRS" class="w-32 h-auto md:w-40 object-contain mx-auto animate-pulse hover:scale-110 transition-transform duration-300" />
                </a>
                <h1 class="text-2xl md:text-3xl font-bold text-gray-800 mt-4">Login</h1>
                <p class="text-sm text-gray-600 mt-2">PT. Hasnur Riung Sinergi site Agm</p>
            </div>

            <!-- Warning Message (Alert Style) -->
            @php
                $messagewarning = Session::get('warning');
            @endphp
            @if (Session::get('warning'))
                <div class="bg-yellow-50 border-l-4 border-yellow-400 text-yellow-800 p-4 mb-4 rounded-md">
                    <p class="text-sm">{{ $messagewarning }}</p>
                </div>
            @endif

            <!-- Form -->
            <form action="/proseslogin" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label for="nrp" class="block text-sm font-medium text-gray-700 mb-1">NRP</label>
                    <input class="form-input w-full" type="text" name="nrp" id="nrp" placeholder="Masukkan NRP" required />
                </div>
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                    <div class="relative">
                        <input class="form-input w-full pr-10" type="password" id="password" name="password" placeholder="Masukkan Password" required />
                        <button type="button" id="togglePassword" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-eye" viewBox="0 0 16 16">
                                <path d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8zM1.173 8a13.133 13.133 0 0 1 1.66-2.043C4.12 4.668 5.88 3.5 8 3.5c2.12 0 3.879 1.168 5.168 2.457A13.133 13.133 0 0 1 14.828 8c-.058.087-.122.183-.195.288-.335.48-.83 1.12-1.465 1.755C11.879 11.332 10.119 12.5 8 12.5c-2.12 0-3.879-1.168-5.168-2.457A13.134 13.134 0 0 1 1.172 8z" />
                                <path d="M8 5.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5zM4.5 8a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0z" />
                            </svg>
                        </button>
                    </div>
                </div>
                <button type="submit" class="btn-primary w-full">
                    Login
                </button>
            </form>

            <!-- Optional: Support Link -->
            <div class="mt-6 text-center">
                <p class="text-xs text-gray-500">Butuh bantuan?</p>
                <a href="#" class="text-sm text-blue-600 hover:text-blue-800 transition-colors">Hubungi Support</a>
            </div>
        </div>

        <!-- Image Section (Hanya di Desktop) -->
        <div class="hidden md:block w-1/2 pl-8">
            <img class="rounded-lg shadow-md w-full h-auto" src="{{ asset('assets/img/logoA3.png') }}" alt="Ilustrasi Login" />
        </div>
    </div>

    <script>
        // Loader Script
        window.onload = function() {
            setTimeout(function() {
                const loader = document.getElementById('loader');
                const loaderImage = document.getElementById('loaderImage');

                loaderImage.classList.add('zoom-in');

                setTimeout(function() {
                    loaderImage.classList.add('fade-out');
                }, 500);

                setTimeout(function() {
                    loader.classList.add('hidden-loader');
                }, 1000);
            }, 1500); // Durasi loader
        };

        // Toggle Password Visibility
        document.getElementById('togglePassword').addEventListener('click', function() {
            const passwordInput = document.getElementById('password');
            const icon = this.querySelector('svg');
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.innerHTML = '<path d="M13.359 11.238C15.06 9.72 16 8 16 8s-3-5.5-8-5.5a7.028 7.028 0 0 0-2.79.588l.77.771A5.944 5.944 0 0 1 8 3.5c2.12 0 3.879 1.168 5.168 2.457A13.134 13.134 0 0 1 14.828 8c-.058.087-.122.183-.195.288-.335.48-.83 1.12-1.465 1.755-.165.165-.337.328-.517.486l.708.709z"/><path d="M11.297 9.176a3.5 3.5 0 0 0-4.474-4.474l.823.823a2.5 2.5 0 0 1 2.829 2.829l.822.822zm-2.943 1.299.822.822a3.5 3.5 0 0 1-4.474-4.474l.823.823a2.5 2.5 0 0 0 2.829 2.829z"/><path d="M3.35 5.47c-.18.16-.353.322-.518.487A13.134 13.134 0 0 1 1.172 8l.195.288c.335.48.83 1.12 1.465 1.755C4.121 11.332 5.881 12.5 8 12.5c.716 0 1.39-.133 2.02-.36l.77.772A7.029 7.029 0 0 0 8 13.5C3 13.5 0 8 0 8s.939-1.721 2.641-3.238l.708.708zm10.296 8.884-12-12 .708-.708 12 12-.708.708z"/>';
            } else {
                passwordInput.type = 'password';
                icon.innerHTML = '<path d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8zM1.173 8a13.133 13.133 0 0 1 1.66-2.043C4.12 4.668 5.88 3.5 8 3.5c2.12 0 3.879 1.168 5.168 2.457A13.133 13.133 0 0 1 14.828 8c-.058.087-.122.183-.195.288-.335.48-.83 1.12-1.465 1.755C11.879 11.332 10.119 12.5 8 12.5c-2.12 0-3.879-1.168-5.168-2.457A13.134 13.134 0 0 1 1.172 8z" /><path d="M8 5.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5zM4.5 8a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0z" />';
            }
        });
    </script>

    <!-- JS Libraries -->
    <script src="/script.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flowbite@2.5.2/dist/flowbite.min.js"></script>
    <script src="{{ asset('app.js') }}"></script>
    <script src="https://unpkg.com/@themesberg/flowbite@1.2.0/dist/flowbite.bundle.js"></script>
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/webcamjs/1.0.26/webcam.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>
</body>

</html>