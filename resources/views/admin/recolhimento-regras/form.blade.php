@extends('layouts.app')

@section('title', isset($regra) ? 'Editar Regra' : 'Nova Regra')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">
        <i class="bi {{ isset($regra) ? 'bi-pencil' : 'bi-plus-lg' }}"></i>
        {{ isset($regra) ? 'Editar Regra' : 'Nova Regra' }}
    </h4>
    <a href="{{ route('admin.recolhimento-regras.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left"></i> Voltar
    </a>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ isset($regra) ? route('admin.recolhimento-regras.update', $regra) : route('admin.recolhimento-regras.store') }}"
              method="POST">
            @csrf
            @if (isset($regra)) @method('PUT') @endif

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Dia da Semana</label>
                    <select name="dia_semana" class="form-select @error('dia_semana') is-invalid @enderror">
                        <option value="">Selecione...</option>
                        @foreach ($diasSemana as $key => $nome)
                            <option value="{{ $key }}"
                                {{ old('dia_semana', $regra->dia_semana ?? '') == $key ? 'selected' : '' }}>
                                {{ $nome }}
                            </option>
                        @endforeach
                    </select>
                    @error('dia_semana') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label">Dias de Antecedência</label>
                    <input type="number" name="dias_antecedencia"
                           class="form-control @error('dias_antecedencia') is-invalid @enderror"
                           value="{{ old('dias_antecedencia', $regra->dias_antecedencia ?? '') }}"
                           min="1" max="365">
                    @error('dias_antecedencia') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-2 d-flex align-items-end">
                    <div class="form-check">
                        <input type="checkbox" name="ativo" value="1" id="ativo"
                               class="form-check-input"
                               {{ old('ativo', $regra->ativo ?? true) ? 'checked' : '' }}>
                        <label class="form-check-label" for="ativo">Ativo</label>
                    </div>
                </div>

                <div class="col-12">
                    <button type="submit" class="btn btn-dc-primary">
                        <i class="bi bi-check-lg"></i> {{ isset($regra) ? 'Atualizar' : 'Salvar' }}
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
