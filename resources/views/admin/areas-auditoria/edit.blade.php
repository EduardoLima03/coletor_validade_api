@extends("layouts.app")

@section("title", "Editar Área de Auditoria")

@section("content")
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0"><i class="bi bi-pencil"></i> Editar Área de Auditoria</h4>
    <a href="{{ route("admin.areas-auditoria.index") }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Voltar
    </a>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route("admin.areas-auditoria.update", $areaAuditorium->id) }}" method="POST">
            @csrf
            @method("PUT")
            <div class="mb-3">
                <label for="loja_id" class="form-label">Loja</label>
                <select class="form-control @error("loja_id") is-invalid @enderror"
                        id="loja_id" name="loja_id" required>
                    <option value="">Selecione a loja</option>
                    @foreach ($lojas as $loja)
                        <option value="{{ $loja->id }}"
                            {{ old("loja_id", $areaAuditorium->loja_id) == $loja->id ? "selected" : "" }}>
                            {{ $loja->nome }}
                        </option>
                    @endforeach
                </select>
                @error("loja_id")
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="mb-3">
                <label for="nome" class="form-label">Nome da Área</label>
                <input type="text"
                       class="form-control @error("nome") is-invalid @enderror"
                       id="nome"
                       name="nome"
                       value="{{ old("nome", $areaAuditorium->nome) }}"
                       required>
                @error("nome")
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="mb-3">
                <label for="descricao" class="form-label">Descrição</label>
                <textarea class="form-control @error("descricao") is-invalid @enderror"
                          id="descricao"
                          name="descricao"
                          rows="3">{{ old("descricao", $areaAuditorium->descricao) }}</textarea>
                @error("descricao")
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-check-lg"></i> Atualizar
            </button>
            <a href="{{ route("admin.areas-auditoria.index") }}" class="btn btn-outline-secondary">Cancelar</a>
        </form>
    </div>
</div>
@endsection
