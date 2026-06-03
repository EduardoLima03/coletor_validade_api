@extends("layouts.app")

@section("title", "Nova Loja")

@section("content")
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0"><i class="bi bi-plus-lg"></i> Nova Loja</h4>
    <a href="{{ route("admin.lojas.index") }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Voltar
    </a>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route("admin.lojas.store") }}" method="POST">
            @csrf
            <div class="mb-3">
                <label for="nome" class="form-label">Nome da Loja</label>
                <input type="text"
                       class="form-control @error("nome") is-invalid @enderror"
                       id="nome"
                       name="nome"
                       value="{{ old("nome") }}"
                       required>
                @error("nome")
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <button type="submit" class="btn btn-success">
                <i class="bi bi-check-lg"></i> Salvar
            </button>
            <a href="{{ route("admin.lojas.index") }}" class="btn btn-outline-secondary">Cancelar</a>
        </form>
    </div>
</div>
@endsection
