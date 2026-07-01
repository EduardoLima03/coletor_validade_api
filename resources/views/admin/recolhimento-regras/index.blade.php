@extends('layouts.app')

@section('title', 'Regras de Recolhimento')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0"><i class="bi bi-gear"></i> Regras de Recolhimento</h4>
    <a href="{{ route('admin.recolhimento-regras.create') }}" class="btn btn-dc-primary btn-sm">
        <i class="bi bi-plus-lg"></i> Nova Regra
    </a>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>Dia da Semana</th>
                        <th>Dias Antecedência</th>
                        <th>Ativo</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($regras as $regra)
                        <tr>
                            <td>{{ $regra->dia_semana_nome }}</td>
                            <td>{{ $regra->dias_antecedencia }} dia(s)</td>
                            <td>
                                @if ($regra->ativo)
                                    <span class="badge bg-success">Sim</span>
                                @else
                                    <span class="badge bg-secondary">Não</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('admin.recolhimento-regras.edit', $regra) }}"
                                   class="btn btn-sm btn-outline-primary" title="Editar">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('admin.recolhimento-regras.destroy', $regra) }}"
                                      method="POST" class="d-inline"
                                      onsubmit="return confirm('Excluir esta regra?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Excluir">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">
                                <i class="bi bi-inbox fs-4 d-block mb-1"></i>
                                Nenhuma regra cadastrada.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
