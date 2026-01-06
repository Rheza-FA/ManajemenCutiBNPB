<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Manajemen Cuti BNPB')</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --bnpb-blue: #0f3878;   
            --bnpb-orange: #ff6b00; 
            --bg-light: #f1f5f9;    
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-light);
            color: #334155;
            overflow-x: hidden; 
        }

        /* --- SPLASH SCREEN STYLE --- */
        #splash-screen {
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background-color: #ffffff;
            z-index: 99999;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            transition: opacity 0.5s ease-out, visibility 0.5s ease-out;
        }
        #splash-screen.hide { opacity: 0; visibility: hidden; }

        .splash-logo { animation: pulse 2s infinite; }
        @keyframes pulse {
            0% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.05); opacity: 0.8; }
            100% { transform: scale(1); opacity: 1; }
        }

        .loading-dots { display: flex; gap: 8px; margin-top: 20px; }
        .dot {
            width: 10px; height: 10px;
            background-color: var(--bnpb-orange);
            border-radius: 50%;
            animation: bounce 1.4s infinite ease-in-out both;
        }
        .dot:nth-child(1) { animation-delay: -0.32s; }
        .dot:nth-child(2) { animation-delay: -0.16s; }
        @keyframes bounce { 0%, 80%, 100% { transform: scale(0); } 40% { transform: scale(1); } }

        /* --- SIDEBAR STYLE --- */
        .sidebar {
            background-color: var(--bnpb-blue);
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
            position: sticky; top: 0; height: 100vh;
            overflow-y: auto; z-index: 1000;
            transition: all 0.3s ease-in-out;
        }
        @media (max-width: 767.98px) {
            .sidebar { position: fixed; left: -100%; width: 280px; z-index: 1050; }
            .sidebar.show { left: 0; }
            .sidebar-overlay {
                position: fixed; top: 0; left: 0; right: 0; bottom: 0;
                background: rgba(0, 0, 0, 0.5); z-index: 1040; display: none; backdrop-filter: blur(2px);
            }
            .sidebar-overlay.show { display: block; }
        }

        .sidebar-brand {
            padding: 25px 20px; display: flex; align-items: center; gap: 12px;
            color: #ffffff; font-weight: 700; font-size: 1.2rem; text-decoration: none;
            border-bottom: 1px solid rgba(255,255,255,0.1); margin-bottom: 20px;
        }
        .sidebar-brand:hover { color: #fff; }

        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.7); font-weight: 500; padding: 12px 20px; margin: 4px 15px;
            border-radius: 8px; display: flex; align-items: center; transition: all 0.3s;
        }
        .sidebar .nav-link i { margin-right: 12px; font-size: 1.1rem; }
        .sidebar .nav-link:hover { color: #ffffff; background-color: rgba(255, 255, 255, 0.1); }
        .sidebar .nav-link.active {
            background-color: var(--bnpb-orange); color: #ffffff;
            box-shadow: 0 4px 6px rgba(0,0,0,0.2); font-weight: 600;
        }

        .main-content { padding: 20px; }
        @media (min-width: 768px) { .main-content { padding: 30px; } }

       /* --- NAVBAR MOBILE STYLE (NO STICKY) --- */
        .navbar-mobile {
            background-color: var(--bnpb-blue) !important; color: white;
            position: relative; z-index: 990; 
            box-shadow: 0 4px 12px rgba(0,0,0,0.15) !important;
            border-radius: 8px !important; margin-bottom: 1.5rem; 
        }

        @media (max-width: 767.98px) {
            .table-responsive-mobile { display: block; width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch; }
            .table-responsive-mobile .table th, .table-responsive-mobile .table td { white-space: nowrap; }
        }

        footer { color: #94a3b8; font-size: 0.85rem; margin-top: 40px; padding-top: 20px; border-top: 1px solid #e2e8f0; }
    </style>
</head>
<body>

<div id="splash-screen">
    <img src="{{ asset('img/logo_bnpb.png') }}" alt="BNPB Logo" width="120" class="splash-logo mb-3">
    <h5 class="fw-bold" style="color: var(--bnpb-blue); letter-spacing: 1px;">MANAJEMEN CUTI</h5>
    <p class="text-muted small">Badan Nasional Penanggulangan Bencana</p>
    <div class="loading-dots">
        <div class="dot"></div><div class="dot"></div><div class="dot"></div>
    </div>
</div>
<div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-3 col-lg-2 sidebar px-0" id="sidebarMenu">
            <div class="d-flex justify-content-between align-items-center pe-3 d-md-none">
                <a href="#" class="sidebar-brand mb-0 border-0">
                    <img src="{{ asset('img/logo_bnpb.png') }}" alt="Logo" height="35" class="d-inline-block align-text-top">
                    <span class="ms-2">Cuti BNPB</span>
                </a>
                <button class="btn btn-sm text-white-50" onclick="toggleSidebar()">
                    <i class="bi bi-x-lg fs-4"></i>
                </button>
            </div>

            <a href="#" class="sidebar-brand d-none d-md-flex">
                <img src="{{ asset('img/logo_bnpb.png') }}" alt="Logo" height="35" class="d-inline-block align-text-top">
                <span class="ms-2">Cuti BNPB</span>
            </a>
            
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('cek-cuti.*') ? 'active' : '' }}" href="{{ route('cek-cuti.index') }}">
                        <i class="bi bi-speedometer2"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('import.*') ? 'active' : '' }}" href="{{ route('import.index') }}">
                        <i class="bi bi-cloud-upload"></i> Import Data
                    </a>
                </li>
            </ul>
        </div>

        <main class="col-md-9 ms-sm-auto col-lg-10 main-content">
            <nav class="navbar navbar-mobile d-md-none mb-4 rounded shadow-sm px-3 py-2">
                <div class="d-flex align-items-center w-100 justify-content-between">
                    <div class="d-flex align-items-center gap-2">
                        <img src="{{ asset('img/logo_bnpb.png') }}" alt="Logo" height="30">
                        <span class="fw-bold text-white small">Cuti BNPB</span>
                    </div>
                    <button class="btn btn-sm text-white border-0" type="button" onclick="toggleSidebar()">
                        <i class="bi bi-list fs-4"></i>
                    </button>
                </div>
            </nav>

            @yield('content')
            
            <footer class="text-center">
                <p>&copy; {{ date('Y') }} Badan Nasional Penanggulangan Bencana. All rights reserved.</p>
            </footer>
        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    // Toggle Sidebar Mobile
    function toggleSidebar() {
        const sidebar = document.getElementById('sidebarMenu');
        const overlay = document.getElementById('sidebarOverlay');
        sidebar.classList.toggle('show');
        overlay.classList.toggle('show');
    }

    // [LOGIKA BARU SPLASH SCREEN - HANYA SEKALI PER SESI]
    document.addEventListener("DOMContentLoaded", function() {
        const splashScreen = document.getElementById('splash-screen');
        
        // 1. Cek apakah user sudah pernah melihat splash screen di sesi browser ini?
        const hasSeenSplash = sessionStorage.getItem('bnpb_splash_seen');

        if (hasSeenSplash) {
            // JIKA SUDAH: Hapus elemen splash screen LANGSUNG (Tanpa delay/animasi)
            splashScreen.style.display = 'none'; 
            splashScreen.remove();
        } else {
            // JIKA BELUM: Jalankan animasi normal
            window.addEventListener('load', function() {
                setTimeout(function() {
                    splashScreen.classList.add('hide'); // Efek fade-out
                    
                    setTimeout(() => {
                        splashScreen.remove(); // Hapus dari DOM
                        // Simpan tanda "SUDAH LIHAT" ke sessionStorage
                        sessionStorage.setItem('bnpb_splash_seen', 'true');
                    }, 500); // Tunggu efek css selesai
                    
                }, 1500); // Durasi tampil splash screen (1.5 detik)
            });
        }
    });
</script>
</body>
</html>