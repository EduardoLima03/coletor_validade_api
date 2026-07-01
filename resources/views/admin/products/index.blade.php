@extends('layouts.app')

@section('title', 'Produtos')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0"><i class="bi bi-box"></i> Produtos</h4>
    <div>
        <a href="{{ route('admin.import.form') }}" class="btn btn-warning">
            <i class="bi bi-upload"></i> Importar CSV
        </a>
        <a href="{{ route('admin.products.create') }}" class="btn btn-success">
            <i class="bi bi-plus-lg"></i> Novo Produto
        </a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.products.index') }}" class="row g-2">
            <div class="col-md-8 col-lg-9">
                <div class="input-group">
                    <input type="text"
                           class="form-control"
                           name="search"
                           placeholder="Buscar por código, descrição ou EAN..."
                           value="{{ $search ?? '' }}">
                    <button class="btn btn-outline-secondary" type="submit">
                        <i class="bi bi-search"></i> Buscar
                    </button>
                    @if ($search)
                        <a href="{{ route('admin.products.index') }}" class="btn btn-outline-danger">
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
                        <th>Código</th>
                        <th>Descrição</th>
                        <th>Códigos de Barras</th>
                        <th>Criado em</th>
                        <th class="text-center" width="180">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($products as $product)
                        <tr>
                            <td>{{ $product->code }}</td>
                            <td>{{ $product->description }}</td>
                            <td>
                                @foreach ($product->barcodes as $barcode)
                                    <span class="badge bg-info text-dark">{{ $barcode->ean }}</span>
                                @endforeach
                                @if ($product->barcodes->isEmpty())
                                    <span class="text-muted">Nenhum</span>
                                @endif
                            </td>
                            <td>{{ $product->created_at->format('d/m/Y H:i') }}</td>
                            <td class="text-center">
                                <a href="{{ route('admin.products.show', $product->id) }}"
                                   class="btn btn-sm btn-outline-info" title="Ver">
                                    <i class="bi bi-eye"></i>
                                </a>
                                @php
                                    $queryStr = http_build_query(request()->query());
                                    $editUrl = route('admin.products.edit', $product->id);
                                    if ($queryStr) {
                                        $editUrl .= '?' . $queryStr;
                                    }
                                @endphp
                                <a href="{{ $editUrl }}"
                                   class="btn btn-sm btn-outline-primary" title="Editar">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('admin.products.destroy', $product->id) }}"
                                      method="POST"
                                      class="d-inline"
                                      onsubmit="return confirm('Tem certeza que deseja excluir este produto?')">
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
                                <i class="bi bi-inbox"></i> Nenhum produto encontrado.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if ($products->hasPages())
        <div class="card-footer">
            {{ $products->links() }}
        </div>
    @endif
</div>
@endsection
