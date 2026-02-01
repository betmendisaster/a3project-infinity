@extends('layouts.admin.tabler')

@section('content')
<style>
#attendanceChart {
    border: 1px solid red;
    background-color: #f9f9f9;
    width: 100% !important;
    height: 300px !important;
    display: block;
}

.card-body {
    overflow: visible !important;
    min-height: 320px;
}
#attendancePieChart {
    max-width: 200px !important;
    margin: 0 auto;
    display: block;
}

</style>

<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <div class="page-pretitle">Overview</div>
                <h2 class="page-title">Dashboard Admin</h2>
            </div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">
        <div class="row row-cards">
            <!-- Total Karyawan -->
            <div class="col-sm-6 col-lg-3">
                <div class="card card-sm hover-scale">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-auto">
                                <span class="bg-primary text-white avatar" data-bs-toggle="tooltip" title="Total seluruh karyawan">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                        viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                        class="icon icon-tabler icon-tabler-users">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                        <path d="M9 7m-4 0a4 4 0 1 0 8 0a4 4 0 1 0 -8 0" />
                                        <path d="M3 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2" />
                                        <path d="M16 3.13a4 4 0 0 1 0 7.75" />
                                        <path d="M21 21v-2a4 4 0 0 0 -3 -3.85" />
                                    </svg>
                                </span>
                            </div>
                            <div class="col">
                                <div class="font-weight-medium">{{ $totalUsers }}</div>
                                <div class="text-secondary">Total Karyawan</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Ringkasan Kehadiran -->
            <div class="col-sm-6 col-lg-3">
                <div class="card card-sm hover-scale">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-auto">
                                <span class="bg-success text-white avatar" data-bs-toggle="tooltip" title="Total kehadiran hari ini">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                        viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                        class="icon icon-tabler icon-tabler-fingerprint">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                        <path d="M18.9 7a8 8 0 0 1 1.1 5v1a6 6 0 0 0 .8 3" />
                                        <path d="M8 11a4 4 0 0 1 8 0v1a10 10 0 0 0 2 6" />
                                        <path d="M12 11v2a14 14 0 0 0 2.5 8" />
                                        <path d="M8 15a18 18 0 0 0 1.8 6" />
                                        <path d="M4.9 19a22 22 0 0 1 -.9 -7v-1a8 8 0 0 1 12 -6.95" />
                                    </svg>
                                </span>
                            </div>
                            <div class="col">
                                <div class="font-weight-medium">{{ $rekapPresensi->totHadir }}</div>
                                <div class="text-secondary">Hadir Hari Ini</div>
                            </div>
                        </div>
                        <small class="text-muted mt-1 d-block">Telat: {{ $rekapPresensi->totLate }}</small>

                        <!-- Pie Chart Hadir vs Tidak Hadir -->
                        <div class="mt-2">
                            <canvas id="attendancePieChart" height="120"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Karyawan Telat -->
            <div class="col-sm-6 col-lg-3">
                <div class="card card-sm hover-scale">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-auto">
                                <span class="bg-danger text-white avatar" data-bs-toggle="tooltip" title="Jumlah karyawan yang datang telat">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                        viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                        class="icon icon-tabler icon-tabler-alarm">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                        <path d="M12 13m-7 0a7 7 0 1 0 14 0a7 7 0 1 0 -14 0" />
                                        <path d="M12 10l0 3l2 0" />
                                        <path d="M7 4l-2.75 2" />
                                        <path d="M17 4l2.75 2" />
                                    </svg>
                                </span>
                            </div>
                            <div class="col">
                                <div class="font-weight-medium">{{ $rekapPresensi->totLate }}</div>
                                <div class="text-secondary">Telat Hari Ini</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Persentase Kehadiran -->
            <div class="col-sm-6 col-lg-3">
                <div class="card card-sm hover-scale">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-auto">
                                <span class="bg-warning text-white avatar" data-bs-toggle="tooltip" title="Persentase karyawan hadir hari ini">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                        viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                        class="icon icon-tabler icon-tabler-chart-pie">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                        <path d="M12 12l8 4v4h-16v-4z"/>
                                    </svg>
                                </span>
                            </div>
                            <div class="col">
                                @php
                                    $percent = $totalUsers > 0 ? round(($rekapPresensi->totHadir / $totalUsers) * 100) : 0;
                                @endphp
                                <div class="font-weight-medium">{{ $percent }}%</div>
                                <div class="text-secondary">Kehadiran</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Grafik Tren Bulanan -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Tren Kehadiran Bulan Ini</h3>
                    </div>
                    <div class="card-body">
                        <canvas id="attendanceChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
{{-- <pre>
Labels: {{ json_encode($labels) }}
Values: {{ json_encode($values) }}
</pre> --}}
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {

        // Tooltip bootstrap
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        });
        // Pie Chart Hadir vs Tidak Hadir
        const hadir = {{ $rekapPresensi->totHadir }};
        const tidakHadir = {{ $totalUsers - $rekapPresensi->totHadir }};

        const ctxPie = document.getElementById('attendancePieChart').getContext('2d');

        // Gradient warna
        const gradientHadir = ctxPie.createLinearGradient(0, 0, 0, 150);
        gradientHadir.addColorStop(0, 'rgba(54, 162, 235, 0.8)');
        gradientHadir.addColorStop(1, 'rgba(54, 162, 235, 0.4)');

        const gradientTidakHadir = ctxPie.createLinearGradient(0, 0, 0, 150);
        gradientTidakHadir.addColorStop(0, 'rgba(108, 117, 125, 0.8)');
        gradientTidakHadir.addColorStop(1, 'rgba(108, 117, 125, 0.4)');

        new Chart(ctxPie, {
            type: 'doughnut',
            data: {
                labels: ['Hadir', 'Tidak Hadir'],
                datasets: [{
                    data: [hadir, tidakHadir],
                    backgroundColor: [gradientHadir, gradientTidakHadir],
                    borderColor: ['rgba(54, 162, 235, 1)', 'rgba(108, 117, 125, 1)'],
                    borderWidth: 2,
                    hoverOffset: 6
                }]
            },
            options: {
                cutout: '70%', // donut lebih ramping → chart terlihat lebih kecil
                responsive: true,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: 'rgba(0,0,0,0.85)',
                        titleColor: '#fff',
                        bodyColor: '#fff',
                        padding: 8,
                        cornerRadius: 6,
                        callbacks: {
                            label: function(context) {
                                const value = context.raw;
                                const total = context.dataset.data.reduce((a,b)=>a+b,0);
                                const percent = ((value/total)*100).toFixed(1);
                                return `${context.label}: ${value} (${percent}%)`;
                            }
                        }
                    }
                },
                animation: {
                    animateRotate: true,
                    animateScale: true,
                    duration: 1000,
                    easing: 'easeOutQuart'
                }
            }
        });
        const labels = {!! json_encode($labels) !!};
        const data = {!! json_encode($values) !!};

        const ctx = document.getElementById('attendanceChart').getContext('2d');

        // Gradient fill
        const gradient = ctx.createLinearGradient(0, 0, 0, 400);
        gradient.addColorStop(0, 'rgba(54, 162, 235, 0.4)');
        gradient.addColorStop(1, 'rgba(54, 162, 235, 0.05)');

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Kehadiran',
                    data: data,
                    fill: true,
                    backgroundColor: gradient,
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 3,
                    tension: 0.5, // Garis sangat smooth
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    pointBackgroundColor: 'rgba(54, 162, 235, 1)',
                    pointBorderColor: 'white',
                    pointHoverBackgroundColor: '#fff',
                    pointHoverBorderColor: 'rgba(54, 162, 235, 1)',
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: {
                    duration: 1000, // Animasi masuk lembut
                    easing: 'easeOutQuart'
                },
                plugins: {
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                        backgroundColor: 'rgba(0,0,0,0.85)',
                        titleColor: '#fff',
                        bodyColor: '#fff',
                        padding: 12,
                        cornerRadius: 6,
                        titleFont: { size: 14, weight: '600' },
                        bodyFont: { size: 13 }
                    },
                    legend: {
                        display: false // Minimalis ala Tabler
                    }
                },
                interaction: {
                    mode: 'nearest',
                    axis: 'x',
                    intersect: false
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0,
                            color: '#6c757d',
                            font: { size: 12 }
                        },
                        grid: {
                            color: 'rgba(0,0,0,0.05)',
                            borderDash: [4, 4]
                        }
                    },
                    x: {
                        ticks: {
                            color: '#6c757d',
                            font: { size: 12 }
                        },
                        grid: {
                            color: 'rgba(0,0,0,0.05)',
                            borderDash: [4, 4]
                        }
                    }
                }
            }
        });
    });
</script>
@endsection
