@extends("layouts.app")

@section("title", "Editar Licença")

@section("content")
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0"><i class="bi bi-pencil"></i> Editar Licença — {{ $license->client_name }}</h4>
    <a href="{{ route("admin.licenses.index") }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left"></i> Voltar
    </a>
</div>

<div class="card">
    <div class="card-body">
        <div class="alert alert-info">
            <strong>Chave da licença:</strong> <code>{{ $license->license_key }}</code>
        </div>

        <form action="{{ route("admin.licenses.update", $license->id) }}" method="POST">
            @csrf
            @method("PUT")

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Cliente</label>
                    <input type="text" name="client_name"
                           class="form-control @error("client_name") is-invalid @enderror"
                           value="{{ old("client_name", $license->client_name) }}" required>
                    @error("client_name") <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">CNPJ</label>
                    <input type="text" name="cnpj"
                           class="form-control @error("cnpj") is-invalid @enderror"
                           value="{{ old("cnpj", $license->cnpj) }}" required>
                    @error("cnpj") <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>

            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Plano</label>
                    <select name="plan" class="form-select @error("plan") is-invalid @enderror" required>
                        <option value="basic" {{ old("plan", $license->plan) === "basic" ? "selected" : "" }}>Basic</option>
                        <option value="pro" {{ old("plan", $license->plan) === "pro" ? "selected" : "" }}>Pro</option>
                        <option value="enterprise" {{ old("plan", $license->plan) === "enterprise" ? "selected" : "" }}>Enterprise</option>
                    </select>
                    @error("plan") <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Máx. Lojas</label>
                    <input type="number" name="max_stores"
                           class="form-control @error("max_stores") is-invalid @enderror"
                           value="{{ old("max_stores", $license->max_stores) }}" min="1" required>
                    @error("max_stores") <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Máx. Usuários</label>
                    <input type="number" name="max_users"
                           class="form-control @error("max_users") is-invalid @enderror"
                           value="{{ old("max_users", $license->max_users) }}" min="1" required>
                    @error("max_users") <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>

            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Válido de</label>
                    <input type="date" name="valid_from"
                           class="form-control @error("valid_from") is-invalid @enderror"
                           value="{{ old("valid_from", $license->valid_from->format("Y-m-d")) }}" required>
                    @error("valid_from") <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Válido até</label>
                    <input type="date" name="valid_until"
                           class="form-control @error("valid_until") is-invalid @enderror"
                           value="{{ old("valid_until", $license->valid_until->format("Y-m-d")) }}" required>
                    @error("valid_until") <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-4 mb-3 d-flex align-items-end">
                    <div class="form-check">
                        <input type="checkbox" name="active" class="form-check-input" id="activeCheck"
                               value="1" {{ old("active", $license->active) ? "checked" : "" }}>
                        <label class="form-check-label" for="activeCheck">Ativa</label>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary">
                <i class="bi bi-check-lg"></i> Atualizar
            </button>
        </form>
    </div>
</div>
@endsection
