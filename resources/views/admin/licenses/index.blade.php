@extends("layouts.app")

@section("title", "Licenças")

@section("content")
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0"><i class="bi bi-key"></i> Licenças</h4>
    <a href="{{ route("admin.licenses.create") }}" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-lg"></i> Nova Licença
    </a>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>Cliente</th>
                    <th>CNPJ</th>
                    <th>Plano</th>
                    <th>Chave</th>
                    <th>Validade</th>
                    <th>Lojas</th>
                    <th>Usuários</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($licenses as $l)
                    <tr>
                        <td>{{ $l->client_name }}</td>
                        <td>{{ $l->cnpj }}</td>
                        <td>
                            <span class="badge bg-{{ $l->plan === 'enterprise' ? 'dark' : ($l->plan === 'pro' ? 'primary' : 'secondary') }}">
                                {{ ucfirst($l->plan) }}
                            </span>
                        </td>
                        <td>
                            <code class="small">{{ $l->license_key }}</code>
                        </td>
                        <td>
                            {{ $l->valid_from->format("d/m/Y") }} até {{ $l->valid_until->format("d/m/Y") }}
                        </td>
                        <td>{{ $l->max_stores }}</td>
                        <td>{{ $l->max_users }}</td>
                        <td>
                            @if ($l->isValid())
                                <span class="badge bg-success">Ativa</span>
                            @else
                                <span class="badge bg-danger">Expirada</span>
                            @endif
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route("admin.licenses.edit", $l->id) }}" class="btn btn-outline-secondary">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route("admin.licenses.destroy", $l->id) }}" method="POST"
                                      onsubmit="return confirm('Remover licença de {{ $l->client_name }}?')">
                                    @csrf
                                    @method("DELETE")
                                    <button type="submit" class="btn btn-outline-danger">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center py-4 text-muted">Nenhuma licença cadastrada.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-2">
    {{ $licenses->links() }}
</div>
@endsection
