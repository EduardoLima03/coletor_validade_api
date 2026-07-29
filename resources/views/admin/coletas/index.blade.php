@extends("layouts.app")

@section("title", "Coletas")

@push("styles")
<style>
    @media print {
        .sidebar, .topbar, .footer-dc, .card.mb-3, .btn-group, .pagination, .sidebar-overlay {
            display: none !important;
        }
        .main-content {
            margin-left: 0 !important;
        }
        .content-area {
            padding: 0 !important;
        }
        .card {
            box-shadow: none !important;
            border: none !important;
        }
        .table {
            font-size: 10pt;
        }
        .table-dark {
            background: #005922 !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        .table-dark th a {
            color: #fff !important;
        }
        .badge {
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        a[href]:after {
            content: none !important;
        }
        .no-print {
            display: none !important;
        }
    }
</style>
@endpush

@section("content")
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0"><i class="bi bi-clipboard-data"></i> Coletas</h4>
    <div class="btn-group">
        <a href="{{ route("admin.coletas.trashed") }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-archive"></i> Excluídos
        </a>
        <a href="{{ route("admin.coletas.export", ["format" => "xlsx", ...request()->query()]) }}" class="btn btn-success btn-sm">
            <i class="bi bi-file-earmark-excel"></i> Excel
        </a>
        <a href="{{ route("admin.coletas.export", ["format" => "csv", ...request()->query()]) }}" class="btn btn-secondary btn-sm">
            <i class="bi bi-file-earmark-spreadsheet"></i> CSV
        </a>
        <button onclick="window.print()" class="btn btn-outline-dark btn-sm">
            <i class="bi bi-printer"></i> Imprimir
        </button>
    </div>
</div>

<div class="card mb-3">
    <div class="card-body">
        <form method="GET" action="{{ route("admin.coletas.index") }}" class="row g-2">
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
            <div class="col-md-2">
                <label class="form-label">Auditor</label>
                <select name="user_id" class="form-select">
                    <option value="">Todos</option>
                    @foreach ($auditores as $user)
                        <option value="{{ $user->id }}" {{ request("user_id") == $user->id ? "selected" : "" }}>
                            {{ $user->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Setor</label>
                <select name="area_auditoria_id" class="form-select">
                    <option value="">Todos</option>
                    @foreach ($areas as $area)
                        <option value="{{ $area->id }}" {{ request("area_auditoria_id") == $area->id ? "selected" : "" }}>
                            {{ $area->nome }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">EAN</label>
                <input type="text" name="ean" class="form-control" placeholder="Buscar EAN" value="{{ request("ean") }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">Descricao</label>
                <input type="text" name="descricao" class="form-control" placeholder="Buscar descricao" value="{{ request("descricao") }}">
            </div>
            <div class="col-md-2">
                <label class="form-label">Dias a vencer</label>
                <select name="dias" class="form-select">
                    <option value="">Todos</option>
                    @foreach ([5, 7, 12, 15, 20, 30, 60] as $d)
                        <option value="{{ $d }}" {{ request("dias") == $d ? "selected" : "" }}>
                            Ate {{ $d }} dias
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Validade inicio</label>
                <input type="date" name="data_inicio" class="form-control" value="{{ request("data_inicio", date("Y-m-d")) }}">
            </div>
            <div class="col-md-2">
                <label class="form-label">Validade fim</label>
                <input type="date" name="data_fim" class="form-control" value="{{ request("data_fim") }}">
            </div>
            <div class="col-md-2">
                <label class="form-label">Coleta inicio</label>
                <input type="date" name="data_coleta_inicio" class="form-control" value="{{ request("data_coleta_inicio") }}">
            </div>
            <div class="col-md-2">
                <label class="form-label">Coleta fim</label>
                <input type="date" name="data_coleta_fim" class="form-control" value="{{ request("data_coleta_fim") }}">
            </div>
            <div class="col-md-2 d-flex align-items-end gap-1">
                <button type="submit" class="btn btn-dc-primary flex-grow-1">
                    <i class="bi bi-funnel"></i> Filtrar
                </button>
                <a href="{{ route("admin.coletas.index") }}" class="btn btn-outline-secondary">
                    <i class="bi bi-x-lg"></i>
                </a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                @php
                    $currentSort = request('sort', 'id');
                    $currentDir = request('direction', 'asc');
                @endphp
                <thead class="table-dark">
                    <tr>
                        <th><a href="{{ sortUrl('id', $currentSort, $currentDir) }}" class="text-white text-decoration-none"># {!! sortIcon('id', $currentSort, $currentDir) !!}</a></th>
                        <th><a href="{{ sortUrl('loja', $currentSort, $currentDir) }}" class="text-white text-decoration-none">Loja {!! sortIcon('loja', $currentSort, $currentDir) !!}</a></th>
                        <th><a href="{{ sortUrl('auditor', $currentSort, $currentDir) }}" class="text-white text-decoration-none">Auditor {!! sortIcon('auditor', $currentSort, $currentDir) !!}</a></th>
                        <th><a href="{{ sortUrl('setor', $currentSort, $currentDir) }}" class="text-white text-decoration-none">Setor {!! sortIcon('setor', $currentSort, $currentDir) !!}</a></th>
                        <th><a href="{{ sortUrl('descricao', $currentSort, $currentDir) }}" class="text-white text-decoration-none">Descricao {!! sortIcon('descricao', $currentSort, $currentDir) !!}</a></th>
                        <th><a href="{{ sortUrl('ean', $currentSort, $currentDir) }}" class="text-white text-decoration-none">EAN {!! sortIcon('ean', $currentSort, $currentDir) !!}</a></th>
                        <th><a href="{{ sortUrl('quantidade', $currentSort, $currentDir) }}" class="text-white text-decoration-none">Qtd {!! sortIcon('quantidade', $currentSort, $currentDir) !!}</a></th>
                        <th><a href="{{ sortUrl('unidade', $currentSort, $currentDir) }}" class="text-white text-decoration-none">Un {!! sortIcon('unidade', $currentSort, $currentDir) !!}</a></th>
                        <th><a href="{{ sortUrl('validade', $currentSort, $currentDir) }}" class="text-white text-decoration-none">Validade {!! sortIcon('validade', $currentSort, $currentDir) !!}</a></th>
                        <th>Dias</th>
                        <th><a href="{{ sortUrl('datahora', $currentSort, $currentDir) }}" class="text-white text-decoration-none">Data/Hora {!! sortIcon('datahora', $currentSort, $currentDir) !!}</a></th>
                        @if ($podeEditar || $podeExcluir)
                            <th class="text-center no-print" width="140">Acoes</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @forelse ($coletas as $coleta)
                        <tr>
                            <td>{{ $coleta->id }}</td>
                            <td>{{ $coleta->loja->nome ?? "-" }}</td>
                            <td>{{ $coleta->user->name ?? "-" }}</td>
                            <td>{{ $coleta->areaAuditoria->nome ?? "-" }}</td>
                            <td>{{ Str::limit($coleta->productName, 40) }}</td>
                            <td>{{ $coleta->ean }}</td>
                            <td>{{ $coleta->quantidade }}</td>
                            <td>{{ $coleta->unidade ?? "un" }}</td>
                            <td>{{ $coleta->data_validade->format("d/m/Y") }}</td>
                            <td>
                                @php $dias = $coleta->dias_a_vencer; @endphp
                                @if ($dias < 0)
                                    <span class="badge bg-danger">{{ $dias }} dias</span>
                                @elseif ($dias <= 5)
                                    <span class="badge bg-warning text-dark">{{ $dias }} dias</span>
                                @elseif ($dias <= 15)
                                    <span class="badge bg-info text-dark">{{ $dias }} dias</span>
                                @else
                                    <span class="badge bg-success">{{ $dias }} dias</span>
                                @endif
                            </td>
                            <td>{{ $coleta->datahora->format("d/m/Y H:i") }}</td>
                            @if ($podeEditar || $podeExcluir)
                            <td class="text-center no-print">
                                @if ($podeEditar)
                                @php
                                    $queryStr = http_build_query(request()->query());
                                    $editUrl = route("admin.coletas.edit", $coleta->id);
                                    if ($queryStr) {
                                        $editUrl .= '?' . $queryStr;
                                    }
                                @endphp
                                <a href="{{ $editUrl }}"
                                   class="btn btn-sm btn-outline-primary" title="Editar">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                @endif
                                @if ($podeExcluir)
                                <form action="{{ route("admin.coletas.destroy", $coleta->id) }}"
                                      method="POST"
                                      class="d-inline"
                                      onsubmit="return confirm(\"Tem certeza que deseja excluir esta coleta?\")">
                                    @csrf
                                    @method("DELETE")
                                    <input type="hidden" name="return_url" value="{{ url()->full() }}">
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Excluir">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                                @endif
                            </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ ($podeEditar || $podeExcluir) ? 11 : 10 }}" class="text-center text-muted py-4">
                                <i class="bi bi-inbox"></i> Nenhuma coleta encontrada.
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