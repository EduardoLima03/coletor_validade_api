@extends('layouts.app')

@section('title', 'Detalhes do Código de Barras')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0"><i class="bi bi-qr-code"></i> Detalhes do Código de Barras</h4>
    <div>
        <a href="{{ route('admin.barcodes.edit', $barcode->id) }}" class="btn btn-primary">
            <i class="bi bi-pencil"></i> Editar
        </a>
        <a href="{{ route('admin.barcodes.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Voltar
        </a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <table class="table table-sm">
            <tr>
                <th class="text-muted" width="150">EAN</th>
                <td>{{ $barcode->ean }}</td>
            </tr>
            <tr>
                <th class="text-muted">Produto</th>
                <td>
                    @if ($barcode->product)
                        <a href="{{ route('admin.products.show', $barcode->product->id) }}">
                            [{{ $barcode->product->code }}] {{ $barcode->product->description }}
                        </a>
                    @else
                        <span class="text-muted">Produto não encontrado</span>
                    @endif
                </td>
            </tr>
            <tr>
                <th class="text-muted">Criado em</th>
                <td>{{ $barcode->created_at->format('d/m/Y H:i:s') }}</td>
            </tr>
            <tr>
                <th class="text-muted">Atualizado em</th>
                <td>{{ $barcode->updated_at->format('d/m/Y H:i:s') }}</td>
            </tr>
        </table>
    </div>
</div>
@endsection
