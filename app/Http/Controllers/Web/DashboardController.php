<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Coleta;
use App\Models\Loja;
use App\Models\AreaAuditoria;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $query = Coleta::query();

        $user = auth()->user();
        if ($user->position !== 'ADMIN') {
            $lojaIds = $user->lojasAcessoIds();
            if (!empty($lojaIds)) {
                $query->whereIn("loja_id", $lojaIds);
            }
        }

        if ($request->filled("loja_id")) {
            $query->where("loja_id", $request->loja_id);
        }

        if ($request->filled("user_id")) {
            $query->where("user_id", $request->user_id);
        }

        if ($request->filled("area_auditoria_id")) {
            $query->where("area_auditoria_id", $request->area_auditoria_id);
        }

        if ($request->filled("dias")) {
            $dias = (int) $request->dias;
            $query->whereBetween("data_validade", [now()->addDay(), now()->addDays($dias)]);
        }

        if ($request->filled("data_inicio")) {
            $query->whereDate("data_validade", ">=", $request->data_inicio);
        }

        if ($request->filled("data_fim")) {
            $query->whereDate("data_validade", "<=", $request->data_fim);
        }

        $totalColetas = (clone $query)->count();
        $coletasVencidas = (clone $query)->whereDate("data_validade", "<", now())->count();
        $coletasAte5 = (clone $query)->whereBetween("data_validade", [now(), now()->addDays(5)])->count();
        $coletasAte15 = (clone $query)->whereBetween("data_validade", [now(), now()->addDays(15)])->count();
        $produtosDistintos = (clone $query)->distinct("descricao")->count("descricao");
        $eansDistintos = (clone $query)->distinct("ean")->count("ean");

        $coletasPorLoja = (clone $query)
            ->select("loja_id", DB::raw("count(*) as total"))
            ->with("loja")
            ->groupBy("loja_id")
            ->orderByDesc("total")
            ->get();

        $coletasPorAuditor = (clone $query)
            ->select("user_id", DB::raw("count(*) as total"))
            ->with("user")
            ->groupBy("user_id")
            ->orderByDesc("total")
            ->get();

        $ultimasColetas = (clone $query)
            ->with("loja", "user", "areaAuditoria")
            ->orderByDesc("id")
            ->limit(10)
            ->get();

        $lojas = $user->lojasAcesso();
        $auditores = User::orderBy("name")->get();
        $areas = AreaAuditoria::orderBy("nome")->get();

        return view("admin.dashboard", compact(
            "totalColetas",
            "coletasVencidas",
            "coletasAte5",
            "coletasAte15",
            "produtosDistintos",
            "eansDistintos",
            "coletasPorLoja",
            "coletasPorAuditor",
            "ultimasColetas",
            "lojas",
            "auditores",
            "areas",
        ));
    }
}
