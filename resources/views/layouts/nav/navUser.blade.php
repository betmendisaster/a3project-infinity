<!-- navbar -->

<nav class="bg-white border-gray-200 sticky top-0 z-50">
    <div class="max-w-screen-xl flex flex-wrap items-center justify-between mx-auto p-4">
        <a href="/" class="flex items-center space-x-3 rtl:space-x-reverse">
            <img src="{{ asset('assets/img/logo/logo.png') }}" class="h-8 animate-pulse" alt="PT HRS site AGM" />
            <span class="self-center text-2xl font-semibold whitespace-nowrap dark:text-white"></span>
        </a>
        <div class="flex items-center md:order-2 space-x-3 md:space-x-0 rtl:space-x-reverse">
            <button type="button"
                class="flex text-sm bg-gray-800 rounded-full md:me-0 focus:ring-4 focus:ring-gray-300 dark:focus:ring-gray-600"
                id="user-menu-button" aria-expanded="false" data-dropdown-toggle="user-dropdown"
                data-dropdown-placement="bottom">
                <span class="sr-only">Open user menu</span>
                @if (!empty(Auth::guard('karyawan')->user()->foto))
                    @php
                        $path = Storage::url('uploads/karyawan/fotoProfile/' . Auth::guard('karyawan')->user()->foto);
                    @endphp
                    <img class="object-cover w-8 h-8 rounded-full" src="{{ url($path) }}"
                        alt="Profile picture of Adhy Wira Pratama" />
                @else
                    <img class="w-8 h-8 rounded-full" src="{{ asset('assets/img/default-profile.jpg') }}"
                        alt="Profile picture of Adhy Wira Pratama" />
                @endif

            </button>
            <!-- Dropdown menu -->
            <div class="z-50 hidden my-4 text-base list-none bg-white divide-y divide-gray-100 rounded-lg shadow dark:bg-gray-700 dark:divide-gray-600"
                id="user-dropdown">
                <div class="px-4 py-3">
                    <span
                        class="block text-sm text-gray-900 dark:text-white">{{ Auth::guard('karyawan')->user()->nama }}</span>
                    <span
                        class="block text-sm text-gray-500 truncate dark:text-gray-400">{{ Auth::guard('karyawan')->user()->nrp }}</span>
                </div>
                <ul class="py-2" aria-labelledby="user-menu-button">
                    <li>
                        <a href="/"
                            class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:hover:bg-gray-600 dark:text-gray-200 dark:hover:text-white">Dashboard</a>
                    </li>
                    <li>
                        <a href="/editProfile"
                            class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:hover:bg-gray-600 dark:text-gray-200 dark:hover:text-white">Profile</a>
                    </li>
                    <li>
                        <form action="/proseslogout" method="POST" style="display: inline;">
                            @csrf
                            <button type="submit" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:hover:bg-gray-600 dark:text-gray-200 dark:hover:text-white w-full text-left">Log out</button>
                        </form> 
                    </li>
                </ul>
            </div>
        </div>
    </div>
</nav>
