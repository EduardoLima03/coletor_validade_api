@extends('layouts.app')

@section('title', 'Detalhes do Usuário')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0"><i class="bi bi-person"></i> Detalhes do Usuário</h4>
    <div>
        <a href="{{ route('admin.users.edit', $user->id) }}" class="btn btn-primary">
            <i class="bi bi-pencil"></i> Editar
        </a>
        <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Voltar
        </a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <table class="table table-sm">
            <tr>
                <th class="text-muted" width="120">Nome</th>
                <td>
                    {{ $user->name }}
                    @if ($user->id === auth()->id())
                        <span class="badge bg-info text-dark">Você</span>
                    @endif
                </td>
            </tr>
            <tr>
                <th class="text-muted">Email</th>
                <td>{{ $user->email }}</td>
            </tr>
            <tr>
                <th class="text-muted">Cargo</th>
                <td>{{ $user->position ?? '-' }}</td>
            </tr>
            <tr>
                <th class="text-muted">Criado em</th>
                <td>{{ $user->created_at ? $user->created_at->format('d/m/Y H:i:s') : '-' }}</td>
            </tr>
            <tr>
                <th class="text-muted">Atualizado em</th>
                <td>{{ $user->updated_at ? $user->updated_at->format('d/m/Y H:i:s') : '-' }}</td>
            </tr>
        </table>
    </div>
</div>
@endsection
