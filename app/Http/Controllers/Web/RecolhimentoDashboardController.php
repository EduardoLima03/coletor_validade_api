<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Loja;
use App\Models\User;
use App\Models\AreaAuditoria;
use App\Services\RecolhimentoService;
use Illuminate\Http\Request;

class RecolhimentoDashboardController extends Controller
{
    public function __construct(
        protected RecolhimentoService $recolhimentoService
    ) {}

    public function index(Request $request)
    {
        $user = auth()->user();
        $filters = $request->only(['loja_id', 'user_id', 'area_auditoria_id', 'data_inicio', 'data_fim']);

        if ($user->position !== 'ADMIN') {
            $lojaIds = $user->lojasAcessoIds();

            if (!empty($filters['loja_id'])) {
                if (is_array($filters['loja_id'])) {
                    $filters['loja_id'] = array_intersect($filters['loja_id'], $lojaIds);
                } elseif (!in_array($filters['loja_id'], $lojaIds)) {
                    $filters['loja_id'] = [];
                }
            }

            if (empty($filters['loja_id']) && !empty($lojaIds)) {
                $filters['loja_id'] = $lojaIds;
            }
        }

        $metrics = $this->recolhimentoService->metrics($filters);

        $lojas = $user->position === 'ADMIN'
            ? Loja::orderBy('nome')->get()
            : $user->lojasAcesso();

        $auditores = User::orderBy('name')->get();
        $areas = AreaAuditoria::orderBy('nome')->get();

        return view('admin.recolhimento.dashboard', array_merge(
            $metrics,
            compact('lojas', 'auditores', 'areas', 'filters')
        ));
    }
}
