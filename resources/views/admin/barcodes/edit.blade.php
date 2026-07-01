@extends('layouts.app')

@section('title', 'Editar Código de Barras')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0"><i class="bi bi-pencil"></i> Editar Código de Barras</h4>
    <a href="{{ $returnUrl }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Voltar
    </a>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('admin.barcodes.update', $barcode->id) }}" method="POST">
            @csrf
            @method('PUT')
            <input type="hidden" name="return_url" value="{{ $returnUrl }}">

            <div class="mb-3">
                <label for="ean" class="form-label">EAN</label>
                <input type="number"
                       class="form-control @error('ean') is-invalid @enderror"
                       id="ean"
                       name="ean"
                       value="{{ old('ean', $barcode->ean) }}"
                       required>
                @error('ean')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="product_id" class="form-label">Produto</label>
                <select class="form-select @error('product_id') is-invalid @enderror"
                        id="product_id"
                        name="product_id"
                        required>
                    <option value="">Selecione um produto</option>
                    @foreach ($products as $product)
                        <option value="{{ $product->id }}"
                            {{ old('product_id', $barcode->product_id) == $product->id ? 'selected' : '' }}>
                            [{{ $product->code }}] {{ $product->description }}
                        </option>
                    @endforeach
                </select>
                @error('product_id')
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
