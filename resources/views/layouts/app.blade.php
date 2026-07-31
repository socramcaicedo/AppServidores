<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/jpeg" href="{{ asset('images/LOGO3.jpeg') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/LOGO3.jpeg') }}">
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <meta name="theme-color" content="#0D2F6E">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="App IPUC">
    <meta name="application-name" content="App IPUC">
    <title>@yield('titulo', 'Gestión de Servidores')</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        :root {
            --azul-oscuro:   #0D2F6E;
            --azul-medio:    #1A4FA8;
            --azul-claro:    #E8F0FB;
            --amarillo:      #F5C518;
            --amarillo-suave:#FFF8DC;
            --blanco:        #FFFFFF;
            --gris-claro:    #F4F6FA;
            --gris-texto:    #555E6D;
            --borde:         #D1DCF0;
            --rojo:          #C0392B;
            --verde:         #1A7A4A;
        }

        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: var(--gris-claro);
            color: #1a1a2e;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* ── Navbar ── */
        .navbar {
            background: var(--azul-oscuro);
            padding: 0 2rem;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 3px solid var(--amarillo);
        }

        .navbar-brand {
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
        }

        .navbar-brand .icono-iglesia {
            width: 34px;
            height: 34px;
            background: var(--amarillo);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
        }

        .navbar-brand .logo-ipuc {
            width: 45px;
            height: 45px;
            object-fit: cover;
            border-radius: 50%; /* ← Hace que sea circular */
            border: 2px solid var(--amarillo); /* ← Borde amarillo opcional */
            box-shadow: 0 2px 4px rgba(0,0,0,0.1); /* ← Sombra suave */
        }

        .navbar-brand span {
            color: var(--blanco);
            font-size: 16px;
            font-weight: 600;
            letter-spacing: 0.3px;
        }

        .navbar-user {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .badge-rol {
            background: var(--amarillo);
            color: var(--azul-oscuro);
            font-size: 11px;
            font-weight: 700;
            padding: 3px 10px;
            border-radius: 20px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .navbar-user span {
            color: #cbd5e8;
            font-size: 14px;
        }

        .btn-logout {
            background: transparent;
            border: 1px solid rgba(255,255,255,0.3);
            color: var(--blanco);
            padding: 5px 14px;
            border-radius: 6px;
            font-size: 13px;
            cursor: pointer;
            text-decoration: none;
            transition: background 0.2s;
        }

        .btn-logout:hover { background: rgba(255,255,255,0.1); }

        /* ── Sidebar ── */
        .layout {
            display: flex;
            flex: 1;
        }

        .sidebar {
            width: 230px;
            background: var(--blanco);
            border-right: 1px solid var(--borde);
            padding: 1.5rem 0;
            min-height: calc(100vh - 60px);
        }

        .sidebar-section {
            padding: 0 1rem 1rem;
            margin-bottom: 0.5rem;
        }

        .sidebar-title {
            font-size: 10px;
            font-weight: 700;
            color: var(--gris-texto);
            text-transform: uppercase;
            letter-spacing: 1px;
            padding: 0 0.75rem;
            margin-bottom: 6px;
        }

        .sidebar a {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 9px 12px;
            border-radius: 7px;
            color: #3a4255;
            text-decoration: none;
            font-size: 14px;
            transition: background 0.15s, color 0.15s;
            margin-bottom: 2px;
        }

        .sidebar a:hover {
            background: var(--azul-claro);
            color: var(--azul-medio);
        }

        .sidebar a.activo {
            background: var(--azul-claro);
            color: var(--azul-medio);
            font-weight: 600;
            border-left: 3px solid var(--azul-medio);
        }

        .sidebar .icono {
            font-size: 16px;
            width: 20px;
            text-align: center;
        }

        /* ── Contenido principal ── */
        .main {
            flex: 1;
            padding: 2rem;
        }

        /* Vista de estadísticas - más compacta */
        .main.estadisticas-view {
            padding: 1.5rem;
        }

        @media (max-width: 768px) {
            .main.estadisticas-view {
                padding: 1rem;
            }
        }

        .page-header {
            margin-bottom: 1.75rem;
        }

        .page-header h1 {
            font-size: 22px;
            font-weight: 600;
            color: var(--azul-oscuro);
        }

        .page-header p {
            color: var(--gris-texto);
            font-size: 14px;
            margin-top: 4px;
        }

        /* ── Alertas ── */
        .alert {
            padding: 12px 16px;
            border-radius: 8px;
            font-size: 14px;
            margin-bottom: 1.5rem;
            border-left: 4px solid;
        }

        .alert-error   { background: #fdf0ef; border-color: var(--rojo); color: var(--rojo); }
        .alert-success { background: #edfaf3; border-color: var(--verde); color: var(--verde); }
        .alert-info    { background: var(--azul-claro); border-color: var(--azul-medio); color: var(--azul-oscuro); }

        /* ── Cards de estadísticas ── */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 16px;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: var(--blanco);
            border: 1px solid var(--borde);
            border-radius: 10px;
            padding: 1.25rem;
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .stat-card .stat-label {
            font-size: 12px;
            color: var(--gris-texto);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 600;
        }

        .stat-card .stat-valor {
            font-size: 28px;
            font-weight: 700;
            color: var(--azul-oscuro);
        }

        .stat-card .stat-sub {
            font-size: 12px;
            color: var(--gris-texto);
        }

        .stat-card.amarillo { border-top: 3px solid var(--amarillo); }
        .stat-card.azul     { border-top: 3px solid var(--azul-medio); }
        .stat-card.verde    { border-top: 3px solid var(--verde); }
        .stat-card.rojo     { border-top: 3px solid var(--rojo); }

        /* ── Botones ── */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 18px;
            border-radius: 7px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            text-decoration: none;
            border: none;
            transition: opacity 0.15s, transform 0.1s;
        }

        .btn:hover   { opacity: 0.88; }
        .btn:active  { transform: scale(0.98); }

        .btn-primario   { background: var(--azul-medio); color: var(--blanco); }
        .btn-amarillo   { background: var(--amarillo); color: var(--azul-oscuro); font-weight: 700; }
        .btn-secundario { background: var(--gris-claro); color: #3a4255; border: 1px solid var(--borde); }
        .btn-peligro    { background: var(--rojo); color: var(--blanco); }

        /* ── Tabla ── */
        .tabla-wrapper {
            background: var(--blanco);
            border: 1px solid var(--borde);
            border-radius: 10px;
            overflow: hidden;
        }

        .tabla-header {
            padding: 1rem 1.25rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid var(--borde);
        }

        .tabla-header h2 {
            font-size: 15px;
            font-weight: 600;
            color: var(--azul-oscuro);
        }

        table { width: 100%; border-collapse: collapse; }

        th {
            background: var(--azul-claro);
            color: var(--azul-oscuro);
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 10px 16px;
            text-align: left;
        }

        td {
            padding: 11px 16px;
            font-size: 14px;
            border-bottom: 1px solid var(--borde);
            color: #2a2a3e;
        }

        tr:last-child td { border-bottom: none; }
        tr:hover td { background: var(--gris-claro); }

        /* ── Pill de estado ── */
        .pill {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
        }

        .pill-activo   { background: #edfaf3; color: var(--verde); }
        .pill-inactivo { background: #fdf0ef; color: var(--rojo); }
        .pill-pendiente { background: var(--amarillo-suave); color: #8a6200; }
    </style>

    <!-- CSS Responsivo para móviles y tablets -->
    <link href="{{ asset('css/responsive.css') }}" rel="stylesheet">
</head>
<body>

<nav class="navbar">
    <button class="hamburger-btn" id="hamburger-btn" aria-label="Abrir menú">
        <span></span>
        <span></span>
        <span></span>
    </button>
    <a href="{{ route('dashboard') }}" class="navbar-brand">
        <img src="{{ asset('images/LOGO3.jpeg') }}"
             alt="Logo IPUC"
             class="logo-ipuc">
        <span class="hide-mobile">Gestión de Servidores</span>
    </a>
    <div class="navbar-user">
        <span class="hide-mobile">{{ auth()->user()->nombre_completo }}</span>
        <span class="badge-rol">{{ auth()->user()->rol->nombre_rol ?? 'Sin rol' }}</span>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn-logout hide-mobile">Cerrar sesión</button>
            <button type="submit" class="btn-logout show-mobile" aria-label="Cerrar sesión">
                &#10005;
            </button>
        </form>
    </div>
</nav>

<!-- Overlay oscuro para móvil -->
<div class="sidebar-overlay" id="sidebar-overlay"></div>

<div class="layout">
    <aside class="sidebar">
        @yield('sidebar')
    </aside>

    <main class="main @yield('main-class')">
        @if(session('error'))
            <div class="alert alert-error">{{ session('error') }}</div>
        @endif
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @yield('contenido')
    </main>
</div>

<script>
    // ============================================
    // MENÚ HAMBURGUESA PARA MÓVIL
    // ============================================
    document.addEventListener('DOMContentLoaded', function() {
        const hamburgerBtn = document.getElementById('hamburger-btn');
        const sidebar = document.querySelector('.sidebar');
        const overlay = document.getElementById('sidebar-overlay');

        // Abrir/cerrar menú
        function toggleMenu() {
            sidebar.classList.toggle('active');
            overlay.classList.toggle('active');
            hamburgerBtn.classList.toggle('active');
            document.body.style.overflow = sidebar.classList.contains('active') ? 'hidden' : '';
        }

        // Event listeners
        if (hamburgerBtn) {
            hamburgerBtn.addEventListener('click', toggleMenu);
        }

        if (overlay) {
            overlay.addEventListener('click', toggleMenu);
        }

        // Cerrar menú al hacer click en un enlace
        const sidebarLinks = document.querySelectorAll('.sidebar a');
        sidebarLinks.forEach(link => {
            link.addEventListener('click', function() {
                if (window.innerWidth < 768) {
                    toggleMenu();
                }
            });
        });

        // Cerrar menú al cambiar tamaño de pantalla
        window.addEventListener('resize', function() {
            if (window.innerWidth >= 768) {
                sidebar.classList.remove('active');
                overlay.classList.remove('active');
                hamburgerBtn.classList.remove('active');
                document.body.style.overflow = '';
            }
        });

        // Cerrar menú con tecla Escape
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && sidebar.classList.contains('active')) {
                toggleMenu();
            }
        });

        // ============================================
        // RESTAURAR POSICIÓN DEL SCROLL AL NAVEGAR
        // ============================================
        const sidebarLinksList = document.querySelectorAll('.sidebar a');
        sidebarLinksList.forEach(link => {
            link.addEventListener('click', function(e) {
                // No prevenir navegación, solo guardar posición
                sessionStorage.setItem('scrollPosition', window.pageYOffset);
            });
        });

        // Restaurar scroll si existe
        if (sessionStorage.getItem('scrollPosition')) {
            window.scrollTo(0, parseInt(sessionStorage.getItem('scrollPosition')));
            sessionStorage.removeItem('scrollPosition');
        }

        // ============================================
        // AJUSTAR TABLAS EN MÓVIL (OPCIONAL)
        // ============================================
        // Descomenta si quieres que las tablas se conviertan en cards en móvil
        /*
        function adjustTablesForMobile() {
            if (window.innerWidth < 576) {
                document.querySelectorAll('.tabla-wrapper table').forEach(table => {
                    table.classList.add('table-cards-mobile');
                    table.querySelectorAll('td').forEach(td => {
                        const th = table.querySelector('th');
                        if (th) {
                            td.setAttribute('data-label', th.textContent);
                        }
                    });
                });
            } else {
                document.querySelectorAll('.table-cards-mobile').forEach(table => {
                    table.classList.remove('table-cards-mobile');
                });
            }
        }

        adjustTablesForMobile();
        window.addEventListener('resize', adjustTablesForMobile);
        */
    });
</script>

<script>
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', function () {
            navigator.serviceWorker.register('/sw.js').catch(function () {});
        });
    }
</script>

</body>
</html>