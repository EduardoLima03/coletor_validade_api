<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title>@yield('title', 'Datacheck - Medeiros')</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root {
            --dc-green: #005922;
            --dc-green-light: #A7D02A;
            --dc-gold: #FFD100;
            --dc-red: #F01516;
            --bs-primary: #005922;
            --bs-primary-rgb: 0, 89, 34;
            --bs-secondary: #A7D02A;
            --bs-secondary-rgb: 167, 208, 42;
            --bs-warning: #FFD100;
            --bs-warning-rgb: 255, 209, 0;
            --bs-danger: #F01516;
            --bs-danger-rgb: 240, 21, 22;
            --bs-success: #005922;
            --bs-success-rgb: 0, 89, 34;
        }
        html { font-size: 14px; }
        @media (min-width: 768px) { html { font-size: 15px; } }
        @media (min-width: 1200px) { html { font-size: 16px; } }
        body { background-color: #f4f6f9; }
        .container-admin { max-width: 1400px; }
        .table > :not(caption) > * > * { padding: 0.5rem 0.5rem; vertical-align: middle; }
        .pagination { margin-bottom: 0; }
        .pagination .page-link { font-size: 0.875rem; padding: 0.375rem 0.625rem; }
        .badge { font-size: 0.8rem; }
        .navbar-dc {
            background: linear-gradient(135deg, #005922 0%, #003d17 100%);
            box-shadow: 0 2px 8px rgba(0,0,0,0.15);
        }
        .navbar-dc .navbar-brand {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 700;
            letter-spacing: 0.5px;
        }
        .navbar-dc .navbar-brand img { height: 28px; }
        .navbar-dc .nav-link.active {
            background: rgba(167, 208, 42, 0.2);
            border-radius: 0.25rem;
        }
        .btn-dc-primary {
            background-color: #005922;
            border-color: #005922;
            color: #fff;
        }
        .btn-dc-primary:hover {
            background-color: #003d17;
            border-color: #003d17;
            color: #fff;
        }
        .btn-dc-outline {
            border-color: #005922;
            color: #005922;
        }
        .btn-dc-outline:hover {
            background-color: #005922;
            color: #fff;
        }
        .footer-dc {
            margin-top: 2rem;
            padding: 0.75rem 0;
            border-top: 1px solid #dee2e6;
            font-size: 0.8rem;
            color: #6c757d;
        }
        .footer-dc a { color: #005922; text-decoration: none; }
        .footer-dc a:hover { text-decoration: underline; }
        .card { border: none; box-shadow: 0 1px 4px rgba(0,0,0,0.08); }
        .table-dark { --bs-table-bg: #005922; --bs-table-color: #fff; }
        .page-link { color: #005922; }
        .page-item.active .page-link { background-color: #005922; border-color: #005922; }
        .page-link:focus { box-shadow: 0 0 0 0.2rem rgba(0, 89, 34, 0.25); }
        .form-control:focus { border-color: #005922; box-shadow: 0 0 0 0.2rem rgba(0, 89, 34, 0.25); }
        .form-select:focus { border-color: #005922; box-shadow: 0 0 0 0.2rem rgba(0, 89, 34, 0.25); }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark navbar-dc mb-3 mb-md-4">
        <div class="container container-admin">
            <a class="navbar-brand" href="{{ route('admin.products.index') }}">
                <img src="{{ asset('favicon.png') }}" alt="Datacheck">
                Datacheck - Medeiros
            </a>
            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('admin.products.*') ? 'active' : '' }}"
                           href="{{ route('admin.products.index') }}">
                            <i class="bi bi-box"></i> Produtos
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('admin.barcodes.*') ? 'active' : '' }}"
                           href="{{ route('admin.barcodes.index') }}">
                            <i class="bi bi-qr-code"></i> Códigos de Barras
                        </a>
                    </li>
                    @if (in_array(strtoupper(auth()->user()->position ?? ''), ['ADMIN']))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}"
                               href="{{ route('admin.users.index') }}">
                                <i class="bi bi-people"></i> Usuários
                            </a>
                        </li>
                    @endif
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle"></i> {{ Auth::user()->name }}
                            <span class="badge bg-secondary ms-1">{{ strtoupper(Auth::user()->position ?? '') }}</span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li>
                                <a class="dropdown-item" href="{{ route('admin.profile.edit') }}">
                                    <i class="bi bi-person-gear"></i> Meu Perfil
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form action="{{ route('admin.logout') }}" method="POST">
                                    @csrf
                                    <button type="submit" class="dropdown-item">
                                        <i class="bi bi-box-arrow-right"></i> Sair
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container container-admin">
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

    <div class="container container-admin footer-dc">
        <div class="d-flex justify-content-between align-items-center">
            <span>
                <i class="bi bi-upc-scan"></i> Datacheck - Medeiros v1.0.0
            </span>
            <span>
                Desenvolvido por
                <a href="https://github.com/EduardoLima03" target="_blank" rel="noopener">
                    CL Dev <i class="bi bi-box-arrow-up-right"></i>
                </a>
            </span>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
