@extends("layouts.app")

@section("title", "Nova Licença")

@section("content")
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0"><i class="bi bi-plus-lg"></i> Nova Licença</h4>
    <a href="{{ route("admin.licenses.index") }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left"></i> Voltar
    </a>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route("admin.licenses.store") }}" method="POST">
            @csrf

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Cliente</label>
                    <input type="text" name="client_name" class="form-control @error("client_name") is-invalid @enderror"
                           value="{{ old("client_name") }}" required>
                    @error("client_name") <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">CNPJ</label>
                    <input type="text" name="cnpj" class="form-control @error("cnpj") is-invalid @enderror"
                           value="{{ old("cnpj") }}" placeholder="00.000.000/0000-00" required>
                    @error("cnpj") <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>

            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Plano</label>
                    <select name="plan" class="form-select @error("plan") is-invalid @enderror" required>
                        <option value="basic" {{ old("plan") === "basic" ? "selected" : "" }}>Basic</option>
                        <option value="pro" {{ old("plan") === "pro" ? "selected" : "" }}>Pro</option>
                        <option value="enterprise" {{ old("plan") === "enterprise" ? "selected" : "" }}>Enterprise</option>
                    </select>
                    @error("plan") <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Máx. Lojas</label>
                    <input type="number" name="max_stores" class="form-control @error("max_stores") is-invalid @enderror"
                           value="{{ old("max_stores", 1) }}" min="1" required>
                    @error("max_stores") <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Máx. Usuários</label>
                    <input type="number" name="max_users" class="form-control @error("max_users") is-invalid @enderror"
                           value="{{ old("max_users", 1) }}" min="1" required>
                    @error("max_users") <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Válido de</label>
                    <input type="date" name="valid_from" class="form-control @error("valid_from") is-invalid @enderror"
                           value="{{ old("valid_from", date("Y-m-d")) }}" required>
                    @error("valid_from") <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Válido até</label>
                    <input type="date" name="valid_until" class="form-control @error("valid_until") is-invalid @enderror"
                           value="{{ old("valid_until") }}" required>
                    @error("valid_until") <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>

            <button type="submit" class="btn btn-primary">
                <i class="bi bi-check-lg"></i> Criar Licença
            </button>
        </form>
    </div>
</div>
@endsection
