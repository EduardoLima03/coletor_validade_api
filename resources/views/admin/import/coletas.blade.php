@extends("layouts.app")

@section("title", "Importar Coletas")

@section("content")
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0"><i class="bi bi-upload"></i> Importar Coletas de Arquivo</h4>
    <a href="{{ route("admin.dashboard") }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Voltar
    </a>
</div>

@if (session("success"))
    <div class="alert alert-success alert-dismissible fade show">
        {!! session("success") !!}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if (session("warning"))
    <div class="alert alert-warning alert-dismissible fade show">
        {!! session("warning") !!}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if (session("error"))
    <div class="alert alert-danger alert-dismissible fade show">
        {!! session("error") !!}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if ($errors->any())
    <div class="alert alert-danger alert-dismissible fade show">
        <ul class="mb-0">
            @foreach ($errors->all() as $erro)
                <li>{{ $erro }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="row">
    <div class="col-md-7">
        <div class="card">
            <div class="card-header"><strong>Importar CSV de Coletas</strong></div>
            <div class="card-body">
                <form method="POST" action="{{ route("admin.importar.coletas.processar") }}"
                      enctype="multipart/form-data">
                    @csrf

                    <div class="mb-3">
                        <label for="arquivo" class="form-label">Arquivo CSV</label>
                        <input type="file"
                               class="form-control @error("arquivo") is-invalid @enderror"
                               id="arquivo"
                               name="arquivo"
                               accept=".csv,.txt"
                               required>
                        @error("arquivo")
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Máximo 100 MB. Formatos aceitos: CSV, TXT.</div>
                    </div>

                    <div class="mb-3">
                        <label for="loja_id" class="form-label">Loja destino</label>
                        <select class="form-select @error("loja_id") is-invalid @enderror"
                                id="loja_id" name="loja_id" required>
                            <option value="">Selecione a loja...</option>
                            @foreach ($lojas as $loja)
                                <option value="{{ $loja->id }}" {{ old("loja_id") == $loja->id ? "selected" : "" }}>
                                    {{ $loja->nome }}
                                </option>
                            @endforeach
                        </select>
                        @error("loja_id")
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <button type="submit" class="btn btn-primary btn-lg" id="btnImportar"
                            onclick="this.disabled=true; this.innerHTML='<span class=\'spinner-border spinner-border-sm\'></span> Importando...'; this.form.submit();">
                        <i class="bi bi-play-fill"></i> Importar
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-5">
        <div class="card">
            <div class="card-header"><strong>Formato esperado</strong></div>
            <div class="card-body">
                <p class="text-muted mb-2">O CSV deve ter as colunas nesta ordem (separador: vírgula):</p>
                <table class="table table-sm table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Coluna</th>
                            <th>Exemplo</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr><td>1</td><td>Data/Hora</td><td>28/01/2025 08:54</td></tr>
                        <tr><td>2</td><td>Auditor</td><td>Marques</td></tr>
                        <tr><td>3</td><td>Setor auditado</td><td>Recebimento</td></tr>
                        <tr><td>4</td><td>Descrição</td><td>RACAO FRISKIES AD 20KG</td></tr>
                        <tr><td>5</td><td>Código/EAN</td><td>7891000116814</td></tr>
                        <tr><td>6</td><td>Quantidade</td><td>2</td></tr>
                        <tr><td>7</td><td>Validade</td><td>01/02/2026</td></tr>
                    </tbody>
                </table>
                <p class="text-muted small mb-1">
                    <i class="bi bi-info-circle"></i>
                    Colunas extras após a 7ª são ignoradas.
                </p>
                <p class="text-muted small mb-1">
                    <i class="bi bi-info-circle"></i>
                    Setores não encontrados são criados automaticamente.
                </p>
                <p class="text-muted small mb-0">
                    <i class="bi bi-info-circle"></i>
                    Coletas com mesmo EAN + setor + validade são atualizadas.
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
