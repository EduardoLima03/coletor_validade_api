@extends('layouts.app')

@section('title', 'Auditoria')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0"><i class="bi bi-journal-text"></i> Registro de Auditoria</h4>
</div>

<div class="card mb-3">
    <div class="card-body py-3">
        <form method="GET" action="{{ route('admin.audit.index') }}" class="row g-2">
            <div class="col-md-3">
                <input type="text" class="form-control form-control-sm" name="search"
                       placeholder="Buscar na descrição..." value="{{ $search ?? '' }}">
            </div>
            <div class="col-md-2">
                <select class="form-select form-select-sm" name="user_id">
                    <option value="">Todos os usuários</option>
                    @foreach ($users as $u)
                        <option value="{{ $u->id }}" {{ $userId == $u->id ? 'selected' : '' }}>
                            {{ $u->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select class="form-select form-select-sm" name="action">
                    <option value="">Todas ações</option>
                    @foreach ($actions as $a)
                        <option value="{{ $a }}" {{ $action == $a ? 'selected' : '' }}>
                            {{ ucfirst($a) }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <input type="date" class="form-control form-control-sm" name="date_from"
                       value="{{ $dateFrom ?? '' }}" placeholder="Data início">
            </div>
            <div class="col-md-2">
                <input type="date" class="form-control form-control-sm" name="date_to"
                       value="{{ $dateTo ?? '' }}" placeholder="Data fim">
            </div>
            <div class="col-md-1">
                <button type="submit" class="btn btn-sm btn-dc-primary w-100">
                    <i class="bi bi-search"></i>
                </button>
            </div>
            @if ($search || $userId || $action || $dateFrom || $dateTo)
                <div class="col-12">
                    <a href="{{ route('admin.audit.index') }}" class="btn btn-sm btn-outline-danger">
                        <i class="bi bi-x-lg"></i> Limpar filtros
                    </a>
                </div>
            @endif
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>Data/Hora</th>
                        <th>Usuário</th>
                        <th>Ação</th>
                        <th>Entidade</th>
                        <th>Descrição</th>
                        <th>IP</th>
                        <th class="text-center" width="60">Detalhes</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($logs as $log)
                        <tr>
                            <td class="text-nowrap">{{ $log->created_at->format('d/m/Y H:i:s') }}</td>
                            <td>{{ $log->user->name ?? 'Sistema' }}</td>
                            <td>
                                @php
                                    $badge = match($log->action) {
                                        'create' => 'success',
                                        'update' => 'primary',
                                        'delete' => 'danger',
                                        'login' => 'info',
                                        'logout' => 'secondary',
                                        'import' => 'warning',
                                        default => 'secondary',
                                    };
                                @endphp
                                <span class="badge bg-{{ $badge }}">{{ ucfirst($log->action) }}</span>
                            </td>
                            <td>{{ ucfirst($log->entity_type) }}</td>
                            <td>{{ Str::limit($log->description, 80) }}</td>
                            <td class="text-muted small">{{ $log->ip_address ?? '-' }}</td>
                            <td class="text-center">
                                <a href="{{ route('admin.audit.show', $log->id) }}"
                                   class="btn btn-sm btn-outline-info" title="Detalhes">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                <i class="bi bi-inbox"></i> Nenhum registro encontrado.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if ($logs->hasPages())
        <div class="card-footer">
            {{ $logs->links() }}
        </div>
    @endif
</div>
@endsection
