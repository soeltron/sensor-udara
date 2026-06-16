<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- Bug #3 Fix: Prevent browser from caching authenticated pages -->
    <meta http-equiv="Cache-Control" content="no-store, no-cache, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title>@yield('title', 'IoT Monitoring') — Dashboard IoT</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #eef2f7;
            color: #2b2d42;
        }

        /* Sidebar */
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(180deg, #1a1f36 0%, #212544 100%);
            position: fixed;
            top: 0;
            left: 0;
            width: 260px;
            z-index: 1000;
            box-shadow: 4px 0 20px rgba(0,0,0,0.12);
            display: flex;
            flex-direction: column;
            transition: width 0.25s ease, background 0.25s ease;
            overflow: hidden;
        }

        .sidebar-brand {
            padding: 1.4rem 1.5rem;
            border-bottom: 1px solid rgba(255,255,255,0.08);
            display: flex;
            align-items: center;
            justify-content: flex-start;
        }

        .sidebar-brand h5 {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        body.sidebar-collapsed .sidebar {
            width: 72px;
        }

        body.sidebar-collapsed .main-content {
            margin-left: 72px;
        }

        .sidebar-collapsed .sidebar .nav-link {
            justify-content: center;
            padding: 0.85rem 0.9rem;
        }

        .sidebar .nav-link .nav-text {
            transition: opacity 0.2s ease;
        }

        body.sidebar-collapsed .sidebar .nav-link .nav-text {
            opacity: 0;
            width: 0;
            max-width: 0;
            margin: 0;
            overflow: hidden;
            white-space: nowrap;
        }

        body.sidebar-collapsed .sidebar-brand {
            justify-content: center;
            padding: 1rem 0.8rem;
        }

        body.sidebar-collapsed .sidebar-brand h5 {
            font-size: 0;
            margin: 0;
        }

        body.sidebar-collapsed .sidebar-brand small {
            display: none;
        }

        .sidebar-brand h5 {
            color: #fff;
            font-weight: 700;
            font-size: 1.15rem;
            letter-spacing: 0.02em;
            margin: 0;
        }

        .sidebar-brand small {
            color: rgba(255,255,255,0.45);
            font-size: 0.72rem;
        }

        .sidebar .nav-link {
            color: rgba(255,255,255,0.65);
            padding: 0.85rem 1.5rem;
            font-weight: 500;
            font-size: 0.9rem;
            border-radius: 0;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
        }

        .sidebar .nav-link i {
            width: 20px;
            margin-right: 0.75rem;
            font-size: 0.95rem;
            opacity: 0.8;
        }

        .sidebar .nav-link .nav-text {
            display: inline-block;
        }

        .sidebar .nav-link.active,
        .sidebar .nav-link:hover {
            background: rgba(99, 102, 241, 0.2);
            color: #fff;
            border-left: 3px solid #6366f1;
        }

        .sidebar .nav-link.active i,
        .sidebar .nav-link:hover i {
            opacity: 1;
        }

        .sidebar-footer {
            margin-top: auto;
            padding: 1rem 1.5rem;
            border-top: 1px solid rgba(255,255,255,0.08);
        }

        /* Main content — shift right when sidebar visible */
        .main-content {
            margin-left: 260px;
            min-height: 100vh;
        }

        /* No sidebar = no margin */
        body.no-sidebar .main-content,
        body.sidebar-collapsed .main-content {
            margin-left: 0;
        }

        body.sidebar-collapsed .sidebar {
            transform: translateX(-100%);
        }

        body.sidebar-collapsed .sidebar,
        body.sidebar-collapsed .sidebar .nav-link {
            transition: transform 0.25s ease;
        }

        body.dark-mode {
            background-color: #121212;
            color: #e5e7eb;
        }

        body.dark-mode .header {
            background: #1f2937;
            border-color: rgba(255,255,255,0.08);
            color: #e5e7eb;
        }

        body.dark-mode .main-content,
        body.dark-mode .card-custom,
        body.dark-mode .metric-card,
        body.dark-mode .sidebar,
        body.dark-mode .guest-notice,
        body.dark-mode .card-body,
        body.dark-mode .table-responsive,
        body.dark-mode .table,
        body.dark-mode .form-control,
        body.dark-mode .form-select,
        body.dark-mode .input-group-text,
        body.dark-mode .alert,
        body.dark-mode .badge,
        body.dark-mode .card-soft {
            background: #1f2937;
            color: #e5e7eb;
            border-color: rgba(255,255,255,0.08);
            box-shadow: 0 8px 30px rgba(0,0,0,0.25);
        }

        body.dark-mode .sidebar {
            background: #111827;
        }

        body.dark-mode .sidebar .nav-link,
        body.dark-mode .sidebar-brand small,
        body.dark-mode .card-header-custom,
        body.dark-mode .guest-notice,
        body.dark-mode .form-label,
        body.dark-mode .form-text,
        body.dark-mode .text-muted,
        body.dark-mode .table-custom thead th,
        body.dark-mode .table-custom tbody td,
        body.dark-mode .table-custom tbody th,
        body.dark-mode h1,
        body.dark-mode h2,
        body.dark-mode h3,
        body.dark-mode h4,
        body.dark-mode h5,
        body.dark-mode h6,
        body.dark-mode p,
        body.dark-mode div,
        body.dark-mode span,
        body.dark-mode small,
        body.dark-mode label {
            color: #f3f4f6 !important;
        }

        body.dark-mode .sidebar .nav-link.active,
        body.dark-mode .sidebar .nav-link:hover {
            background: rgba(99, 102, 241, 0.3);
            color: #fff;
        }

        body.dark-mode .form-control,
        body.dark-mode .form-select,
        body.dark-mode .input-group-text {
            background: #111827;
            border-color: rgba(255,255,255,0.12);
            color: #e5e7eb;
        }

        body.dark-mode .form-control::placeholder,
        body.dark-mode .form-select option {
            color: #9ca3af;
        }

        body.dark-mode .table-custom thead th {
            border-bottom-color: rgba(255,255,255,0.12);
            color: #f3f4f6 !important;
            background-color: #111827;
        }

        body.dark-mode .table-custom tbody tr {
            border-color: rgba(255,255,255,0.08);
            background-color: #1f2937;
        }

        body.dark-mode .table-custom tbody td {
            background-color: #1f2937;
            color: #f3f4f6 !important;
        }

        body.dark-mode .metric-card:hover,
        body.dark-mode .card-custom,
        body.dark-mode .card-soft {
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        }

        body.dark-mode .bg-light,
        body.dark-mode .bg-opacity-10 {
            background-color: #2d3748 !important;
            color: #cbd5e1;
        }

        body.dark-mode .metric-card {
            background: #1f2937;
            border-color: rgba(255,255,255,0.12);
        }

        body.dark-mode .metric-card:hover {
            background: #2d3748;
        }

        body.dark-mode .icon-box {
            opacity: 0.9;
        }

        body.dark-mode .circle-indicator {
            color: #e5e7eb;
            background: rgba(99, 102, 241, 0.15);
        }

        body.dark-mode .rounded-3 {
            background: #2d3748 !important;
        }

        body.dark-mode .btn-outline-primary,
        body.dark-mode .btn-outline-secondary {
            border-color: rgba(255,255,255,0.3);
            color: #cbd5e1;
        }

        body.dark-mode .btn-outline-primary:hover,
        body.dark-mode .btn-outline-secondary:hover {
            background-color: rgba(99, 102, 241, 0.2);
            border-color: rgba(99, 102, 241, 0.5);
            color: #fff;
        }


        /* Header */
        .header {
            background: #ffffff;
            border-bottom: 1px solid rgba(0, 0, 0, 0.06);
            padding: 0.9rem 2rem;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
        }

        .card-soft {
            background: rgba(255, 255, 255, 0.97);
            border: 1px solid rgba(67, 97, 238, 0.08);
            border-radius: 18px;
            box-shadow: 0 18px 40px rgba(66, 88, 125, 0.08);
        }

        .metric-card {
            background: #fff;
            border-radius: 16px;
            border: 1px solid rgba(0,0,0,0.05);
            box-shadow: 0 4px 20px rgba(0,0,0,0.06);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .metric-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 30px rgba(0,0,0,0.1);
        }

        .sensor-detail-content {
            transition: max-height 0.3s ease, opacity 0.25s ease;
        }

        .circle-indicator {
            width: 84px;
            height: 84px;
            border: 4px solid;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.85rem;
            font-weight: 700;
            color: #1f2937;
        }

        .metric-value {
            font-size: 2.25rem;
            font-weight: 700;
            line-height: 1.1;
        }

        .metric-label {
            color: #6b7280;
            font-size: 0.9rem;
        }

        .icon-box {
            width: 52px;
            height: 52px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
        }

        .card-custom {
            background: #fff;
            border-radius: 16px;
            border: 1px solid rgba(100, 116, 139, 0.18);
            box-shadow: 0 4px 20px rgba(0,0,0,0.05);
        }

        .card-header-custom {
            background: transparent;
            border-bottom: 1px solid rgba(100, 116, 139, 0.16);
            padding: 1rem 1.25rem;
            font-weight: 600;
            color: #374151;
        }

        .btn-toggle {
            min-width: 58px;
            font-size: 0.82rem;
            padding: 0.3rem 0.7rem;
        }

        .table-custom thead th {
            border-bottom: 1px solid rgba(148, 163, 184, 0.24);
            color: #6b7280;
            font-weight: 600;
            font-size: 0.82rem;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        .table-custom tbody td,
        .table-custom tbody th {
            border-top: 1px solid rgba(148, 163, 184, 0.12);
        }

        .badge-role {
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        .status-badge {
            font-size: 0.78rem;
            padding: 0.35rem 0.7rem;
            border-radius: 999px;
        }

        .unit-toggle-btn {
            cursor: pointer;
            border-radius: 999px;
            padding: 0.2rem 0.6rem;
            font-size: 0.75rem;
            font-weight: 600;
            transition: all 0.2s;
        }

        /* Guest notice bar */
        .guest-notice {
            background: linear-gradient(90deg, #f59e0b, #d97706);
            color: #fff;
            font-size: 0.87rem;
            padding: 0.5rem 1.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
    </style>
    @stack('styles')
</head>
<body class="{{ Auth::check() ? '' : 'no-sidebar' }}">

    {{-- Sidebar — only for logged in users --}}
    @auth
    <div class="sidebar">
        <div class="sidebar-brand">
            {{-- Bug #5 Fix: Changed from "IoT Dashboard" to "Dashboard IoT" --}}
            <h5><i class="fas fa-microchip me-2 text-indigo-400" style="color:#a5b4fc;"></i>Dashboard sensor suhu</h5>
            <small></small>
        </div>
        <nav class="nav flex-column py-2">
            <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                <i class="fas fa-tachometer-alt"></i><span class="nav-text">Semua Sensor</span>
            </a>
            <a class="nav-link" href="#section-cards" onclick="showSensorPanel('dht22', event)">
                <i class="fas fa-thermometer-half"></i><span class="nav-text">Sensor DHT22</span>
            </a>
            <a class="nav-link" href="#section-air" onclick="showSensorPanel('mq135', event)">
                <i class="fas fa-wind"></i><span class="nav-text">Sensor MQ-135</span>
            </a>
            <a class="nav-link" href="#section-control" onclick="scrollToSection('section-control', event)">
                <i class="fas fa-cogs"></i><span class="nav-text">Kontrol & Pengaturan</span>
            </a>
            <a class="nav-link" href="#section-charts" onclick="scrollToSection('section-charts', event)">
                <i class="fas fa-chart-area"></i><span class="nav-text">Grafik Realtime</span>
            </a>
            <a class="nav-link" href="#section-history" onclick="scrollToSection('section-history', event)">
                <i class="fas fa-history"></i><span class="nav-text">Data Historis</span>
            </a>
        </nav>
        <div class="sidebar-footer">
            <div class="d-flex align-items-center mb-2">
                <div class="rounded-circle bg-indigo-500 d-flex align-items-center justify-content-center me-2" style="width:34px;height:34px;background:#6366f1;">
                    <i class="fas fa-user text-white" style="font-size:0.8rem;"></i>
                </div>
                <div>
                    <div class="text-white fw-semibold" style="font-size:0.85rem;">{{ Auth::user()->name }}</div>
                    <div style="color:rgba(255,255,255,0.45);font-size:0.72rem;">{{ Auth::user()->role }}</div>
                </div>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn btn-sm w-100" style="background:rgba(239,68,68,0.15);color:#fca5a5;border:1px solid rgba(239,68,68,0.3);">
                    <i class="fas fa-sign-out-alt me-1"></i> Logout
                </button>
            </form>
        </div>
    </div>
    @endauth

    <div class="main-content">
        {{-- Top header for authenticated users --}}
        @auth
        <header class="header d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-2">
                <button id="sidebar-toggle" class="btn btn-sm btn-outline-secondary" type="button" onclick="toggleSidebar()" title="Buka/Tutup sidebar">
                    <i class="fas fa-bars"></i>
                </button>
                <div>
                    <h5 class="mb-0 fw-bold">@yield('page-title', 'Dashboard IoT')</h5>
                    <small class="text-muted">{{ now()->isoFormat('dddd, D MMMM YYYY') }}</small>
                </div>
            </div>
            <div class="d-flex align-items-center gap-2">
                <span id="device-status" class="badge bg-success status-badge">
                    <i class="fa-solid fa-circle-check me-1"></i>Online
                </span>
                @if(Auth::user()->isAdmin())
                    <span class="badge bg-purple text-white badge-role" style="background:#7c3aed;padding:0.4rem 0.8rem;">Admin</span>
                @endif
                <button id="theme-toggle" class="btn btn-sm btn-outline-secondary" type="button" onclick="toggleTheme()" title="Mode gelap / terang">
                    <i class="fas fa-moon"></i> <span id="theme-label">Gelap</span>
                </button>
            </div>
        </header>
        @endauth

        {{-- Guest notice bar --}}
        @guest
        <div class="guest-notice">
            <span><i class="fas fa-eye me-2"></i>Anda melihat dashboard sebagai tamu. Login untuk mengontrol perangkat.</span>
            <div class="d-flex gap-2">
                <a href="{{ route('login') }}" class="btn btn-sm btn-light fw-semibold">Login</a>
                <a href="{{ route('register') }}" class="btn btn-sm btn-outline-light">Daftar</a>
            </div>
        </div>
        @endguest

        <div class="p-4">
            @yield('content')
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
        // Bug #2 Fix: scrollToSection function for sidebar navigation
        function scrollToSection(id, event) {
            if (event) event.preventDefault();
            const el = document.getElementById(id);
            if (el) {
                el.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        }

        function showSensorPanel(sensor, event) {
            if (event) event.preventDefault();
            const cardDht22 = document.getElementById('card-dht22');
            const cardMq135 = document.getElementById('card-mq135');

            if (sensor === 'dht22') {
                if (cardDht22) cardDht22.style.display = 'block';
                if (cardMq135) cardMq135.style.display = 'none';
            } else if (sensor === 'mq135') {
                if (cardMq135) cardMq135.style.display = 'block';
                if (cardDht22) cardDht22.style.display = 'none';
            }

            scrollToSection('sensor-detail-section');
        }

        function hideSensorDetail(sensor) {
            const cardDht22 = document.getElementById('card-dht22');
            const cardMq135 = document.getElementById('card-mq135');

            if (sensor === 'dht22' && cardDht22) {
                cardDht22.style.display = 'none';
            } else if (sensor === 'mq135' && cardMq135) {
                cardMq135.style.display = 'none';
            }
        }

        function applySidebarState() {
            const collapsed = localStorage.getItem('sidebarCollapsed') === 'true';
            document.body.classList.toggle('sidebar-collapsed', collapsed);
        }

        function toggleSidebar() {
            const collapsed = !(document.body.classList.contains('sidebar-collapsed'));
            document.body.classList.toggle('sidebar-collapsed', collapsed);
            localStorage.setItem('sidebarCollapsed', collapsed);
        }

        function updateThemeButton() {
            const themeLabel = document.getElementById('theme-label');
            const icon = document.querySelector('#theme-toggle i');
            if (!themeLabel || !icon) return;
            if (document.body.classList.contains('dark-mode')) {
                themeLabel.textContent = 'Terang';
                icon.classList.remove('fa-moon');
                icon.classList.add('fa-sun');
            } else {
                themeLabel.textContent = 'Gelap';
                icon.classList.remove('fa-sun');
                icon.classList.add('fa-moon');
            }
        }

        function applyThemeState() {
            const theme = localStorage.getItem('themeMode') || 'light';
            document.body.classList.toggle('dark-mode', theme === 'dark');
            updateThemeButton();
        }

        function toggleTheme() {
            const nextTheme = document.body.classList.contains('dark-mode') ? 'light' : 'dark';
            document.body.classList.toggle('dark-mode', nextTheme === 'dark');
            localStorage.setItem('themeMode', nextTheme);
            updateThemeButton();
        }

        document.addEventListener('DOMContentLoaded', function () {
            applySidebarState();
            applyThemeState();
        });
    </script>
    @stack('scripts')
</body>
</html>
