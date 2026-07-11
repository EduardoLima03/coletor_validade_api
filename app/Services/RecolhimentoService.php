<?php

namespace App\Services;

use App\Models\Coleta;
use App\Models\RecolhimentoRegra;
use App\Models\Notification;
use Illuminate\Support\Collection;

class RecolhimentoService
{
    public function produtosParaRecolher(int $lojaId): Collection
    {
        $dias = RecolhimentoRegra::diasAntecedenciaParaHoje();
        if (!$dias) {
            return collect();
        }

        return Coleta::with(['loja', 'barcode.product', 'areaAuditoria'])
            ->where('coletas.loja_id', $lojaId)
            ->disponiveisParaRecolhimento($dias)
            ->leftJoin('barcodes', 'coletas.ean', '=', 'barcodes.ean')
            ->leftJoin('products', 'barcodes.product_id', '=', 'products.id')
            ->orderBy('products.description')
            ->get();
    }

    public function registrarRecolhimento(int $coletaId, float $quantidade, int $userId): Coleta
    {
        $coleta = Coleta::findOrFail($coletaId);

        if ($quantidade <= 0) {
            $coleta->delete();
            return $coleta;
        }

        $coleta->update([
            'recolhido_em' => now(),
            'recolhido_quantidade' => $quantidade,
            'recolhido_user_id' => $userId,
            'datahora' => now(),
            'user_id' => $userId,
            'quantidade' => $quantidade,
        ]);

        return $coleta->fresh();
    }

    public function gerarNotificacoes(): int
    {
        $regras = RecolhimentoRegra::ativos()->get();
        $created = 0;

        foreach ($regras as $regra) {
            $coletas = Coleta::with('user', 'loja')
                ->disponiveisParaRecolhimento($regra->dias_antecedencia)
                ->get()
                ->groupBy('user_id');

            $jaNotificadosHoje = Notification::where('type', 'recolhimento_alert')
                ->whereDate('created_at', now())
                ->pluck('user_id')
                ->toArray();

            foreach ($coletas as $userId => $userColetas) {
                if (!$userId || in_array($userId, $jaNotificadosHoje)) {
                    continue;
                }

                $count = $userColetas->count();
                $lojaNomes = $userColetas->pluck('loja.nome')->unique()->take(3)->implode(', ');

                Notification::create([
                    'user_id' => $userId,
                    'type' => 'recolhimento_alert',
                    'title' => "{$count} produto(s) para recolher",
                    'message' => "{$count} produto(s) vence(m) nos próximos {$regra->dias_antecedencia} dia(s). Lojas: {$lojaNomes}.",
                    'icon' => 'bi-box-seam',
                    'color' => 'primary',
                    'data' => [
                        'regra_id' => $regra->id,
                        'dias_antecedencia' => $regra->dias_antecedencia,
                        'total' => $count,
                    ],
                ]);

                $created++;
            }
        }

        return $created;
    }

    public function metrics(array $filters = []): array
    {
        $query = Coleta::recolhidos()->with(['loja', 'user', 'barcode.product', 'areaAuditoria']);

        if (!empty($filters['loja_id'])) {
            $query->where('loja_id', $filters['loja_id']);
        }
        if (!empty($filters['user_id'])) {
            $query->where('recolhido_user_id', $filters['user_id']);
        }
        if (!empty($filters['area_auditoria_id'])) {
            $query->where('area_auditoria_id', $filters['area_auditoria_id']);
        }
        if (!empty($filters['data_inicio'])) {
            $query->whereDate('recolhido_em', '>=', $filters['data_inicio']);
        }
        if (!empty($filters['data_fim'])) {
            $query->whereDate('recolhido_em', '<=', $filters['data_fim']);
        }

        $totalRegistros = (clone $query)->count();
        $totalQuantidade = (clone $query)->sum('recolhido_quantidade');
        $totalValor = (clone $query)->get()->sum(fn($c) => $c->valor_recolhido);
        $produtosDistintos = (clone $query)->distinct('ean')->count('ean');

        $porLoja = (clone $query)
            ->selectRaw('loja_id, count(*) as total, SUM(recolhido_quantidade) as qtd')
            ->with('loja')
            ->groupBy('loja_id')
            ->orderByDesc('total')
            ->get()
            ->map(fn($item) => [
                'loja_nome' => $item->loja?->nome ?? 'Sem loja',
                'total' => $item->total,
                'quantidade' => (float) $item->qtd,
            ]);

        $itens = (clone $query)
            ->orderByDesc('recolhido_em')
            ->paginate(30);

        $comparativoMensal = $this->comparativoMensal($filters);

        return compact(
            'totalRegistros', 'totalQuantidade', 'totalValor',
            'produtosDistintos', 'porLoja', 'itens', 'comparativoMensal'
        );
    }

    private function comparativoMensal(array $filters): Collection
    {
        $query = Coleta::recolhidos()->with('barcode.product');

        if (!empty($filters['loja_id'])) {
            $query->where('loja_id', $filters['loja_id']);
        }

        $meses = $query
            ->selectRaw("DATE_FORMAT(recolhido_em, '%Y-%m') as mes")
            ->selectRaw('COUNT(*) as total_registros')
            ->selectRaw('SUM(recolhido_quantidade) as total_quantidade')
            ->groupBy('mes')
            ->orderBy('mes')
            ->get()
            ->map(function ($item) {
                $coletasMes = Coleta::recolhidos()
                    ->whereRaw("DATE_FORMAT(recolhido_em, '%Y-%m') = ?", [$item->mes]);
                if (!empty($filters['loja_id'])) {
                    $coletasMes->where('loja_id', $filters['loja_id']);
                }
                $valor = $coletasMes->get()->sum(fn($c) => $c->valor_recolhido);

                return [
                    'mes' => $item->mes,
                    'total_registros' => $item->total_registros,
                    'total_quantidade' => (float) $item->total_quantidade,
                    'total_valor' => round($valor, 2),
                ];
            });

        return $meses;
    }
}
