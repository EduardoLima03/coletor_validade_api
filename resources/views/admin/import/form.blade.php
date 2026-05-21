@extends('layouts.app')

@section('title', 'Importar CSV')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0"><i class="bi bi-upload"></i> Importar Produtos via CSV</h4>
    <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Voltar
    </a>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card mb-3">
            <div class="card-header"><strong>Processar VALIDADE.csv</strong></div>
            <div class="card-body">
                <p class="text-muted">
                    Processa o arquivo <code>VALIDADE.csv</code> localizado na raiz do projeto.
                    Produtos serão criados ou atualizados pelo código, e os códigos de barras vinculados automaticamente.
                </p>
                <form action="{{ route('admin.import.process') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="bi bi-play-fill"></i> Processar VALIDADE.csv
                    </button>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><strong>Upload de arquivo CSV</strong></div>
            <div class="card-body">
                <p class="text-muted">
                    Ou faça upload de um arquivo CSV com as colunas: <code>COD</code>, <code>DESCRICAO</code>, <code>EAN</code>.
                </p>
                <form action="{{ route('admin.import.process') }}" method="POST" enctype="multipart/form-data">
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
                    <button type="submit" class="btn btn-success">
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
@endsection
