@extends('layouts.app')

@section('title', 'Detalhes do Produto')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0"><i class="bi bi-box"></i> Detalhes do Produto</h4>
    <div>
        <a href="{{ route('admin.products.edit', $product->id) }}" class="btn btn-primary">
            <i class="bi bi-pencil"></i> Editar
        </a>
        <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Voltar
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card mb-3">
            <div class="card-header"><strong>Informações</strong></div>
            <div class="card-body">
                <table class="table table-sm">
                    <tr>
                        <th class="text-muted" width="120">Código</th>
                        <td>{{ $product->code }}</td>
                    </tr>
                    <tr>
                        <th class="text-muted">Descrição</th>
                        <td>{{ $product->description }}</td>
                    </tr>
                    <tr>
                        <th class="text-muted">Custo Unitário</th>
                        <td>R$ {{ number_format($product->custo ?? 0, 2, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <th class="text-muted">Criado em</th>
                        <td>{{ $product->created_at->format('d/m/Y H:i:s') }}</td>
                    </tr>
                    <tr>
                        <th class="text-muted">Atualizado em</th>
                        <td>{{ $product->updated_at->format('d/m/Y H:i:s') }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <strong>Códigos de Barras</strong>
                <a href="{{ route('admin.barcodes.create', ['product_id' => $product->id]) }}"
                   class="btn btn-sm btn-success">
                    <i class="bi bi-plus-lg"></i> Adicionar
                </a>
            </div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>EAN</th>
                            <th class="text-center" width="120">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($product->barcodes as $barcode)
                            <tr>
                                <td>{{ $barcode->ean }}</td>
                                <td class="text-center">
                                    <a href="{{ route('admin.barcodes.edit', $barcode->id) }}"
                                       class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form action="{{ route('admin.barcodes.destroy', $barcode->id) }}"
                                          method="POST"
                                          class="d-inline"
                                          onsubmit="return confirm('Tem certeza?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2" class="text-center text-muted py-3">
                                    Nenhum código de barras vinculado.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
