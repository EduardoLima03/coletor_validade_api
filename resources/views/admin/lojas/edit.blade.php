@extends("layouts.app")

@section("title", "Editar Loja")

@section("content")
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0"><i class="bi bi-pencil"></i> Editar Loja</h4>
    <a href="{{ $returnUrl }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Voltar
    </a>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route("admin.lojas.update", $loja->id) }}" method="POST">
            @csrf
            @method("PUT")
            <input type="hidden" name="return_url" value="{{ $returnUrl }}">
            <div class="mb-3">
                <label for="nome" class="form-label">Nome da Loja</label>
                <input type="text"
                       class="form-control @error("nome") is-invalid @enderror"
                       id="nome"
                       name="nome"
                       value="{{ old("nome", $loja->nome) }}"
                       required>
                @error("nome")
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-check-lg"></i> Atualizar
            </button>
            <a href="{{ $returnUrl }}" class="btn btn-outline-secondary">Cancelar</a>
        </form>
    </div>
</div>
@endsection
