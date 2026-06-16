@extends("layouts.app")

@section("title", "Editar Coleta")

@section("content")
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0"><i class="bi bi-pencil"></i> Editar Coleta #{{ $coleta->id }}</h4>
    <a href="{{ $returnUrl }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Voltar
    </a>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route("admin.coletas.update", $coleta->id) }}" method="POST">
            @csrf
            @method("PUT")
            <input type="hidden" name="return_url" value="{{ $returnUrl }}">

            <div class="row">
                <div class="col-md-3 mb-3">
                    <label class="form-label">Loja</label>
                    <input type="text" class="form-control" value="{{ $coleta->loja->nome ?? "-" }}" readonly>
                </div>
                <div class="col-md-3 mb-3">
                    <label for="area_auditoria_id" class="form-label">Setor (Área de Auditoria)</label>
                    <select class="form-select @error("area_auditoria_id") is-invalid @enderror"
                            id="area_auditoria_id"
                            name="area_auditoria_id">
                        <option value="">Selecione...</option>
                        @foreach ($areasAuditoria as $area)
                            <option value="{{ $area->id }}" {{ old("area_auditoria_id", $coleta->area_auditoria_id) == $area->id ? "selected" : "" }}>
                                {{ $area->nome }}
                            </option>
                        @endforeach
                    </select>
                    @error("area_auditoria_id")
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Auditor</label>
                    <input type="text" class="form-control" value="{{ $coleta->user->name ?? "-" }}" readonly>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">EAN</label>
                    <input type="text" class="form-control" value="{{ $coleta->ean }}" readonly>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12 mb-3">
                    <label class="form-label">Descricao</label>
                    <input type="text" class="form-control" value="{{ $coleta->productName }}" readonly>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4 mb-3">
                    <label for="quantidade" class="form-label">Quantidade</label>
                    <input type="text"
                           class="form-control @error("quantidade") is-invalid @enderror"
                           id="quantidade"
                           name="quantidade"
                           value="{{ old("quantidade", $coleta->quantidade) }}"
                           required>
                    @error("quantidade")
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-2 mb-3">
                    <label for="unidade" class="form-label">Unidade</label>
                    <select class="form-select @error("unidade") is-invalid @enderror" id="unidade" name="unidade">
                        @foreach (["un", "kg", "cx"] as $u)
                            <option value="{{ $u }}" {{ old("unidade", $coleta->unidade ?? "un") == $u ? "selected" : "" }}>
                                {{ $u }}
                            </option>
                        @endforeach
                    </select>
                    @error("unidade")
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-4 mb-3">
                    <label for="data_validade" class="form-label">Data de Validade</label>
                    <input type="date"
                           class="form-control @error("data_validade") is-invalid @enderror"
                           id="data_validade"
                           name="data_validade"
                           value="{{ old("data_validade", $coleta->data_validade->format("Y-m-d")) }}"
                           required>
                    @error("data_validade")
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Dias a Vencer</label>
                    <input type="text" class="form-control"
                           value="{{ $coleta->dias_a_vencer }} dias"
                           readonly
                           style="font-weight: bold;">
                </div>
            </div>

            <button type="submit" class="btn btn-primary">
                <i class="bi bi-check-lg"></i> Atualizar
            </button>
            <a href="{{ $returnUrl }}" class="btn btn-outline-secondary">Cancelar</a>
        </form>
    </div>
</div>
@endsection