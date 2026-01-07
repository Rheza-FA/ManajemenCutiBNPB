@extends('layouts.app')

@section('title', 'Cek Sisa Cuti')

@section('content')

<style>
    /* --- ANIMATION & TRANSITIONS --- */
    .fade-in-up {
        animation: fadeInUp 0.6s ease-out forwards;
        opacity: 0;
        transform: translateY(20px);
    }
    
    @keyframes fadeInUp {
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .delay-100 { animation-delay: 0.1s; }
    .delay-200 { animation-delay: 0.2s; }
    .delay-300 { animation-delay: 0.3s; }

    /* --- CUSTOM CARD STYLE (Box Putih) --- */
    .card-custom {
        background: #ffffff;
        border: none;
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.05);
        transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
        overflow: hidden;
    }
    .card-custom:hover {
        transform: translateY(-5px);
        box-shadow: 0 12px 30px rgba(0,0,0,0.08);
    }
    
    .card-header-custom {
        background: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
        padding: 18px 24px;
        font-weight: 700;
        color: #0f3878;
        font-size: 1rem;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    /* --- FORM ELEMENTS --- */
    .form-control-custom {
        border: 1px solid #cbd5e1;
        padding: 12px 16px;
        border-radius: 10px;
        font-size: 0.95rem;
        transition: border-color 0.2s, box-shadow 0.2s;
        
        /* [PERBAIKAN MOBILE] Background putih & text gelap agar input date terlihat */
        background-color: #ffffff;
        color: #334155;
        min-height: 48px; /* Tinggi yang nyaman untuk sentuhan jari */
    }
    .form-control-custom:focus {
        border-color: #ff6b00;
        box-shadow: 0 0 0 4px rgba(255, 107, 0, 0.1);
        outline: none;
    }
    
    /* [PERBAIKAN MOBILE] CSS agar icon kalender & teks tanggal muncul benar di HP */
    input[type="date"] {
        -webkit-appearance: none;
        appearance: none;
        position: relative;
    }
    
    label.form-label {
        font-weight: 600;
        color: #64748b;
        font-size: 0.85rem;
        margin-bottom: 6px;
    }

    /* --- BUTTONS --- */
    .btn-bnpb-primary {
        background: linear-gradient(135deg, #ff6b00 0%, #ea580c 100%);
        border: none;
        color: white;
        font-weight: 600;
        padding: 10px 28px;
        border-radius: 10px;
        transition: all 0.3s ease;
        box-shadow: 0 4px 6px rgba(255, 107, 0, 0.2);
    }
    .btn-bnpb-primary:hover {
        background: linear-gradient(135deg, #ea580c 0%, #c2410c 100%);
        transform: translateY(-1px);
        box-shadow: 0 6px 12px rgba(255, 107, 0, 0.3);
    }
    
    .btn-bnpb-secondary {
        background: #e0f2fe;
        color: #0ea5e9;
        border: none;
        font-weight: 600;
        padding: 10px 24px;
        border-radius: 10px;
        transition: all 0.3s ease;
    }
    .btn-bnpb-secondary:hover {
        background: #bae6fd;
        color: #0284c7;
    }

    /* --- STATISTIC TEXT --- */
    .stat-label { font-size: 0.85rem; color: #64748b; font-weight: 500; }
    .stat-value { font-weight: 700; color: #334155; }
    .stat-highlight { color: #ff6b00; }
    
    /* --- TABLE --- */
    .table-hover tbody tr:hover {
        background-color: #f8fafc;
    }
    .badge-status {
        font-size: 0.75rem;
        padding: 6px 12px;
        font-weight: 600;
        border-radius: 30px;
    }

    /* --- NEW STYLES: HERO CARD (Box Biru) --- */
    .card-hero {
        background: linear-gradient(135deg, #0f3878 0%, #1e4d9c 100%);
        color: white;
        border: none;
        border-radius: 16px;
        overflow: hidden;
        /* Tambahkan transisi agar animasi halus */
        transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
    }
    
    /* Efek Hover untuk Box Biru */
    .card-hero:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 35px rgba(15, 56, 120, 0.4) !important; 
    }

    .stat-box-light {
        background: rgba(255, 255, 255, 0.1);
        border-radius: 8px;
        padding: 10px;
        backdrop-filter: blur(5px);
        height: 100%;
    }
    
    /* Custom Tabs */
    .nav-pills-custom .nav-link {
        color: #64748b;
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        font-size: 0.9rem;
        font-weight: 600;
        padding: 10px 20px;
        margin-right: 10px;
        transition: all 0.2s;
    }
    .nav-pills-custom .nav-link:hover {
        background-color: #f1f5f9;
    }
    .nav-pills-custom .nav-link.active {
        background-color: var(--bnpb-blue);
        color: #fff;
        border-color: var(--bnpb-blue);
        box-shadow: 0 4px 6px rgba(15, 56, 120, 0.2);
    }
    .nav-pills-custom .nav-link i {
        margin-right: 6px;
    }

    /* --- FIX TOMBOL & LAYOUT MOBILE ONLY --- */
    @media (max-width: 767.98px) {
        .btn-mobile-fix {
            /* Rampingkan tombol & Text 1 baris */
            padding: 8px 10px !important; 
            font-size: 0.85rem !important; 
            white-space: nowrap; 
            
            /* Flexbox untuk sentralisasi isi tombol */
            display: flex !important;
            align-items: center;
            justify-content: center;
            
            /* Paksa lebar penuh */
            width: 100%; 
        }

        /* Sesuaikan ukuran Icon */
        .btn-mobile-fix i {
            font-size: 1.1rem !important;
        }
        
        /* Override margin 'me-2' bawaan Bootstrap KHUSUS di mobile */
        .btn-mobile-fix .me-2 {
            margin-right: 6px !important; 
        }

        /* [PERBAIKAN MOBILE] Alignment Header agar sejajar dengan teks Navbar */
        .mobile-header-align {
            padding-left: 6px; /* Geser sedikit ke kanan */
        }
    }
</style>

<div class="d-flex justify-content-between align-items-center mb-3 mb-md-5 fade-in-up mobile-header-align">
    <div>
        <h3 class="fw-bold text-bnpb-blue mb-1">
            <i class="bi bi-speedometer2 me-2"></i>Dashboard Cuti
        </h3>
        <p class="text-muted mb-0 small">Pantau status cuti dan riwayat pengajuan pegawai.</p>
    </div>
    <div class="d-none d-md-flex align-items-center bg-white px-4 py-2 rounded-pill shadow-sm border">
        <div class="me-3 text-end lh-1">
            <span class="d-block fw-bold text-bnpb-blue small">Admin / Petugas</span>
            <span class="d-block text-muted" style="font-size: 10px; letter-spacing: 0.5px;">PEGAWAI ASN</span>
        </div>
        <div class="rounded-circle bg-light d-flex align-items-center justify-content-center text-bnpb-orange fw-bold shadow-sm" style="width: 40px; height: 40px; font-size: 1.1rem;">
            A
        </div>
    </div>
</div>

<div class="row g-4">
    
    <div class="col-lg-8 fade-in-up delay-100">
        <div class="card card-custom h-100">
            <div class="card-header-custom">
                <i class="bi bi-search text-bnpb-orange"></i> 
                <span>Pencarian Data Pegawai</span>
            </div>
            <div class="card-body p-4">
                
                @if(session('error'))
                <div class="alert alert-danger border-0 d-flex align-items-center mb-4 shadow-sm" role="alert" style="background-color: #fee2e2; color: #b91c1c; border-radius: 10px;">
                    <i class="bi bi-exclamation-circle-fill me-3 fs-5"></i>
                    <div>{{ session('error') }}</div>
                </div>
                @endif

                <form id="searchForm" action="{{ route('cek-cuti.check') }}" method="POST">
                    @csrf
                    <div class="mb-4">
                        <label for="keyword" class="form-label">NIP atau Nama Pegawai</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0 rounded-start-3" style="border-color: #cbd5e1;">
                                <i class="bi bi-person text-muted"></i>
                            </span>
                            <input type="text" class="form-control form-control-custom border-start-0 rounded-end-3" id="keyword" name="keyword" 
                                   placeholder="Masukkan NIP atau Nama Lengkap..." value="{{ old('keyword', $data['pegawai']->nip ?? '') }}" required>
                        </div>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label for="tgl_dari" class="form-label">Periode Awal</label>
                            <input type="date" class="form-control form-control-custom" id="tgl_dari" name="tgl_dari" 
                                   value="{{ old('tgl_dari', isset($data['tahun']) ? $data['tahun'].'-01-01' : date('Y').'-01-01') }}">
                        </div>
                        <div class="col-md-6">
                            <label for="tgl_sampai" class="form-label">Periode Akhir</label>
                            <input type="date" class="form-control form-control-custom" id="tgl_sampai" name="tgl_sampai"
                                   value="{{ old('tgl_sampai', isset($data['tahun']) ? $data['tahun'].'-12-31' : date('Y').'-12-31') }}">
                        </div>
                    </div>

                    <div class="d-flex gap-2 gap-md-3">
                        
                        <button type="submit" class="btn btn-bnpb-primary btn-mobile-fix flex-grow-1 flex-md-grow-0" id="btnCari">
                            <i class="bi bi-search me-2"></i> 
                            <span>Cari Data</span>
                        </button>
                        
                        @if(isset($data))
                        <button type="submit" formaction="{{ route('cek-cuti.export') }}" class="btn btn-bnpb-secondary btn-mobile-fix flex-grow-1 flex-md-grow-0">
                            <i class="bi bi-file-earmark-excel me-2"></i> 
                            <span>Export Excel</span>
                        </button>
                        @endif
                        
                    </div>
                </form>

                @if(isset($data))
                <div class="mt-4 pt-4 border-top">
                    <div class="d-flex align-items-start">
                        <div class="flex-shrink-0">
                             <div class="rounded-3 bg-light p-3 text-bnpb-blue shadow-sm">
                                <i class="bi bi-person-badge fs-3"></i>
                             </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h5 class="mb-1 fw-bold text-bnpb-blue">{{ $data['pegawai']->nama }}</h5>
                            <div class="d-flex flex-wrap gap-2 mb-2">
                                <span class="badge bg-secondary bg-opacity-10 text-secondary border fw-normal">{{ $data['pegawai']->nip }}</span>
                                <span class="badge bg-info bg-opacity-10 text-info border fw-normal">{{ $data['pegawai']->jenis }}</span>
                            </div>
                            <p class="mb-1 text-dark fw-medium small">{{ $data['pegawai']->jabatan }}</p>
                            <p class="mb-0 text-muted small"><i class="bi bi-building me-1"></i> {{ $data['pegawai']->unit_kerja }}</p>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-4 fade-in-up delay-200">
        @if(isset($data))
            <div class="card card-hero shadow-lg h-100">
                <div class="card-body p-4 d-flex flex-column">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <h6 class="text-white-50 text-uppercase ls-1 mb-1" style="letter-spacing: 1px; font-size: 0.75rem;">Sisa Cuti Tahun {{ $data['tahun'] }}</h6>
                            <h2 class="display-4 fw-bold mb-0">{{ $data['sisa_cuti'] }} <span class="fs-6 fw-normal">Hari</span></h2>
                        </div>
                        <div class="p-2 bg-white bg-opacity-25 rounded-circle">
                            <i class="bi bi-wallet2 fs-4 text-white"></i>
                        </div>
                    </div>

                    @if($data['status_cuti_besar'])
                        <div class="alert alert-warning border-0 py-2 px-3 small mb-auto">
                            <i class="bi bi-exclamation-triangle-fill me-1"></i> Cuti Besar diambil (Sisa Hangus)
                        </div>
                    @else
                        <div class="row g-2 mb-auto">
                            <div class="col-6">
                                <div class="stat-box-light">
                                    <span class="d-block text-white-50 small" style="font-size: 0.7rem;">Jatah Dasar</span>
                                    <span class="fw-bold">{{ $data['jatah_dasar'] }}</span>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="stat-box-light">
                                    <span class="d-block text-white-50 small" style="font-size: 0.7rem;">Carry Over (Lalu)</span>
                                    <span class="fw-bold">+ {{ $data['carry_over_tahun_lalu'] }}</span>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="stat-box-light">
                                    <span class="d-block text-white-50 small" style="font-size: 0.7rem;">Total Potensi</span>
                                    <span class="fw-bold">{{ $data['total_jatah'] }}</span>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="stat-box-light position-relative overflow-hidden">
                                    <span class="d-block text-white-50 small" style="font-size: 0.7rem;">Terpakai</span>
                                    <span class="fw-bold text-warning">- {{ $data['cuti_terpakai'] }}</span>
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="mt-3 pt-3 border-top border-white border-opacity-10 d-flex justify-content-between align-items-center">
                        <span class="small text-white-50">Carry Over (Depan)</span>
                        <span class="fw-bold">{{ $data['carry_over_tahun_depan'] }} Hari</span>
                    </div>
                </div>
            </div>
        @else
            <div class="card card-custom text-center py-5 h-100 justify-content-center">
                <div class="card-body">
                    <img src="https://cdn-icons-png.flaticon.com/512/7486/7486754.png" width="80" class="opacity-25 mb-3">
                    <h6 class="text-muted">Data Kosong</h6>
                    
                    <p class="small text-muted mb-0">
                        <span class="d-none d-lg-inline">Lakukan pencarian pegawai di kiri.</span> <span class="d-lg-none">Lakukan pencarian pegawai di atas.</span> </p>
                    
                </div>
            </div>
        @endif
    </div>

    @if(isset($data))
    <div class="col-12 fade-in-up delay-300">
        
        <ul class="nav nav-pills nav-pills-custom mb-3" id="pills-tab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="pills-history-tab" data-bs-toggle="pill" data-bs-target="#pills-history" type="button" role="tab" aria-controls="pills-history" aria-selected="true">
                    <i class="bi bi-clock-history"></i> Riwayat Cuti
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="pills-stats-tab" data-bs-toggle="pill" data-bs-target="#pills-stats" type="button" role="tab" aria-controls="pills-stats" aria-selected="false">
                    <i class="bi bi-bar-chart-line"></i> Statistik
                </button>
            </li>
        </ul>

        <div class="tab-content" id="pills-tabContent">
            
            <div class="tab-pane fade show active" id="pills-history" role="tabpanel" aria-labelledby="pills-history-tab">
                <div class="card card-custom">
                    <div class="card-header-custom d-flex justify-content-between align-items-center">
                         <span>Riwayat Pengajuan (Cuti Tahunan)</span>
                         <span class="badge bg-light text-secondary border fw-normal">{{ count($data['riwayat']) }} Data</span>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive table-responsive-mobile">  
                            <table class="table align-middle mb-0 table-hover">
                                <thead class="bg-light text-secondary">
                                    <tr>
                                        <th class="px-4 py-3 small fw-bold text-uppercase border-bottom">Jenis Cuti</th>
                                        <th class="px-4 py-3 small fw-bold text-uppercase border-bottom">Periode</th>
                                        <th class="px-4 py-3 small fw-bold text-uppercase border-bottom text-center">Durasi</th>
                                        <th class="px-4 py-3 small fw-bold text-uppercase border-bottom">Keterangan</th>
                                        <th class="px-4 py-3 small fw-bold text-uppercase border-bottom">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($data['riwayat'] as $item)
                                    <tr>
                                        <td class="px-4 py-3">
                                            <span class="fw-bold text-bnpb-blue small">{{ $item->jenis_cuti }}</span>
                                        </td>
                                        <td class="px-4 py-3 small text-muted">
                                            <div class="d-flex flex-column">
                                                <span><i class="bi bi-calendar-event me-1"></i> {{ \Carbon\Carbon::parse($item->tanggal_mulai)->format('d M Y') }}</span>
                                                <span class="text-xs text-secondary ms-3">s/d {{ \Carbon\Carbon::parse($item->tanggal_selesai)->format('d M Y') }}</span>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            <span class="badge bg-light text-dark border px-3 py-2 rounded-pill">{{ $item->lama_cuti }} Hari</span>
                                        </td>
                                        <td class="px-4 py-3 small text-secondary">
                                            {{ Str::limit($item->keterangan ?? '-', 40) }}
                                        </td>
                                        <td class="px-4 py-3">
                                            <span class="badge bg-success bg-opacity-10 text-success badge-status border border-success border-opacity-25">
                                                <i class="bi bi-check-circle-fill me-1"></i> Disetujui
                                            </span>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-5">
                                            <img src="https://cdn-icons-png.flaticon.com/512/7486/7486754.png" alt="Empty" width="60" class="mb-3 opacity-50">
                                            <p class="text-muted small mb-0">Belum ada riwayat cuti tahunan yang ditemukan.</p>
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="tab-pane fade" id="pills-stats" role="tabpanel" aria-labelledby="pills-stats-tab">
                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="card card-custom h-100">
                            <div class="card-header-custom">
                                <i class="bi bi-pie-chart text-bnpb-orange"></i> Statistik Bulanan
                            </div>
                            <div class="card-body p-3">
                                <div style="height: 300px; position: relative;">
                                    <canvas id="chartDonut"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card card-custom h-100">
                            <div class="card-header-custom">
                                <i class="bi bi-graph-up text-bnpb-orange"></i> Tren Jenis Cuti
                            </div>
                            <div class="card-body p-3">
                                <div style="height: 300px; position: relative;">
                                    <canvas id="chartLine"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
    @endif

</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    // Loading State untuk Tombol Cari
    const form = document.getElementById('searchForm');
    const btnCari = document.getElementById('btnCari');
    
    if(form) {
        form.addEventListener('submit', function() {
            const originalText = btnCari.innerHTML;
            btnCari.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Memproses...';
            btnCari.classList.add('disabled');
        });
    }

    // Initialize Tooltips Bootstrap
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
      return new bootstrap.Tooltip(tooltipTriggerEl)
    })
</script>

@if(isset($data))
<script>
    document.addEventListener("DOMContentLoaded", function() {
        // --- DATA DARI CONTROLLER ---
        const donutData = @json($data['chart_donut']); 
        const lineDataSets = @json($data['chart_line']); 

        // --- CHART 1: DONUT (Bulanan) ---
        const ctxDonut = document.getElementById('chartDonut').getContext('2d');
        new Chart(ctxDonut, {
            type: 'doughnut',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'],
                datasets: [{
                    label: 'Hari Cuti',
                    data: donutData,
                    backgroundColor: [
                        '#0f3878', '#ff6b00', '#3b82f6', '#10b981', '#f59e0b', '#ef4444',
                        '#6366f1', '#8b5cf6', '#ec4899', '#14b8a6', '#84cc16', '#64748b'
                    ],
                    borderWidth: 2,
                    borderColor: '#ffffff',
                    hoverOffset: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '70%', 
                plugins: {
                    legend: { 
                        position: 'right', 
                        labels: { 
                            boxWidth: 12, 
                            usePointStyle: true,
                            font: { size: 11 }
                        } 
                    }
                },
                animation: {
                    animateScale: true,
                    animateRotate: true,
                    duration: 1500,
                    easing: 'easeOutQuart'
                }
            }
        });

        // --- CHART 2: LINE (Jenis Cuti) ---
        const ctxLine = document.getElementById('chartLine').getContext('2d');
        new Chart(ctxLine, {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'],
                datasets: lineDataSets
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',    
                    intersect: false, 
                },
                plugins: {
                    legend: { 
                        position: 'bottom', 
                        labels: { 
                            usePointStyle: true,
                            padding: 20 
                        } 
                    },
                    tooltip: {
                        enabled: true,
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        padding: 12,      
                        titleFont: { size: 14 },
                        bodyFont: { size: 13 }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { 
                            stepSize: 1,
                            font: { size: 11 } 
                        },
                        grid: { borderDash: [5, 5] }
                    },
                    x: {
                        grid: { display: false },
                        ticks: { font: { size: 11 } }
                    }
                },
                elements: {
                    line: { tension: 0.4 }, 
                    point: { 
                        radius: 4,          
                        hitRadius: 20,      
                        hoverRadius: 6 
                    }
                },
                animation: {
                    duration: 2000,
                    easing: 'easeOutQuart'
                }
            }   
        });
    });
</script>
@endif

@endsection