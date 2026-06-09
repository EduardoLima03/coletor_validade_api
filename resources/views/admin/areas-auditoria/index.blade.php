@extends("layouts.app")

@section("title", "Áreas de Auditoria")

@section("content")
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0"><i class="bi bi-clipboard-check"></i> Áreas de Auditoria</h4>
    <a href="{{ route("admin.areas-auditoria.create") }}" class="btn btn-success">
        <i class="bi bi-plus-lg"></i> Nova Área
    </a>
</div>

<div class="card mb-3">
    <div class="card-body">
        <form method="GET" action="{{ route("admin.areas-auditoria.index") }}" class="row g-2">
            <div class="col-md-4">
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
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-dc-primary w-100">
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
                        <th>Loja</th>
                        <th>Nome</th>
                        <th>Descrição</th>
                        <th>Criado em</th>
                        <th class="text-center" width="160">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($areas as $area)
                        <tr>
                            <td>{{ $area->lojas->pluck("nome")->implode(", ") ?: "---" }}</td>
                            <td>{{ $area->nome }}</td>
                            <td>{{ $area->descricao ?? '---' }}</td>
                            <td>{{ $area->created_at->format("d/m/Y H:i") }}</td>
                            <td class="text-center">
                                <a href="{{ route("admin.areas-auditoria.edit", $area->id) }}"
                                   class="btn btn-sm btn-outline-primary" title="Editar">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route("admin.areas-auditoria.excluir", $area->id) }}"
                                      method="POST"
                                      class="d-inline"
                                      onsubmit="return confirm('Tem certeza que deseja excluir esta área?')">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Excluir">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">
                                <i class="bi bi-inbox"></i> Nenhuma área de auditoria cadastrada.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if ($areas->hasPages())
        <div class="card-footer">
            {{ $areas->links() }}
        </div>
    @endif
</div>
@endsection
