@extends('layouts.app')

@section('title', 'Dashboard de Recolhimento')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0"><i class="bi bi-box-seam"></i> Dashboard de Recolhimento</h4>
    <div>
        <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-speedometer2"></i> Dashboard Geral
        </a>
        <a href="{{ route('admin.recolhimento-regras.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-gear"></i> Configurar Regras
        </a>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-6 col-md-3">
        <div class="card text-center p-3">
            <div class="fs-3 fw-bold text-primary">{{ $totalRegistros }}</div>
            <div class="text-muted small">Registros</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card text-center p-3">
            <div class="fs-3 fw-bold text-success">{{ number_format($totalQuantidade, 2) }}</div>
            <div class="text-muted small">Qtd. Recolhida</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card text-center p-3">
            <div class="fs-3 fw-bold text-warning">R$ {{ number_format($totalValor, 2, ',', '.') }}</div>
            <div class="text-muted small">Valor Total</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card text-center p-3">
            <div class="fs-3 fw-bold text-info">{{ $produtosDistintos }}</div>
            <div class="text-muted small">Produtos Distintos</div>
        </div>
    </div>
</div>

<div class="card mb-3">
    <div class="card-body">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label">Loja</label>
                <select name="loja_id" class="form-select form-select-sm">
                    <option value="">Todas</option>
                    @foreach ($lojas as $loja)
                        <option value="{{ $loja->id }}" {{ ($filters['loja_id'] ?? '') == $loja->id ? 'selected' : '' }}>
                            {{ $loja->nome }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Usuário</label>
                <select name="user_id" class="form-select form-select-sm">
                    <option value="">Todos</option>
                    @foreach ($auditores as $a)
                        <option value="{{ $a->id }}" {{ ($filters['user_id'] ?? '') == $a->id ? 'selected' : '' }}>
                            {{ $a->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Área</label>
                <select name="area_auditoria_id" class="form-select form-select-sm">
                    <option value="">Todas</option>
                    @foreach ($areas as $area)
                        <option value="{{ $area->id }}" {{ ($filters['area_auditoria_id'] ?? '') == $area->id ? 'selected' : '' }}>
                            {{ $area->nome }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Data Início</label>
                <input type="date" name="data_inicio" class="form-control form-control-sm"
                       value="{{ $filters['data_inicio'] ?? '' }}">
            </div>
            <div class="col-md-2">
                <label class="form-label">Data Fim</label>
                <input type="date" name="data_fim" class="form-control form-control-sm"
                       value="{{ $filters['data_fim'] ?? '' }}">
            </div>
            <div class="col-md-1">
                <button type="submit" class="btn btn-dc-primary btn-sm w-100">
                    <i class="bi bi-funnel"></i>
                </button>
            </div>
        </form>
    </div>
</div>

@if ($comparativoMensal->isNotEmpty())
<div class="card mb-3">
    <div class="card-header bg-white">
        <strong><i class="bi bi-bar-chart"></i> Comparativo Mensal (R$)</strong>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-sm mb-0">
                <thead>
                    <tr>
                        <th>Mês</th>
                        <th class="text-end">Registros</th>
                        <th class="text-end">Quantidade</th>
                        <th class="text-end">Valor Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($comparativoMensal as $mes)
                        <tr>
                            <td>{{ \Carbon\Carbon::createFromFormat('Y-m', $mes['mes'])->format('M/Y') }}</td>
                            <td class="text-end">{{ $mes['total_registros'] }}</td>
                            <td class="text-end">{{ number_format($mes['total_quantidade'], 2) }}</td>
                            <td class="text-end">R$ {{ number_format($mes['total_valor'], 2, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif

@if ($porLoja->isNotEmpty())
<div class="card mb-3">
    <div class="card-header bg-white">
        <strong><i class="bi bi-shop"></i> Por Loja</strong>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-sm mb-0">
                <thead>
                    <tr>
                        <th>Loja</th>
                        <th class="text-end">Registros</th>
                        <th class="text-end">Quantidade</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($porLoja as $item)
                        <tr>
                            <td>{{ $item['loja_nome'] }}</td>
                            <td class="text-end">{{ $item['total'] }}</td>
                            <td class="text-end">{{ number_format($item['quantidade'], 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif

<div class="card">
    <div class="card-header bg-white">
        <strong><i class="bi bi-list-ul"></i> Itens Recolhidos</strong>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>Produto</th>
                        <th>EAN</th>
                        <th>Loja</th>
                        <th>Área</th>
                        <th class="text-end">Qtd.</th>
                        <th class="text-end">Valor</th>
                        <th>Recolhido em</th>
                        <th>Usuário</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($itens as $item)
                        <tr>
                            <td>{{ $item->product_name }}</td>
                            <td><code>{{ $item->ean }}</code></td>
                            <td>{{ $item->loja?->nome }}</td>
                            <td>{{ $item->areaAuditoria?->nome ?? '-' }}</td>
                            <td class="text-end">{{ number_format((float) ($item->recolhido_quantidade ?? 0), 2) }}</td>
                            <td class="text-end">R$ {{ number_format($item->valor_recolhido, 2, ',', '.') }}</td>
                            <td>{{ $item->recolhido_em?->format('d/m/Y H:i') }}</td>
                            <td>{{ $item->recolhidoUser?->name ?? $item->user?->name }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                <i class="bi bi-inbox fs-4 d-block mb-1"></i>
                                Nenhum item recolhido encontrado.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="mt-3">
    {{ $itens->links() }}
</div>
@endsection
