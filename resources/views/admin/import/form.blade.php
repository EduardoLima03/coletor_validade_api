@extends('layouts.app')

@section('title', 'Importar CSV')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0"><i class="bi bi-upload"></i> Importar Produtos via CSV</h4>
    <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Voltar
    </a>
</div>

@if (session('import_errors'))
    <div class="card mb-3 border-danger">
        <div class="card-header text-bg-danger">
            <i class="bi bi-exclamation-triangle"></i> Detalhes dos erros ({{ count(session('import_errors')) }})
        </div>
        <div class="card-body p-0">
            <div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
                <table class="table table-sm table-bordered mb-0">
                    <thead class="table-secondary">
                        <tr>
                            <th>Linha</th>
                            <th>Código</th>
                            <th>EAN</th>
                            <th>Motivo</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach (session('import_errors') as $err)
                            <tr>
                                <td>{{ $err['line'] }}</td>
                                <td>{{ $err['code'] }}</td>
                                <td>{{ $err['ean'] }}</td>
                                <td class="text-danger">{{ $err['reason'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endif

<div class="row">
    <div class="col-md-8">
        <div class="card mb-3">
            <div class="card-header"><strong>Processar VALIDADE.csv</strong></div>
            <div class="card-body">
                <p class="text-muted">
                    Processa o arquivo <code>VALIDADE.csv</code> localizado na raiz do projeto.
                    Produtos serão criados ou atualizados pelo código, e os códigos de barras vinculados automaticamente.
                </p>
                <button type="button" class="btn btn-primary btn-lg" onclick="startImport('default')">
                    <i class="bi bi-play-fill"></i> Processar VALIDADE.csv
                </button>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><strong>Upload de arquivo CSV</strong></div>
            <div class="card-body">
                <p class="text-muted">
                    Ou faça upload de um arquivo CSV com as colunas: <code>COD</code>, <code>DESCRICAO</code>, <code>EAN</code>.
                </p>
                <form id="upload-form">
                    @csrf
                    <div class="mb-3">
                        <label for="csv_file" class="form-label">Selecionar arquivo CSV</label>
                        <input type="file"
                               class="form-control @error('csv_file') is-invalid @enderror"
                               id="csv_file"
                               name="csv_file"
                               accept=".csv,.txt">
                        @error('csv_file')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <button type="button" class="btn btn-success" onclick="startImport('upload')">
                        <i class="bi bi-upload"></i> Importar CSV
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-header"><strong>Formato esperado</strong></div>
            <div class="card-body">
                <p class="text-muted mb-2">O CSV deve conter as seguintes colunas:</p>
                <table class="table table-sm table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>Coluna</th>
                            <th>Exemplo</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><code>COD</code></td>
                            <td>1500104</td>
                        </tr>
                        <tr>
                            <td><code>DESCRICAO</code></td>
                            <td>FEIJAO CORDA PRECIOSO 1KG</td>
                        </tr>
                        <tr>
                            <td><code>EAN</code></td>
                            <td>7898926342068</td>
                        </tr>
                    </tbody>
                </table>
                <p class="text-muted small mb-0">Separador: vírgula (<code>,</code>)<br>Delimitador: aspas duplas (<code>"</code>)</p>
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
let importProcessed = 0;

function startImport(type) {
    if (importRunning) return;
    importRunning = true;

    document.getElementById('import-overlay').classList.remove('d-none');
    updateProgress(0, 'Iniciando...');
    showDetail('');

    if (type === 'upload') {
        var form = document.getElementById('upload-form');
        var formData = new FormData(form);
        if (!formData.get('csv_file').name) {
            alert('Selecione um arquivo CSV.');
            importRunning = false;
            document.getElementById('import-overlay').classList.add('d-none');
            return;
        }
        startImportWithFile(formData);
    } else {
        startImportWithDefault();
    }
}

function startImportWithFile(formData) {
    formData.append('_token', document.querySelector('input[name="_token"]').value);
    fetch('{{ route("admin.import.start") }}', {
        method: 'POST',
        body: formData
    })
    .then(function (r) { return r.json(); })
    .then(function (data) {
        if (data.error) { showError(data.error); return; }
        importTotal = data.total;
        processChunks();
    })
    .catch(function () { showError('Erro ao iniciar importação.'); });
}

function startImportWithDefault() {
    fetch('{{ route("admin.import.start") }}', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value, 'Accept': 'application/json' }
    })
    .then(function (r) { return r.json(); })
    .then(function (data) {
        if (data.error) { showError(data.error); return; }
        importTotal = data.total;
        processChunks();
    })
    .catch(function () { showError('Erro ao iniciar importação.'); });
}

function processChunks() {
    if (!importRunning) return;

    fetch('{{ route("admin.import.chunk") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        }
    })
    .then(function (r) { return r.json(); })
    .then(function (data) {
        if (data.error) { showError(data.error); return; }

        var p = data.progress;
        importProcessed = p.processed;

        updateProgress(p.percent, 'Processando... (' + p.processed + ' de ' + p.total + ')');
        showDetail(
            'Produtos criados: ' + p.created_products +
            ' | Atualizados: ' + p.updated_products +
            ' | EANs criados: ' + p.created_barcodes +
            ' | Pulados: ' + p.skipped_barcodes +
            (p.errors > 0 ? ' | Erros: ' + p.errors : '')
        );

        if (data.done) {
            importRunning = false;
            document.getElementById('import-overlay').classList.add('d-none');
            showResult(p);
        } else {
            setTimeout(processChunks, 100);
        }
    })
    .catch(function () { showError('Erro ao processar lote.'); });
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

    if (p.errors > 0) {
        header.className = 'modal-header text-bg-warning';
        title.textContent = 'Importação concluída com erros';
    } else {
        header.className = 'modal-header text-bg-success';
        title.textContent = 'Importação concluída com sucesso';
    }

    var errorsHtml = '';
    if (p.error_details && p.error_details.length > 0) {
        var rows = p.error_details.map(function(e) {
            return '<tr><td>' + e.line + '</td><td>' + e.code + '</td><td>' + e.ean + '</td><td class="text-danger">' + e.reason + '</td></tr>';
        }).join('');
        errorsHtml =
            '<div class="mt-3">' +
            '<button class="btn btn-sm btn-outline-danger w-100" type="button" data-bs-toggle="collapse" data-bs-target="#errorDetails">' +
            '<i class="bi bi-exclamation-triangle"></i> Ver detalhes dos ' + p.errors + ' erro(s)' +
            '</button>' +
            '<div class="collapse mt-2" id="errorDetails">' +
            '<div class="table-responsive" style="max-height: 250px; overflow-y: auto;">' +
            '<table class="table table-sm table-bordered mb-0">' +
            '<thead class="table-secondary"><tr><th>Linha</th><th>Código</th><th>EAN</th><th>Motivo</th></tr></thead>' +
            '<tbody>' + rows + '</tbody></table></div></div></div>';
    }

    body.innerHTML =
        '<p>' + p.message + '</p>' +
        '<table class="table table-sm table-bordered mb-0">' +
        '<tr><td>Total de linhas</td><td><strong>' + p.total + '</strong></td></tr>' +
        '<tr><td>Processadas</td><td><strong>' + p.processed + '</strong></td></tr>' +
        '<tr><td>Produtos criados</td><td><strong>' + p.created_products + '</strong></td></tr>' +
        '<tr><td>Produtos atualizados</td><td><strong>' + p.updated_products + '</strong></td></tr>' +
        '<tr><td>Códigos de barras criados</td><td><strong>' + p.created_barcodes + '</strong></td></tr>' +
        '<tr><td>Códigos de barras pulados</td><td><strong>' + p.skipped_barcodes + '</strong></td></tr>' +
        (p.errors > 0 ? '<tr><td>Erros</td><td><strong class="text-danger">' + p.errors + '</strong></td></tr>' : '') +
        '</table>' +
        errorsHtml;

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