@extends('layouts.app')

@section('title', 'Import Data Master')

@section('content')

<style>
    /* --- ANIMATION --- */
    .fade-in-up {
        animation: fadeInUp 0.6s ease-out forwards;
        opacity: 0;
        transform: translateY(20px);
    }
    
    @keyframes fadeInUp {
        to { opacity: 1; transform: translateY(0); }
    }
    
    .delay-100 { animation-delay: 0.1s; }
    .delay-200 { animation-delay: 0.2s; }

    /* --- THEME COLORS --- */
    .text-bnpb-blue { color: #0f3878; }
    .text-bnpb-orange { color: #ff6b00; }

    /* --- CARD STYLE --- */
    .card-custom {
        background: #ffffff;
        border: none;
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.05);
        transition: transform 0.2s;
        overflow: hidden;
    }
    .card-custom:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.08);
    }

    .card-header-custom {
        background: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
        padding: 20px 24px;
        font-weight: 700;
        color: #0f3878;
        font-size: 1.1rem;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    /* --- FORM ELEMENTS --- */
    .form-select-custom, .form-control-custom {
        border: 1px solid #cbd5e1;
        padding: 12px 16px;
        border-radius: 10px;
        font-size: 0.95rem;
    }
    .form-select-custom:focus, .form-control-custom:focus {
        border-color: #ff6b00;
        box-shadow: 0 0 0 4px rgba(255, 107, 0, 0.1);
        outline: none;
    }

    /* --- BUTTONS --- */
    .btn-bnpb-primary {
        background: linear-gradient(135deg, #ff6b00 0%, #ea580c 100%);
        border: none;
        color: white;
        font-weight: 600;
        padding: 12px 28px;
        border-radius: 10px;
        transition: all 0.3s ease;
        width: 100%;
    }
    .btn-bnpb-primary:hover {
        background: linear-gradient(135deg, #ea580c 0%, #c2410c 100%);
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(255, 107, 0, 0.3);
        color: white;
    }

    /* --- FILE UPLOAD AREA STYLE --- */
    .upload-area {
        border: 2px dashed #cbd5e1;
        border-radius: 12px;
        padding: 30px;
        text-align: center;
        background-color: #f8fafc;
        transition: all 0.3s;
        cursor: pointer;
        position: relative;
    }
    .upload-area:hover {
        border-color: #ff6b00;
        background-color: #fff7ed; /* Orange muda */
    }
    .upload-area input[type="file"] {
        position: absolute;
        width: 100%;
        height: 100%;
        top: 0;
        left: 0;
        opacity: 0;
        cursor: pointer;
    }
    .upload-icon {
        font-size: 2.5rem;
        color: #94a3b8;
        transition: color 0.3s;
    }
    .upload-area:hover .upload-icon {
        color: #ff6b00;
    }
</style>

<div class="d-flex justify-content-between align-items-center mb-5 fade-in-up">
    <div>
        <h3 class="fw-bold text-bnpb-blue mb-1">
            <i class="bi bi-database-add me-2"></i>Import Data Master
        </h3>
        <p class="text-muted mb-0 small">Update data pegawai dan riwayat cuti massal melalui file Excel.</p>
    </div>
    <div class="d-none d-md-block">
        <button class="btn btn-light border text-muted btn-sm" onclick="window.history.back()">
            <i class="bi bi-arrow-left me-1"></i> Kembali
        </button>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-lg-6 col-md-8 fade-in-up delay-100">
        
        @if(session('success'))
        <div class="alert alert-success border-0 d-flex align-items-center shadow-sm mb-4" role="alert" style="background-color: #dcfce7; color: #166534; border-radius: 10px;">
            <div class="bg-white rounded-circle p-1 me-3 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                <i class="bi bi-check-lg"></i>
            </div>
            <div>
                <strong class="d-block">Berhasil!</strong>
                <span class="small">{{ session('success') }}</span>
            </div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        @endif

        @if(session('error'))
        <div class="alert alert-danger border-0 d-flex align-items-center shadow-sm mb-4" role="alert" style="background-color: #fee2e2; color: #b91c1c; border-radius: 10px;">
            <div class="bg-white rounded-circle p-1 me-3 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                <i class="bi bi-x-lg"></i>
            </div>
            <div>
                <strong class="d-block">Gagal Import!</strong>
                <span class="small">{{ session('error') }}</span>
            </div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        @endif

        <div class="card card-custom">
            <div class="card-header-custom">
                <div class="rounded p-2 bg-primary bg-opacity-10 text-primary">
                    <i class="bi bi-file-earmark-spreadsheet fs-5"></i>
                </div>
                Form Upload Excel
            </div>
            
            <div class="card-body p-4">
                <form id="importForm" action="{{ route('import.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    
                    <div class="mb-4">
                        <label class="form-label">Kategori Data <span class="text-danger">*</span></label>
                        <select name="kategori" class="form-select form-select-custom" required>
                            <option value="" disabled selected> Pilih Jenis Data yang Diupload </option>
                            <option value="pns"> Data Pegawai PNS</option>
                            <option value="cpns"> Data Pegawai CPNS</option>
                            <option value="pppk"> Data Pegawai PPPK</option>
                            <option value="riwayat_cuti"> Data Riwayat Cuti</option>
                        </select>
                        <div class="form-text text-muted small mt-2">
                            <i class="bi bi-info-circle me-1"></i> Pilih kategori yang sesuai dengan isi file Excel Anda.
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">File Excel (.xlsx / .csv) <span class="text-danger">*</span></label>
                        
                        <div class="upload-area" id="dropZone">
                            <input type="file" name="file" id="fileInput" accept=".xlsx, .xls, .csv" required>
                            <i class="bi bi-cloud-arrow-up-fill upload-icon mb-2 d-block"></i>
                            <h6 class="fw-bold mb-1" id="fileName">Klik atau Tarik File ke Sini</h6>
                            <p class="text-muted small mb-0">Maksimal ukuran file 5MB</p>
                        </div>
                    </div>

                    <div class="mt-5">
                        <button type="submit" class="btn btn-bnpb-primary" id="btnImport">
                            <i class="bi bi-box-arrow-in-down me-2"></i> Proses Import Data
                        </button>
                    </div>
                </form>
            </div>
            
            <div class="card-footer bg-light text-center py-3 border-top">
                <small class="text-muted">
                    <i class="bi bi-shield-lock me-1"></i> Pastikan format header Excel sesuai dengan template database.
                </small>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const fileInput = document.getElementById('fileInput');
        const fileNameDisplay = document.getElementById('fileName');
        const importForm = document.getElementById('importForm');
        const btnImport = document.getElementById('btnImport');
        const dropZone = document.getElementById('dropZone');

        // 1. Update nama file saat file dipilih
        fileInput.addEventListener('change', function() {
            if (this.files && this.files.length > 0) {
                fileNameDisplay.innerHTML = `<span class="text-bnpb-blue">${this.files[0].name}</span>`;
                dropZone.style.borderColor = '#0f3878';
                dropZone.style.backgroundColor = '#f0f9ff';
            } else {
                fileNameDisplay.textContent = "Klik atau Tarik File ke Sini";
                dropZone.style.borderColor = '#cbd5e1';
                dropZone.style.backgroundColor = '#f8fafc';
            }
        });

        // 2. Loading State saat tombol diklik
        importForm.addEventListener('submit', function() {
            const originalContent = btnImport.innerHTML;
            btnImport.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Memproses...';
            btnImport.classList.add('disabled');
            // Mencegah double submit
            setTimeout(() => {
                btnImport.classList.remove('disabled');
                btnImport.innerHTML = originalContent;
            }, 10000); // Reset setelah 10 detik (timeout)
        });
    });
</script>

@endsection