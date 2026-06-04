@extends('layouts.app')

@section('title', 'Datacheck - Configurações')

@section('content')
<div class="container-admin">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0">
            <i class="bi bi-gear-wide-connected"></i> Configurações
        </h4>
    </div>

    <div class="row">
        <div class="col-md-7">
            <div class="card mb-3">
                <div class="card-header"><strong>Empresa</strong></div>
                <div class="card-body">
                    <form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="row g-4">
                            <div class="col-md-6">
                                <label for="company_name" class="form-label fw-semibold">Nome da Empresa</label>
                                <input type="text"
                                       class="form-control @error('company_name') is-invalid @enderror"
                                       id="company_name"
                                       name="company_name"
                                       value="{{ old('company_name', $setting->company_name) }}"
                                       maxlength="100">
                                <div class="form-text">
                                    Exibido como: <strong>Datacheck - {{ old('company_name', $setting->company_name) }}</strong>
                                </div>
                                @error('company_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="company_icon" class="form-label fw-semibold">Ícone</label>
                                <input type="file"
                                       class="form-control @error('company_icon') is-invalid @enderror"
                                       id="company_icon"
                                       name="company_icon"
                                       accept="image/png,image/jpeg">
                                <div class="form-text">PNG ou JPG. Máx: 2MB.</div>
                                @error('company_icon')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror

                                @if ($setting->company_icon)
                                    <div class="mt-2 d-flex align-items-center gap-3">
                                        <img src="{{ asset('storage/' . $setting->company_icon) }}"
                                             alt="Ícone atual"
                                             style="height: 40px;">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="remove_icon" name="remove_icon" value="1">
                                            <label class="form-check-label text-danger small" for="remove_icon">
                                                Remover ícone atual
                                            </label>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-dc-primary">
                                <i class="bi bi-check-lg"></i> Salvar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-5">
            <div class="card mb-3">
                <div class="card-header"><strong>Licença</strong></div>
                <div class="card-body">
                    <form action="{{ route('admin.settings.update') }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="license_key" class="form-label fw-semibold">Chave de Licença</label>
                            <input type="text"
                                   class="form-control @error('license_key') is-invalid @enderror"
                                   id="license_key"
                                   name="license_key"
                                   value="{{ old('license_key', $setting->license_key) }}"
                                   maxlength="50"
                                   placeholder="Insira a chave fornecida">
                            @error('license_key')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        @if ($licenseInfo)
                            <table class="table table-sm table-bordered mb-0">
                                <tr>
                                    <td>Pacote</td>
                                    <td class="fw-semibold">{{ $licenseInfo['package_name'] }}</td>
                                </tr>
                                <tr>
                                    <td>Usuários</td>
                                    <td>
                                        @if ($licenseInfo['max_users'] === 0)
                                            Ilimitado
                                        @else
                                            {{ $licenseInfo['max_users'] }}
                                            <small class="text-muted">({{ $licenseInfo['user_count'] }} cadastrados)</small>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td>Expira</td>
                                    <td>
                                        @if ($licenseInfo['expires_at'])
                                            {{ $licenseInfo['expires_at']->format('d/m/Y') }}
                                            @if ($licenseInfo['expired'])
                                                <span class="badge bg-danger">Expirada</span>
                                            @elseif ($licenseInfo['days_remaining'] > 0)
                                                <span class="badge bg-success">{{ $licenseInfo['days_remaining'] }} dia(s)</span>
                                            @endif
                                        @else
                                            Vitalícia
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td>Status</td>
                                    <td>
                                        @if ($licenseInfo['valid'] && !$licenseInfo['expired'])
                                            <span class="badge bg-success">Ativa</span>
                                        @else
                                            <span class="badge bg-danger">Inválida</span>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        @elseif ($setting->license_key)
                            <div class="alert alert-warning py-2 mb-0">
                                <i class="bi bi-exclamation-triangle"></i>
                                Não foi possível validar a licença. Verifique a chave ou a conexão com o servidor de licenciamento.
                            </div>
                        @else
                            <p class="text-muted small mb-0">
                                <i class="bi bi-info-circle"></i>
                                Adquira uma licença para ativar todos os recursos do sistema.
                            </p>
                        @endif

                        @if ($setting->license_key)
                            <div class="mt-3">
                                <button type="submit" class="btn btn-dc-primary btn-sm">
                                    <i class="bi bi-check-lg"></i> Salvar Chave
                                </button>
                            </div>
                        @endif
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
