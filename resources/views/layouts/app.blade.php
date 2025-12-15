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
            --bnpb-blue: #0f3878;   /* Warna Biru Tua BNPB */
            --bnpb-orange: #ff6b00; /* Warna Oranye BNPB */
            --bg-light: #f1f5f9;    /* Background Abu Muda */
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-light);
            color: #334155;
        }

        /* --- SIDEBAR STYLE --- */
        .sidebar {
            background-color: var(--bnpb-blue);
            min-height: 100vh;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
        }

        .sidebar-brand {
            padding: 25px 20px;
            display: flex;
            align-items: center;
            gap: 12px;
            color: #ffffff;
            font-weight: 700;
            font-size: 1.2rem;
            text-decoration: none;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            margin-bottom: 20px;
        }
        
        .sidebar-brand:hover { color: #fff; }

        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.7);
            font-weight: 500;
            padding: 12px 20px;
            margin: 4px 15px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            transition: all 0.3s;
        }

        .sidebar .nav-link i {
            margin-right: 12px;
            font-size: 1.1rem;
        }

        .sidebar .nav-link:hover {
            color: #ffffff;
            background-color: rgba(255, 255, 255, 0.1);
        }

        .sidebar .nav-link.active {
            background-color: var(--bnpb-orange);
            color: #ffffff;
            box-shadow: 0 4px 6px rgba(0,0,0,0.2);
            font-weight: 600;
        }

        /* --- CONTENT STYLE --- */
        .main-content {
            padding: 30px;
        }

        .navbar-mobile {
            background-color: var(--bnpb-blue);
            color: white;
        }
        
        footer {
            color: #94a3b8;
            font-size: 0.85rem;
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #e2e8f0;
        }
    </style>
</head>
<body>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-3 col-lg-2 d-none d-md-block sidebar px-0">
            <a href="#" class="sidebar-brand">
                <i class="bi bi-building-fill-check fs-4 text-warning"></i>
                <span>Cuti BNPB</span>
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
                    <span class="navbar-brand mb-0 h1 text-white">Cuti BNPB</span>
                    <button class="btn btn-sm btn-outline-light" type="button" data-bs-toggle="collapse" data-bs-target="#sidebarMenu">
                        <i class="bi bi-list"></i>
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
</body>
</html>