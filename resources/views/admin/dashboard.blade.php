@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="card mb-3">
    <div class="card-body">
        <form method="GET" action="{{ route("admin.dashboard") }}" class="row g-2">
            <div class="col-md-2">
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
                <label class="form-label">Data inicio</label>
                <input type="date" name="data_inicio" class="form-control" value="{{ request("data_inicio") }}">
            </div>
            <div class="col-md-2">
                <label class="form-label">Data fim</label>
                <input type="date" name="data_fim" class="form-control" value="{{ request("data_fim") }}">
            </div>
            <div class="col-12 d-flex gap-1">
                <button type="submit" class="btn btn-dc-primary">
                    <i class="bi bi-funnel"></i> Filtrar
                </button>
                <a href="{{ route("admin.dashboard") }}" class="btn btn-outline-secondary">
                    <i class="bi bi-x-lg"></i> Limpar
                </a>
            </div>
        </form>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-6 col-md-4 col-xl-2">
        <div class="card text-white h-100" style="background: linear-gradient(135deg, #005922, #003d17);">
            <div class="card-body text-center">
                <h6 class="card-title mb-1 opacity-75 small">Total Coletas</h6>
                <h3 class="mb-0 fw-bold">{{ $totalColetas }}</h3>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-2">
        <div class="card text-white h-100" style="background: linear-gradient(135deg, #F01516, #b01011);">
            <div class="card-body text-center">
                <h6 class="card-title mb-1 opacity-75 small">Vencidas</h6>
                <h3 class="mb-0 fw-bold">{{ $coletasVencidas }}</h3>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-2">
        <div class="card text-white h-100" style="background: linear-gradient(135deg, #FF8C00, #cc7000);">
            <div class="card-body text-center">
                <h6 class="card-title mb-1 opacity-75 small">Ate 5 dias</h6>
                <h3 class="mb-0 fw-bold">{{ $coletasAte5 }}</h3>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-2">
        <div class="card text-white h-100" style="background: linear-gradient(135deg, #FFD100, #cca700);">
            <div class="card-body text-center">
                <h6 class="card-title mb-1 opacity-75 small">Ate 15 dias</h6>
                <h3 class="mb-0 fw-bold">{{ $coletasAte15 }}</h3>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-2">
        <div class="card text-white h-100" style="background: linear-gradient(135deg, #A7D02A, #7fa020);">
            <div class="card-body text-center">
                <h6 class="card-title mb-1 opacity-75 small">Produtos</h6>
                <h3 class="mb-0 fw-bold">{{ $produtosDistintos }}</h3>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-2">
        <div class="card text-white h-100" style="background: linear-gradient(135deg, #1E90FF, #0066cc);">
            <div class="card-body text-center">
                <h6 class="card-title mb-1 opacity-75 small">EANs</h6>
                <h3 class="mb-0 fw-bold">{{ $eansDistintos }}</h3>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-2">
        <div class="card text-white h-100" style="background: linear-gradient(135deg, #6c757d, #495057);">
            <div class="card-body text-center">
                <h6 class="card-title mb-1 opacity-75 small">Excluídas</h6>
                <h3 class="mb-0 fw-bold">{{ $coletasExcluidas }}</h3>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-shop"></i> Coletas por Loja</h6>
            </div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>Loja</th>
                            <th class="text-end">Coletas</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($coletasPorLoja as $item)
                            <tr>
                                <td>{{ $item->loja->nome ?? "-" }}</td>
                                <td class="text-end">{{ $item->total }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2" class="text-center text-muted py-3">Nenhuma coleta.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-person"></i> Coletas por Auditor</h6>
            </div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>Auditor</th>
                            <th class="text-end">Coletas</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($coletasPorAuditor as $item)
                            <tr>
                                <td>{{ $item->user->name ?? "-" }}</td>
                                <td class="text-end">{{ $item->total }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2" class="text-center text-muted py-3">Nenhuma coleta.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="row mt-3">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-person-lines-fill"></i> Métricas por Usuário</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th>Usuário</th>
                                <th class="text-end">Coletas</th>
                                <th class="text-end">Qtd Total</th>
                                <th class="text-end">EANs</th>
                                <th class="text-end">Áreas</th>
                                <th class="text-end">Tempo</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($metricasUsuarios as $item)
                                <tr>
                                    <td>{{ $item->user->name ?? "-" }}</td>
                                    <td class="text-end">{{ $item->total_coletas }}</td>
                                    <td class="text-end">{{ $item->total_qtd }}</td>
                                    <td class="text-end">{{ $item->total_eans }}</td>
                                    <td class="text-end">{{ $item->total_areas }}</td>
                                    <td class="text-end">{{ $item->tempo_formatado }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-3">Nenhuma coleta.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mt-3">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-clock-history"></i> Ultimas Coletas</h6>
            </div>
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
                                <th>Validade</th>
                                <th>Dias</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($ultimasColetas as $coleta)
                                <tr>
                                    <td>{{ $coleta->id }}</td>
                                    <td>{{ $coleta->loja->nome ?? "-" }}</td>
                                    <td>{{ $coleta->user->name ?? "-" }}</td>
                                    <td>{{ $coleta->areaAuditoria->nome ?? "-" }}</td>
                                    <td>{{ Str::limit($coleta->descricao, 35) }}</td>
                                    <td>{{ $coleta->ean }}</td>
                                    <td>{{ $coleta->quantidade }}</td>
                                    <td>{{ $coleta->data_validade->format("d/m/Y") }}</td>
                                    <td>
                                        @php $d = $coleta->dias_a_vencer; @endphp
                                        @if ($d < 0)
                                            <span class="badge bg-danger">{{ $d }}</span>
                                        @elseif ($d <= 5)
                                            <span class="badge bg-warning text-dark">{{ $d }}</span>
                                        @elseif ($d <= 15)
                                            <span class="badge bg-info text-dark">{{ $d }}</span>
                                        @else
                                            <span class="badge bg-success">{{ $d }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center text-muted py-3">Nenhuma coleta.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
