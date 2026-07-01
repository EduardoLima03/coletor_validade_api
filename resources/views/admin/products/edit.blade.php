@extends('layouts.app')

@section('title', 'Editar Produto')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0"><i class="bi bi-pencil"></i> Editar Produto</h4>
    <a href="{{ $returnUrl }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Voltar
    </a>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('admin.products.update', $product->id) }}" method="POST">
            @csrf
            @method('PUT')
            <input type="hidden" name="return_url" value="{{ $returnUrl }}">

            <div class="mb-3">
                <label for="code" class="form-label">Código</label>
                <input type="number"
                       class="form-control @error('code') is-invalid @enderror"
                       id="code"
                       name="code"
                       value="{{ old('code', $product->code) }}"
                       required>
                @error('code')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="description" class="form-label">Descrição</label>
                <input type="text"
                       class="form-control @error('description') is-invalid @enderror"
                       id="description"
                       name="description"
                       value="{{ old('description', $product->description) }}"
                       required
                       maxlength="255">
                @error('description')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="custo" class="form-label">Custo Unitário (R$)</label>
                <input type="text"
                       class="form-control @error('custo') is-invalid @enderror"
                       id="custo"
                       name="custo"
                       value="{{ old('custo', number_format($product->custo ?? 0, 2, ',', '')) }}"
                       placeholder="0,00">
                @error('custo')
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
