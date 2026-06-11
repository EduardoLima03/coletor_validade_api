@extends("layouts.app")

@section("title", "Editar Área de Auditoria")

@section("content")
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0"><i class="bi bi-pencil"></i> Editar Área de Auditoria</h4>
    <a href="{{ $returnUrl }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Voltar
    </a>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route("admin.areas-auditoria.update", $areaAuditorium->id) }}" method="POST">
            @csrf
            @method("PUT")
            <input type="hidden" name="return_url" value="{{ $returnUrl }}">
            <div class="mb-3">
                <label class="form-label">Lojas</label>
                <div class="row">
                    @php
                        $selectedLojas = old("loja_ids", $areaAuditorium->lojas->pluck('id')->toArray());
                    @endphp
                    @foreach ($lojas as $loja)
                        <div class="col-md-4 col-lg-3 mb-2">
                            <div class="form-check">
                                <input class="form-check-input @error("loja_ids") is-invalid @enderror"
                                       type="checkbox"
                                       name="loja_ids[]"
                                       value="{{ $loja->id }}"
                                       id="loja_{{ $loja->id }}"
                                       {{ in_array($loja->id, $selectedLojas) ? "checked" : "" }}>
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
            <a href="{{ $returnUrl }}" class="btn btn-outline-secondary">Cancelar</a>
        </form>
    </div>
</div>
@endsection