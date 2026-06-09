@extends("layouts.app")

@section("title", "Coletas Excluídas")

@section("content")
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0"><i class="bi bi-archive"></i> Coletas Excluídas</h4>
    <a href="{{ route("admin.coletas.index") }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left"></i> Voltar
    </a>
</div>

<div class="card mb-3">
    <div class="card-body">
        <form method="GET" action="{{ route("admin.coletas.trashed") }}" class="row g-2">
            <div class="col-md-3">
                <label class="form-label">Loja</label>
                <select name="loja_id" class="form-select">
                    <option value="">Todas</option>
                    @foreach ($lojas as $loja)
                        <option value="{{ $loja->id }}" {{ request("loja_id") == $loja->id ? "selected" : "" }}>
                            {{ $loja->nome }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button type="submit" class="btn btn-dc-primary flex-grow-1">
                    <i class="bi bi-funnel"></i> Filtrar
                </button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>Loja</th>
                        <th>Auditor</th>
                        <th>Setor</th>
                        <th>Descricao</th>
                        <th>EAN</th>
                        <th>Qtd</th>
                        <th>Un</th>
                        <th>Validade</th>
                        <th>Excluído em</th>
                        <th class="text-center" width="100">Ação</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($coletas as $coleta)
                        <tr class="text-muted">
                            <td>{{ $coleta->id }}</td>
                            <td>{{ $coleta->loja->nome ?? "-" }}</td>
                            <td>{{ $coleta->user->name ?? "-" }}</td>
                            <td>{{ $coleta->areaAuditoria->nome ?? "-" }}</td>
                            <td>{{ Str::limit($coleta->descricao, 40) }}</td>
                            <td>{{ $coleta->ean }}</td>
                            <td>{{ $coleta->quantidade }}</td>
                            <td>{{ $coleta->unidade ?? "un" }}</td>
                            <td>{{ $coleta->data_validade->format("d/m/Y") }}</td>
                            <td>{{ $coleta->deleted_at->format("d/m/Y H:i") }}</td>
                            <td class="text-center">
                                <form action="{{ route("admin.coletas.restore", $coleta->id) }}"
                                      method="POST"
                                      class="d-inline"
                                      onsubmit="return confirm('Restaurar esta coleta?')">
                                    @csrf
                                    @method("PUT")
                                    <button type="submit" class="btn btn-sm btn-outline-success" title="Restaurar">
                                        <i class="bi bi-arrow-counterclockwise"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center text-muted py-4">
                                <i class="bi bi-inbox"></i> Nenhuma coleta excluída.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if ($coletas->hasPages())
        <div class="card-footer">
            {{ $coletas->links() }}
        </div>
    @endif
</div>
@endsection
