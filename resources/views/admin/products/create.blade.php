@extends('layouts.app')

@section('title', 'Novo Produto')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0"><i class="bi bi-plus-lg"></i> Novo Produto</h4>
    <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Voltar
    </a>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('admin.products.store') }}" method="POST">
            @csrf

            <div class="mb-3">
                <label for="code" class="form-label">Código</label>
                <input type="number"
                       class="form-control @error('code') is-invalid @enderror"
                       id="code"
                       name="code"
                       value="{{ old('code') }}"
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
                       value="{{ old('description') }}"
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
                       value="{{ old('custo', '0,00') }}"
                       placeholder="0,00">
                @error('custo')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <button type="submit" class="btn btn-success">
                <i class="bi bi-check-lg"></i> Salvar
            </button>
            <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary">Cancelar</a>
        </form>
    </div>
</div>
@endsection
