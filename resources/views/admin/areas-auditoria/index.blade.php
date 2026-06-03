@extends("layouts.app")

@section("title", "Áreas de Auditoria")

@section("content")
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0"><i class="bi bi-clipboard-check"></i> Áreas de Auditoria</h4>
    <a href="{{ route("admin.areas-auditoria.create") }}" class="btn btn-success">
        <i class="bi bi-plus-lg"></i> Nova Área
    </a>
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
                            <td>{{ $area->loja?->nome ?? '---' }}</td>
                            <td>{{ $area->nome }}</td>
                            <td>{{ $area->descricao ?? '---' }}</td>
                            <td>{{ $area->created_at->format("d/m/Y H:i") }}</td>
                            <td class="text-center">
                                <a href="{{ route("admin.areas-auditoria.edit", $area->id) }}"
                                   class="btn btn-sm btn-outline-primary" title="Editar">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route("admin.areas-auditoria.destroy", $area->id) }}"
                                      method="POST"
                                      class="d-inline"
                                      onsubmit="return confirm('Tem certeza que deseja excluir esta área?')">
                                    @csrf
                                    @method("DELETE")
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
