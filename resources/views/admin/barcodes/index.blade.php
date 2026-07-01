@extends('layouts.app')

@section('title', 'Códigos de Barras')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0"><i class="bi bi-qr-code"></i> Códigos de Barras</h4>
    <a href="{{ route('admin.barcodes.create') }}" class="btn btn-success">
        <i class="bi bi-plus-lg"></i> Novo Código de Barras
    </a>
</div>

<div class="card">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.barcodes.index') }}" class="row g-2">
            <div class="col-md-8 col-lg-9">
                <div class="input-group">
                    <input type="text"
                           class="form-control"
                           name="search"
                           placeholder="Buscar por EAN, código ou descrição do produto..."
                           value="{{ $search ?? '' }}">
                    <button class="btn btn-outline-secondary" type="submit">
                        <i class="bi bi-search"></i> Buscar
                    </button>
                    @if ($search)
                        <a href="{{ route('admin.barcodes.index') }}" class="btn btn-outline-danger">
                            <i class="bi bi-x-lg"></i>
                        </a>
                    @endif
                </div>
            </div>
        </form>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>EAN</th>
                        <th>Código Produto</th>
                        <th>Descrição Produto</th>
                        <th>Criado em</th>
                        <th class="text-center" width="180">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($barcodes as $barcode)
                        <tr>
                            <td>{{ $barcode->ean }}</td>
                            <td>{{ $barcode->product->code ?? '-' }}</td>
                            <td>{{ $barcode->product->description ?? '-' }}</td>
                            <td>{{ $barcode->created_at->format('d/m/Y H:i') }}</td>
                            <td class="text-center">
                                <a href="{{ route('admin.barcodes.show', $barcode->id) }}"
                                   class="btn btn-sm btn-outline-info" title="Ver">
                                    <i class="bi bi-eye"></i>
                                </a>
                                @php
                                    $queryStr = http_build_query(request()->query());
                                    $editUrl = route('admin.barcodes.edit', $barcode->id);
                                    if ($queryStr) {
                                        $editUrl .= '?' . $queryStr;
                                    }
                                @endphp
                                <a href="{{ $editUrl }}"
                                   class="btn btn-sm btn-outline-primary" title="Editar">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('admin.barcodes.destroy', $barcode->id) }}"
                                      method="POST"
                                      class="d-inline"
                                      onsubmit="return confirm('Tem certeza que deseja excluir este código de barras?')">
                                    @csrf
                                    @method('DELETE')
                                    <input type="hidden" name="return_url" value="{{ url()->full() }}">
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Excluir">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">
                                <i class="bi bi-inbox"></i> Nenhum código de barras encontrado.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if ($barcodes->hasPages())
        <div class="card-footer">
            {{ $barcodes->links() }}
        </div>
    @endif
</div>
@endsection
