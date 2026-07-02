<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\RecolhimentoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RecolhimentoController extends Controller
{
    public function __construct(
        protected RecolhimentoService $recolhimentoService
    ) {}

    public function produtos(int $lojaId): JsonResponse
    {
        $produtos = $this->recolhimentoService->produtosParaRecolher($lojaId);
        $valorTotal = $produtos->sum(fn($c) => ($c->barcode?->product?->custo ?? 0) * (float) $c->quantidade);

        return response()->json([
            'produtos' => $produtos->map(fn($c) => [
                'id' => $c->id,
                'ean' => $c->ean,
                'descricao' => $c->product_name,
                'quantidade' => $c->quantidade,
                'unidade' => $c->unidade,
                'data_validade' => $c->data_validade?->format('Y-m-d'),
                'dias_a_vencer' => $c->dias_a_vencer,
                'area_auditoria' => $c->areaAuditoria?->nome,
                'custo' => (float) ($c->barcode?->product?->custo ?? 0),
            ]),
            'total' => $produtos->count(),
            'valor_total' => round($valorTotal, 2),
        ]);
    }

    public function pendentes(int $lojaId): JsonResponse
    {
        $produtos = $this->recolhimentoService->produtosParaRecolher($lojaId);
        $total = $produtos->count();

        return response()->json([
            'total' => $total,
            'tem_pendentes' => $total > 0,
            'produtos' => $produtos->take(5)->map(fn($c) => [
                'descricao' => $c->product_name,
                'data_validade' => $c->data_validade?->format('d/m/Y'),
                'dias_a_vencer' => $c->dias_a_vencer,
            ]),
        ]);
    }

    public function registrar(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'coleta_id' => 'required|exists:coletas,id',
            'quantidade' => 'required|numeric|min:0',
        ]);

        $coleta = $this->recolhimentoService->registrarRecolhimento(
            $validated['coleta_id'],
            (float) $validated['quantidade'],
            auth()->id()
        );

        return response()->json([
            'message' => $validated['quantidade'] > 0
                ? 'Recolhimento registrado com sucesso.'
                : 'Item removido (quantidade zero).',
            'coleta' => [
                'id' => $coleta->id,
                'recolhido_em' => $coleta->recolhido_em?->toISOString(),
                'recolhido_quantidade' => $coleta->recolhido_quantidade,
                'deleted_at' => $coleta->deleted_at,
            ],
        ]);
    }
}
