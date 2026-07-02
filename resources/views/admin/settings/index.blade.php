@extends('layouts.app')

@section('title', 'Datacheck - Configurações')

@section('content')
<div class="container-admin">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0">
            <i class="bi bi-gear-wide-connected"></i> Configurações da Empresa
        </h4>
    </div>

    <div class="card">
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
                            O nome será exibido como: <strong>Datacheck - {{ old('company_name', $setting->company_name) }}</strong>
                        </div>
                        @error('company_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="company_icon" class="form-label fw-semibold">Ícone da Empresa</label>
                        <input type="file"
                               class="form-control @error('company_icon') is-invalid @enderror"
                               id="company_icon"
                               name="company_icon"
                               accept="image/png,image/jpeg">
                        <div class="form-text">Formatos: PNG, JPG. Máximo: 2MB.</div>
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
@endsection
