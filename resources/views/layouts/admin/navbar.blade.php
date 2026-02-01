<header class="navbar navbar-expand-md d-print-none">
  <div class="container-xl">
    <!-- Brand -->
    <h1 class="navbar-brand navbar-brand-autodark d-none-navbar-horizontal pe-0 pe-md-3">
      <a href="/panel/dashboardadmin">
        <img src="{{ asset('tabler/static/site.agm.svg') }}" width="110" height="32" alt="Tabler">
      </a>
    </h1>

    <!-- Toggler -->
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbar-menu"
      aria-controls="navbar-menu" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <!-- Navbar Right (User / Theme) -->
    <div class="navbar-nav flex-row order-md-last">
      <!-- Dark/Light Mode -->
      <div class="d-none d-md-flex">
        <a href="?theme=dark" class="nav-link px-0 hide-theme-dark" data-bs-toggle="tooltip" title="Enable dark mode">
          <!-- Moon icon SVG -->
        </a>
        <a href="?theme=light" class="nav-link px-0 hide-theme-light" data-bs-toggle="tooltip" title="Enable light mode">
          <!-- Sun icon SVG -->
        </a>
      </div>

      <!-- User dropdown -->
      <div class="nav-item dropdown">
        <a href="#" class="nav-link d-flex lh-1 text-reset p-0" data-bs-toggle="dropdown">
          <span class="avatar avatar-sm" style="background-image: url(./static/avatars/000m.jpg)"></span>
          <div class="d-none d-xl-block ps-2">
            <div>{{ Auth::guard('user')->user()->name }}</div>
            <div class="mt-1 small text-secondary">Administrator</div>
          </div>
        </a>
        <div class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
          <form action="/proseslogoutadmin" method="POST">
            @csrf
            <button type="submit" class="dropdown-item">Logout</button>
          </form>
        </div>
      </div>
    </div>

    <!-- Collapse Menu -->
    <div class="collapse navbar-collapse" id="navbar-menu">
      <ul class="navbar-nav">
        <li class="nav-item"><a class="nav-link" href="/panel/dashboardadmin">Home</a></li>
        <li class="nav-item"><a class="nav-link" href="/panel/presensi/monitoring">Monitoring Presensi</a></li>
        <li class="nav-item"><a class="nav-link" href="/panel/presensi/cis">Data CIS</a></li>

        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">Data Master</a>
          <div class="dropdown-menu">
            <a class="dropdown-item" href="/panel/karyawan">Karyawan</a>
            <a class="dropdown-item" href="/panel/department">Department</a>
            <a class="dropdown-item" href="/panel/cuti">Master Cuti</a>
            <a class="dropdown-item" href="/settings/cabang">Area Presensi</a>
            <a class="dropdown-item" href="/settings/jamKerja">Jam Kerja</a>
            <a class="dropdown-item" href="/settings/jamKerjaDept">Jam Kerja Department</a>
          </div>
        </li>

        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">Report</a>
          <div class="dropdown-menu">
            <a class="dropdown-item" href="/panel/report">Rekap Presensi Per User</a>
            <a class="dropdown-item" href="/panel/dailyReport">Rekap Presensi Harian</a>
            <a class="dropdown-item" href="/panel/rekapReport">Rekap Presensi Bulanan</a>
          </div>
        </li>

        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">Settings</a>
          <div class="dropdown-menu">
            <a class="dropdown-item" href="/settings/cabang">Area Presensi</a>
            <a class="dropdown-item" href="/settings/jamKerja">Jam Kerja</a>
            <a class="dropdown-item" href="/settings/jamKerjaDept">Jam Kerja Department</a>
          </div>
        </li>
      </ul>
    </div>
  </div>
</header>

<script>
document.addEventListener("DOMContentLoaded", function() {
    // Tooltip bootstrap
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (el) { return new bootstrap.Tooltip(el); });
});
</script>
