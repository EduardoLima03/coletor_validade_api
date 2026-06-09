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
                <label class="form-label">Lojas vinculadas</label>
                <div class="row g-2 @error("loja_ids") is-invalid @enderror">
                    @foreach ($lojas as $loja)
                        <div class="col-md-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox"
                                       name="loja_ids[]" value="{{ $loja->id }}"
                                       id="loja_{{ $loja->id }}"
                                       {{ in_array($loja->id, old("loja_ids", $areaAuditorium->lojas->pluck("id")->toArray())) ? "checked" : "" }}>
                                <label class="form-check-label" for="loja_{{ $loja->id }}">
                                    {{ $loja->nome }}
                                </label>
                            </div>
                        </div>
                    @endforeach
                </div>
                @error("loja_ids")
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
                @error("loja_ids.*")
                    <div class="invalid-feedback d-block">{{ $message }}</div>
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
