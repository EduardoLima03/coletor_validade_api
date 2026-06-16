@extends("layouts.app")

@section("title", "Importar Coletas")

@section("content")
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0"><i class="bi bi-upload"></i> Importar Coletas de Arquivo</h4>
    <a href="{{ route("admin.dashboard") }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Voltar
    </a>
</div>

<div class="row">
    <div class="col-md-7">
        <div class="card">
            <div class="card-header"><strong>Importar CSV de Coletas</strong></div>
            <div class="card-body">
                <form id="import-form">
                    @csrf

                    <div class="mb-3">
                        <label for="csv_file" class="form-label">Arquivo CSV</label>
                        <input type="file"
                               class="form-control @error("csv_file") is-invalid @enderror"
                               id="csv_file"
                               name="csv_file"
                               accept=".csv,.txt"
                               required>
                        @error("csv_file")
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

                    <button type="button" class="btn btn-primary btn-lg" id="btnImportar" onclick="startImport()">
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

<div id="import-overlay" class="import-overlay d-none">
    <div class="import-overlay-content">
        <div class="mb-3">
            <div class="spinner-border text-light" role="status" style="width: 3rem; height: 3rem;">
                <span class="visually-hidden">Processando...</span>
            </div>
        </div>
        <h4 class="text-light mb-2" id="overlay-title">Importando...</h4>
        <p class="text-light mb-2" id="overlay-status">Iniciando processamento...</p>
        <div class="progress w-75 mx-auto mb-2" style="height: 20px;">
            <div id="overlay-bar" class="progress-bar progress-bar-striped progress-bar-animated bg-success"
                 role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                0%
            </div>
        </div>
        <p class="text-light small mb-0" id="overlay-detail"></p>
        <p class="text-light small mt-2">
            <i class="bi bi-exclamation-triangle"></i> Não saia desta página até a conclusão.
        </p>
    </div>
</div>

<div id="result-modal" class="modal fade" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" id="result-header">
                <h5 class="modal-title" id="result-title">Resultado</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="result-body">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">OK</button>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
.import-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.85);
    z-index: 9999;
    display: flex;
    align-items: center;
    justify-content: center;
}
.import-overlay-content {
    text-align: center;
    width: 100%;
    max-width: 500px;
}
</style>
@endpush

@push('scripts')
<script>
let importRunning = false;
let importTotal = 0;

function extractError(d) {
    if (d && d.error) return d.error;
    if (d && d.message) return d.message;
    if (d && d.errors) {
        var keys = Object.keys(d.errors);
        if (keys.length > 0) return d.errors[keys[0]][0];
    }
    return 'Erro desconhecido';
}

function startImport() {
    if (importRunning) return;

    var form = document.getElementById('import-form');
    var formData = new FormData(form);

    if (!formData.get('csv_file').name) {
        alert('Selecione um arquivo CSV.');
        return;
    }
    if (!formData.get('loja_id')) {
        alert('Selecione a loja destino.');
        return;
    }

    importRunning = true;
    document.getElementById('import-overlay').classList.remove('d-none');
    updateProgress(0, 'Iniciando...');
    showDetail('');

    formData.append('_token', document.querySelector('input[name="_token"]').value);
    fetch('{{ route("admin.importar.coletas.start") }}', {
        method: 'POST',
        headers: { 'Accept': 'application/json' },
        body: formData
    })
    .then(function (r) {
        if (!r.ok) { return r.json().then(function (d) { throw new Error(extractError(d)); }); }
        return r.json();
    })
    .then(function (data) {
        if (data.error) { showError(data.error); return; }
        importTotal = data.total;
        processChunks();
    })
    .catch(function (e) { showError(e.message || 'Erro ao iniciar importação.'); });
}

function processChunks() {
    if (!importRunning) return;

    fetch('{{ route("admin.importar.coletas.chunk") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        }
    })
    .then(function (r) {
        if (!r.ok) { return r.json().then(function (d) { throw new Error(extractError(d)); }); }
        return r.json();
    })
    .then(function (data) {
        if (data.error) { showError(data.error); return; }

        var p = data.progress;

        updateProgress(p.percent, 'Processando... (' + p.processed + ' de ' + p.total + ')');
        showDetail(
            'Importadas: ' + p.importadas +
            ' | Areas criadas: ' + p.areas_criadas +
            (p.erros > 0 ? ' | Erros: ' + p.erros : '')
        );

        if (data.done) {
            importRunning = false;
            document.getElementById('import-overlay').classList.add('d-none');
            showResult(p);
        } else {
            setTimeout(processChunks, 100);
        }
    })
    .catch(function (e) { showError(e.message || 'Erro ao processar lote.'); });
}

function updateProgress(percent, status) {
    var bar = document.getElementById('overlay-bar');
    bar.style.width = percent + '%';
    bar.setAttribute('aria-valuenow', percent);
    bar.textContent = percent + '%';
    if (percent >= 100) {
        bar.classList.remove('bg-success');
        bar.classList.add('bg-warning');
    }
    document.getElementById('overlay-status').textContent = status;
}

function showDetail(text) {
    document.getElementById('overlay-detail').textContent = text;
}

function showResult(p) {
    var title = document.getElementById('result-title');
    var header = document.getElementById('result-header');
    var body = document.getElementById('result-body');

    if (p.erros > 0) {
        header.className = 'modal-header text-bg-warning';
        title.textContent = 'Importação concluída com erros';
    } else {
        header.className = 'modal-header text-bg-success';
        title.textContent = 'Importação concluída com sucesso';
    }

    body.innerHTML =
        '<p>' + p.message + '</p>' +
        '<table class="table table-sm table-bordered mb-0">' +
        '<tr><td>Total de linhas</td><td><strong>' + p.total + '</strong></td></tr>' +
        '<tr><td>Processadas</td><td><strong>' + p.processed + '</strong></td></tr>' +
        '<tr><td>Importadas/atualizadas</td><td><strong>' + p.importadas + '</strong></td></tr>' +
        '<tr><td>Puladas</td><td><strong>' + p.puladas + '</strong></td></tr>' +
        '<tr><td>Áreas criadas</td><td><strong>' + p.areas_criadas + '</strong></td></tr>' +
        (p.erros > 0 ? '<tr><td>Erros</td><td><strong class="text-danger">' + p.erros + '</strong></td></tr>' : '') +
        '</table>';

    var modal = new bootstrap.Modal(document.getElementById('result-modal'));
    modal.show();
}

function showError(msg) {
    importRunning = false;
    document.getElementById('import-overlay').classList.add('d-none');

    var title = document.getElementById('result-title');
    var header = document.getElementById('result-header');
    var body = document.getElementById('result-body');

    header.className = 'modal-header text-bg-danger';
    title.textContent = 'Erro na importação';
    body.innerHTML = '<p class="mb-0">' + msg + '</p>';

    var modal = new bootstrap.Modal(document.getElementById('result-modal'));
    modal.show();
}

window.addEventListener('beforeunload', function (e) {
    if (importRunning) {
        e.preventDefault();
        e.returnValue = '';
    }
});
</script>
@endpush
@endsection
