@extends('layouts.app')

@section('title', 'Detalhes da Auditoria')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0"><i class="bi bi-journal-text"></i> Detalhes do Registro</h4>
    <a href="{{ route('admin.audit.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Voltar
    </a>
</div>

<div class="card">
    <div class="card-body">
        <table class="table table-sm">
            <tr>
                <th class="text-muted" width="120">Data/Hora</th>
                <td>{{ $log->created_at->format('d/m/Y H:i:s') }}</td>
            </tr>
            <tr>
                <th class="text-muted">Usuário</th>
                <td>{{ $log->user->name ?? 'Sistema' }} ({{ $log->user->email ?? '-' }})</td>
            </tr>
            <tr>
                <th class="text-muted">Ação</th>
                <td>
                    @php
                        $badge = match($log->action) {
                            'create' => 'success', 'update' => 'primary', 'delete' => 'danger',
                            'login' => 'info', 'logout' => 'secondary', 'import' => 'warning',
                            default => 'secondary',
                        };
                    @endphp
                    <span class="badge bg-{{ $badge }}">{{ ucfirst($log->action) }}</span>
                </td>
            </tr>
            <tr>
                <th class="text-muted">Entidade</th>
                <td>{{ ucfirst($log->entity_type) }}</td>
            </tr>
            <tr>
                <th class="text-muted">ID Entidade</th>
                <td>{{ $log->entity_id ?? '-' }}</td>
            </tr>
            <tr>
                <th class="text-muted">Descrição</th>
                <td>{{ $log->description ?? '-' }}</td>
            </tr>
            <tr>
                <th class="text-muted">IP</th>
                <td>{{ $log->ip_address ?? '-' }}</td>
            </tr>
        </table>
    </div>
</div>
@endsection
