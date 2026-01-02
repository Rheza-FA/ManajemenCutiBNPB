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

    /* --- CUSTOM CARD STYLE --- */
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
    }
    .form-control-custom:focus {
        border-color: #ff6b00;
        box-shadow: 0 0 0 4px rgba(255, 107, 0, 0.1);
        outline: none;
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
</style>

<div class="d-flex justify-content-between align-items-center mb-5 fade-in-up">
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
                                   value="{{ old('tgl_dari', $data['tahun'] ?? date('Y') . '-01-01') }}">
                        </div>
                        <div class="col-md-6">
                            <label for="tgl_sampai" class="form-label">Periode Akhir</label>
                            <input type="date" class="form-control form-control-custom" id="tgl_sampai" name="tgl_sampai">
                        </div>
                    </div>

                    <div class="d-flex gap-3">
                        <button type="submit" class="btn btn-bnpb-primary d-flex align-items-center" id="btnCari">
                            <i class="bi bi-search me-2"></i> Cari Data
                        </button>
                        
                        @if(isset($data))
                        <button type="submit" formaction="{{ route('cek-cuti.export') }}" class="btn btn-bnpb-secondary d-flex align-items-center">
                            <i class="bi bi-file-earmark-excel me-2"></i> Export Excel
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
        <div class="card card-custom h-100 bg-white">
            <div class="card-header-custom justify-content-between">
                <div>
                    <i class="bi bi-pie-chart text-bnpb-orange"></i> Ringkasan Cuti
                </div>
                <span class="badge bg-light text-dark border">{{ isset($data) ? $data['tahun'] : date('Y') }}</span>
            </div>
            
            <div class="card-body p-4 d-flex flex-column justify-content-center">
                @if(isset($data))
                    
                    {{-- ALERT JIKA CUTI BESAR --}}
                    @if($data['status_cuti_besar'])
                    <div class="alert alert-warning d-flex align-items-start mb-4 shadow-sm border-0" role="alert" style="background-color: #fff7ed; color: #c2410c;">
                        <i class="bi bi-exclamation-triangle-fill me-2 mt-1"></i>
                        <div class="small lh-sm">
                            <strong>Perhatian:</strong><br>
                            Pegawai mengambil <u>Cuti Besar</u> tahun ini. Sisa Cuti Tahunan otomatis <strong>HANGUS</strong>.
                        </div>
                    </div>
                    @endif

                    {{-- ANGKA SISA CUTI (SELALU TAMPIL) --}}
                    <div class="text-center mb-4 position-relative">
                        <div class="display-3 fw-bold {{ $data['sisa_cuti'] < 0 ? 'text-danger' : 'text-success' }}">
                            {{ $data['sisa_cuti'] }}
                        </div>
                        <div class="text-muted fw-bold small text-uppercase">Hari Tersisa</div>
                        
                        <div class="position-absolute top-50 start-50 translate-middle rounded-circle" 
                             style="width: 120px; height: 120px; background: {{ $data['sisa_cuti'] < 0 ? '#fee2e2' : '#dcfce7' }}; z-index: -1; opacity: 0.5;"></div>
                    </div>
                    
                    <div class="vstack gap-3">
                        
                        {{-- TAMPILKAN RINCIAN HANYA JIKA BUKAN CUTI BESAR --}}
                        @if(!$data['status_cuti_besar'])
                            <div class="d-flex justify-content-between align-items-center border-bottom pb-2 border-dashed">
                                <span class="stat-label">Jatah Dasar</span>
                                <span class="stat-value">{{ $data['jatah_dasar'] }}</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center border-bottom pb-2 border-dashed">
                                <span class="stat-label">Carry Over (Lalu)</span>
                                <span class="stat-value text-primary">+ {{ $data['carry_over_tahun_lalu'] }}</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center bg-light p-2 rounded">
                                <span class="stat-label fw-bold text-dark">Total Jatah</span>
                                <span class="stat-value text-dark">{{ $data['total_jatah'] }}</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center border-bottom pb-2 border-dashed">
                                <span class="stat-label text-danger">Cuti Diambil</span>
                                <span class="stat-value text-danger">- {{ $data['cuti_terpakai'] }}</span>
                            </div>
                        @endif

                        {{-- CARRY OVER DEPAN (SELALU TAMPIL) --}}
                        <div class="d-flex justify-content-between align-items-center pt-1">
                            <span class="stat-label fw-bold text-bnpb-blue">Carry Over (Depan)</span>
                            <span class="stat-value text-bnpb-blue">{{ $data['carry_over_tahun_depan'] }}</span>
                        </div>
                    </div>

                @else
                    <div class="text-center py-5 text-muted">
                        <i class="bi bi-clipboard-data fs-1 d-block mb-3 opacity-25"></i>
                        <p class="small">Silakan lakukan pencarian untuk melihat data sisa cuti.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-md-6 fade-in-up delay-300">
        <div class="card card-custom h-100">
            <div class="card-header-custom">
                <i class="bi bi-bar-chart-line text-bnpb-orange"></i> Statistik Bulanan
            </div>
            <div class="card-body p-3">
                <div style="height: 300px; position: relative;">
                    @if(isset($data))
                        <canvas id="chartDonut"></canvas>
                    @else
                        <div class="d-flex flex-column align-items-center justify-content-center h-100 text-muted opacity-50">
                            <i class="bi bi-pie-chart-fill fs-1 mb-2"></i>
                            <span class="small">Menunggu data...</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6 fade-in-up delay-300">
        <div class="card card-custom h-100">
            <div class="card-header-custom">
                <i class="bi bi-graph-up text-bnpb-orange"></i> Tren Jenis Cuti
            </div>
            <div class="card-body p-3">
                <div style="height: 300px; position: relative;">
                    @if(isset($data))
                        <canvas id="chartLine"></canvas>
                    @else
                        <div class="d-flex flex-column align-items-center justify-content-center h-100 text-muted opacity-50">
                            <i class="bi bi-graph-up-arrow fs-1 mb-2"></i>
                            <span class="small">Menunggu data...</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 fade-in-up delay-300">
        <div class="card card-custom">
            <div class="card-header-custom d-flex justify-content-between align-items-center">
                <span>
                    <i class="bi bi-clock-history text-bnpb-orange"></i> Riwayat Cuti (Cuti Tahunan Only)
                </span>
                <button class="btn btn-sm btn-light border" type="button" data-bs-toggle="tooltip" title="Hanya menampilkan Cuti Tahunan">
                    <i class="bi bi-info-circle"></i> Info
                </button>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
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
                        @if(isset($data))
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
                        @else
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted small">
                                    Silakan lakukan pencarian pegawai terlebih dahulu.
                                </td>
                            </tr>
                        @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    // Loading State untuk Tombol Cari
    const form = document.getElementById('searchForm');
    const btnCari = document.getElementById('btnCari');
    
    if(form) {
        form.addEventListener('submit', function() {
            // Cek apakah tombol yang diklik adalah export (jangan loading state kalo export)
            // Tapi karena form submit generic, kita bisa kasih delay dikit visual feedback
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
                cutout: '70%', // Donut lebih tipis
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
                plugins: {
                    legend: { position: 'bottom', labels: { usePointStyle: true } }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { stepSize: 1 },
                        grid: { borderDash: [5, 5] }
                    },
                    x: {
                        grid: { display: false }
                    }
                },
                elements: {
                    line: { tension: 0.4 }, // Smooth curve
                    point: { radius: 4, hoverRadius: 6 }
                },
                interaction: {
                    mode: 'index',
                    intersect: false,
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