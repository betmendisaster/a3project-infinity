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
    {{-- fontawesome --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.1/css/all.min.css" />
    <!-- Font Google -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@200..700&display=swap" rel="stylesheet" />
    <!-- Tailwindcss CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://unpkg.com/@themesberg/flowbite@1.2.0/dist/flowbite.min.css" />
    <title>A3-app</title>
        <!-- Tailwind CSS CDN (jika belum ada) -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- jQuery CDN (jika belum ada, tambahkan di atas Swal) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- SweetAlert CDN -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    {{-- Loader --}}
    <style>
        .loader {
            border: 4px solid #f3f3f3;
            border-radius: 50%;
            border-top: 4px solid #000;
            width: 40px;
            height: 40px;
            -webkit-animation: spin 2s linear infinite;
            animation: spin 2s linear infinite;
        }

        @-webkit-keyframes spin {
            0% {
                -webkit-transform: rotate(0deg);
            }

            100% {
                -webkit-transform: rotate(360deg);
            }
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        .loader-text {
            margin-top: 10px;
            font-size: 1.2rem;
            color: #000;
        }
    </style>
</head>

<body>
    <div id="loader" class="fixed inset-0 flex flex-col items-center justify-center bg-white z-50 pb-20">
        <div class="loader"></div>
        <div class="loader-text">Loading...</div>
    </div>


    @include('layouts.nav.navUser')

    @yield('header')
    {{-- Main Hero --}}
    <div class="appCapsule">
        @yield('content')
    </div>
    {{-- @include('layouts.nav.bottomNavUser') --}}
    @include('layouts.scripts.script')

    <script>
        window.addEventListener('load', function() {
            document.getElementById('loader').style.display = 'none';
            document.getElementById('content').style.display = 'block';
        });
    </script>
</body>

</html>
