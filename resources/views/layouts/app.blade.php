<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title>@yield('title', 'Datacheck - ' . ($companySetting->company_name ?? 'Medeiros'))</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root {
            --dc-green: #005922;
            --dc-green-light: #A7D02A;
            --dc-gold: #FFD100;
            --dc-red: #F01516;
            --sidebar-width: 240px;
        }
        html { font-size: 14px; }
        @media (min-width: 768px) { html { font-size: 15px; } }
        @media (min-width: 1200px) { html { font-size: 16px; } }
        body {
            background-color: #f4f6f9;
            min-height: 100vh;
        }
        .wrapper {
            display: flex;
            min-height: 100vh;
        }
        .sidebar {
            width: var(--sidebar-width);
            background: linear-gradient(180deg, #005922 0%, #003d17 100%);
            display: flex;
            flex-direction: column;
            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;
            z-index: 100;
            transition: transform 0.3s ease;
        }
        .sidebar-brand {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 1rem 1.25rem;
            color: #fff;
            font-weight: 700;
            font-size: 1.1rem;
            text-decoration: none;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        .sidebar-brand:hover { color: #fff; }
        .sidebar-brand img { height: 28px; }
        .sidebar-nav {
            flex: 1;
            padding: 0.75rem 0;
            overflow-y: auto;
        }
        .sidebar-nav .nav-item { margin: 0; }
        .sidebar-nav .nav-link {
            color: rgba(255,255,255,0.75);
            padding: 0.6rem 1.25rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.9rem;
            transition: all 0.2s;
            text-decoration: none;
        }
        .sidebar-nav .nav-link:hover {
            color: #fff;
            background: rgba(255,255,255,0.1);
        }
        .sidebar-nav .nav-link.active {
            color: #fff;
            background: rgba(167, 208, 42, 0.2);
            border-right: 3px solid #A7D02A;
        }
        .sidebar-nav .nav-link i { width: 20px; text-align: center; }
        .sidebar-nav .nav-section {
            color: rgba(255,255,255,0.4);
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            padding: 1rem 1.25rem 0.25rem;
        }
        .sidebar-footer {
            padding: 0.75rem 1.25rem;
            border-top: 1px solid rgba(255,255,255,0.1);
        }
        .sidebar-footer .user-info {
            color: rgba(255,255,255,0.8);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .sidebar-footer .user-info small {
            color: rgba(255,255,255,0.5);
            display: block;
        }
        .main-content {
            margin-left: var(--sidebar-width);
            flex: 1;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        .topbar {
            background: #fff;
            padding: 0.75rem 1.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 1px 4px rgba(0,0,0,0.08);
            position: sticky;
            top: 0;
            z-index: 99;
        }
        .topbar-toggle {
            display: none;
            background: none;
            border: none;
            font-size: 1.5rem;
            color: #005922;
            cursor: pointer;
        }
        .content-area {
            flex: 1;
            padding: 1.5rem;
        }
        .container-admin { max-width: 1400px; }
        .table > :not(caption) > * > * { padding: 0.5rem 0.5rem; vertical-align: middle; }
        .pagination { margin-bottom: 0; }
        .pagination .page-link { font-size: 0.875rem; padding: 0.375rem 0.625rem; }
        .badge { font-size: 0.8rem; }
        .card { border: none; box-shadow: 0 1px 4px rgba(0,0,0,0.08); }
        .table-dark { --bs-table-bg: #005922; --bs-table-color: #fff; }
        .page-link { color: #005922; }
        .page-item.active .page-link { background-color: #005922; border-color: #005922; }
        .page-link:focus { box-shadow: 0 0 0 0.2rem rgba(0, 89, 34, 0.25); }
        .form-control:focus { border-color: #005922; box-shadow: 0 0 0 0.2rem rgba(0, 89, 34, 0.25); }
        .form-select:focus { border-color: #005922; box-shadow: 0 0 0 0.2rem rgba(0, 89, 34, 0.25); }
        .btn-dc-primary { background-color: #005922; border-color: #005922; color: #fff; }
        .btn-dc-primary:hover { background-color: #003d17; border-color: #003d17; color: #fff; }
        .footer-dc {
            padding: 0.75rem 1.5rem;
            border-top: 1px solid #dee2e6;
            font-size: 0.8rem;
            color: #6c757d;
            background: #fff;
        }
        .footer-dc a { color: #005922; text-decoration: none; }
        .footer-dc a:hover { text-decoration: underline; }

        @media (max-width: 767.98px) {
            .sidebar {
                transform: translateX(-100%);
            }
            .sidebar.show {
                transform: translateX(0);
            }
            .main-content {
                margin-left: 0;
            }
            .topbar-toggle {
                display: block;
            }
            .sidebar-overlay {
                display: none;
                position: fixed;
                inset: 0;
                background: rgba(0,0,0,0.5);
                z-index: 99;
            }
            .sidebar-overlay.show {
                display: block;
            }
        }
    </style>
    @stack('styles')
</head>
<body>
    <div class="wrapper">
        <div class="sidebar-overlay" id="sidebarOverlay"></div>

        <aside class="sidebar" id="sidebar">
            <a class="sidebar-brand" href="{{ route('admin.dashboard') }}">
                @if ($companySetting && $companySetting->company_icon)
                    <img src="{{ asset('storage/' . $companySetting->company_icon) }}" alt="Datacheck">
                @else
                    <img src="{{ asset('favicon.png') }}" alt="Datacheck">
                @endif
                Datacheck - {{ $companySetting->company_name ?? 'Medeiros' }}
            </a>
            <nav class="sidebar-nav">
                @if (in_array(strtoupper(auth()->user()->position ?? ''), ['ADMIN']))
                    <div class="nav-section">Registros</div>
                    <div class="nav-item">
                        <a class="nav-link {{ request()->routeIs('admin.products.*') ? 'active' : '' }}"
                           href="{{ route('admin.products.index') }}">
                            <i class="bi bi-box"></i> Produtos
                        </a>
                    </div>
                    <div class="nav-item">
                        <a class="nav-link {{ request()->routeIs('admin.barcodes.*') ? 'active' : '' }}"
                           href="{{ route('admin.barcodes.index') }}">
                            <i class="bi bi-qr-code"></i> Códigos de Barras
                        </a>
                    </div>
                    <div class="nav-item">
                        <a class="nav-link {{ request()->routeIs('admin.lojas.*') ? 'active' : '' }}"
                           href="{{ route('admin.lojas.index') }}">
                            <i class="bi bi-shop"></i> Lojas
                        </a>
                    </div>
                    <div class="nav-item">
                        <a class="nav-link {{ request()->routeIs('admin.areas-auditoria.*') ? 'active' : '' }}"
                           href="{{ route('admin.areas-auditoria.index') }}">
                            <i class="bi bi-clipboard-check"></i> Áreas de Auditoria
                        </a>
                    </div>
                    <div class="nav-item">
                        <a class="nav-link {{ request()->routeIs('admin.import.*') ? 'active' : '' }}"
                           href="{{ route('admin.import.form') }}">
                            <i class="bi bi-upload"></i> Importar
                        </a>
                    </div>
                @endif
                <div class="nav-section">Operacional</div>
                <div class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}"
                       href="{{ route('admin.dashboard') }}">
                        <i class="bi bi-speedometer2"></i> Dashboard
                    </a>
                </div>
                <div class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.coletas.*') ? 'active' : '' }}"
                       href="{{ route('admin.coletas.index') }}">
                        <i class="bi bi-clipboard-data"></i> Coletas
                    </a>
                </div>
                @if (in_array(strtoupper(auth()->user()->position ?? ''), ['ADMIN']))
        <div class="nav-section">Administração</div>
        <div class="nav-item">
            <a class="nav-link {{ request()->routeIs('admin.importar.coletas.*') ? 'active' : '' }}"
               href="{{ route('admin.importar.coletas.form') }}">
                <i class="bi bi-upload"></i> Importar Coletas
            </a>
        </div>
        <div class="nav-item">
            <a class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}"
               href="{{ route('admin.users.index') }}">
                <i class="bi bi-people"></i> Usuários
            </a>
        </div>
                    <div class="nav-item">
                        <a class="nav-link {{ request()->routeIs('admin.audit.*') ? 'active' : '' }}"
                           href="{{ route('admin.audit.index') }}">
                            <i class="bi bi-journal-text"></i> Auditoria
                        </a>
                    </div>
                    <div class="nav-item">
                        <a class="nav-link {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}"
                           href="{{ route('admin.settings.index') }}">
                            <i class="bi bi-gear-wide-connected"></i> Configurações
                        </a>
                    </div>
                @endif
            </nav>
            <div class="sidebar-footer">
                <div class="user-info">
                    <i class="bi bi-person-circle fs-5"></i>
                    <div>
                        {{ Auth::user()->name }}
                        <small>
                            <span class="badge bg-secondary">{{ strtoupper(Auth::user()->position ?? '') }}</span>
                        </small>
                    </div>
                </div>
                <div class="mt-2 d-flex gap-2">
                    <a href="{{ route('admin.profile.edit') }}" class="btn btn-sm btn-outline-light" title="Perfil">
                        <i class="bi bi-gear"></i>
                    </a>
                    <form action="{{ route('admin.logout') }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-outline-light" title="Sair">
                            <i class="bi bi-box-arrow-right"></i>
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        <div class="main-content">
            <div class="topbar">
                <div>
                    <button class="topbar-toggle" id="sidebarToggle" type="button">
                        <i class="bi bi-list"></i>
                    </button>
                    <span class="fw-semibold d-none d-md-inline">@yield('title', 'Dashboard')</span>
                </div>
                <div>
                    <span class="text-muted small">
                        <i class="bi bi-calendar3"></i> {{ now()->format('d/m/Y') }}
                    </span>
                </div>
            </div>

            <div class="content-area">
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show py-2" role="alert"
                         style="background-color: #e8f5e9; border-color: #005922; color: #003d17;">
                        <i class="bi bi-check-circle"></i> {{ session('success') }}
                        <button type="button" class="btn-close py-2" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible fade show py-2" role="alert"
                         style="background-color: #fde8e8; border-color: #F01516; color: #a00;">
                        <i class="bi bi-exclamation-triangle"></i> {{ session('error') }}
                        <button type="button" class="btn-close py-2" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @yield('content')
            </div>

            <div class="footer-dc">
                <div class="d-flex justify-content-between align-items-center">
                    <span>
                        <i class="bi bi-upc-scan"></i> Datacheck - Medeiros v{{ config('app.version') }}
                    </span>
                    <span>
                        CL Dev
                    </span>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('sidebarToggle')?.addEventListener('click', function() {
            document.getElementById('sidebar')?.classList.toggle('show');
            document.getElementById('sidebarOverlay')?.classList.toggle('show');
        });
        document.getElementById('sidebarOverlay')?.addEventListener('click', function() {
            document.getElementById('sidebar')?.classList.remove('show');
            document.getElementById('sidebarOverlay')?.classList.remove('show');
        });
    </script>
    @stack('scripts')
</body>
</html>
