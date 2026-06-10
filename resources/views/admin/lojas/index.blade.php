@extends("layouts.app")

@section("title", "Lojas")

@section("content")
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0"><i class="bi bi-shop"></i> Lojas</h4>
    <a href="{{ route("admin.lojas.create") }}" class="btn btn-success">
        <i class="bi bi-plus-lg"></i> Nova Loja
    </a>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>Nome</th>
                        <th>Coletas</th>
                        <th>Criado em</th>
                        <th class="text-center" width="160">Acoes</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($lojas as $loja)
                        <tr>
                            <td>{{ $loja->nome }}</td>
                            <td>{{ $loja->coletas()->count() }}</td>
                            <td>{{ $loja->created_at->format("d/m/Y H:i") }}</td>
                            <td class="text-center">
                                @php
                                    $queryStr = http_build_query(request()->query());
                                    $editUrl = route("admin.lojas.edit", $loja->id);
                                    if ($queryStr) {
                                        $editUrl .= "?" . $queryStr;
                                    }
                                @endphp
                                <a href="{{ $editUrl }}"
                                   class="btn btn-sm btn-outline-primary" title="Editar">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route("admin.lojas.destroy", $loja->id) }}"
                                      method="POST"
                                      class="d-inline"
                                      onsubmit="return confirm(\"Tem certeza que deseja excluir?\")">
                                    @csrf
                                    @method("DELETE")
                                    <input type="hidden" name="return_url" value="{{ url()->full() }}">
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Excluir">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">
                                <i class="bi bi-inbox"></i> Nenhuma loja cadastrada.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if ($lojas->hasPages())
        <div class="card-footer">
            {{ $lojas->links() }}
        </div>
    @endif
</div>
@endsection