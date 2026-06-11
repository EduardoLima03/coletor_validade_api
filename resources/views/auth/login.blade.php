<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title>Login - Datacheck Medeiros</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #003d17 0%, #005922 40%, #1a7a3a 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
        .card {
            border: none;
            border-radius: 1rem;
            box-shadow: 0 0.5rem 2rem rgba(0, 0, 0, 0.3);
        }
        .card-header {
            background: linear-gradient(135deg, #005922, #003d17);
            color: white;
            border-radius: 1rem 1rem 0 0 !important;
            text-align: center;
            padding: 1.5rem;
        }
        .card-header img {
            height: 40px;
            margin-bottom: 0.5rem;
        }
        .card-header i {
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
        }
        .btn-dc {
            background: linear-gradient(135deg, #005922, #003d17);
            border: none;
            color: white;
        }
        .btn-dc:hover {
            background: linear-gradient(135deg, #003d17, #002b10);
            color: white;
        }
        .footer-dc {
            text-align: center;
            margin-top: 1rem;
            font-size: 0.8rem;
            color: rgba(255,255,255,0.6);
        }
        .footer-dc a {
            color: #FFD100;
            text-decoration: none;
        }
        .footer-dc a:hover { text-decoration: underline; }
        .form-control:focus {
            border-color: #005922;
            box-shadow: 0 0 0 0.2rem rgba(0, 89, 34, 0.25);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="card">
                    <div class="card-header">
                        <img src="{{ asset('favicon.png') }}" alt="Datacheck">
                        <h4 class="mb-0">Datacheck - Medeiros</h4>
                        <small>Faça login para continuar</small>
                    </div>
                    <div class="card-body p-4">
                        <form method="POST" action="{{ route('admin.login') }}">
                            @csrf

                            <div class="mb-3">
                                <label for="email" class="form-label">E-mail</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                    <input type="email"
                                           class="form-control @error('email') is-invalid @enderror"
                                           id="email"
                                           name="email"
                                           value="{{ old('email') }}"
                                           required
                                           autofocus>
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label">Senha</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                    <input type="password"
                                           class="form-control @error('password') is-invalid @enderror"
                                           id="password"
                                           name="password"
                                           required>
                                    @error('password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <button type="submit" class="btn btn-dc w-100 btn-lg">
                                <i class="bi bi-box-arrow-in-right"></i> Entrar
                            </button>
                        </form>
                    </div>
                </div>
                <div class="footer-dc">
                    Datacheck - Medeiros v{{ config('app.version') }} &mdash;
                    Desenvolvido por
                    <a href="https://github.com/EduardoLima03" target="_blank" rel="noopener">
                        CL Dev <i class="bi bi-box-arrow-up-right"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
