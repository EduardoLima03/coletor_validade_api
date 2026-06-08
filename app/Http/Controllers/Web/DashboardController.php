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
        $query = Coleta::withTrashed();

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

        $metricasUsuarios = (clone $query)
            ->select(
                "user_id",
                DB::raw("count(*) as total_coletas"),
                DB::raw("SUM(quantidade) as total_qtd"),
                DB::raw("COUNT(DISTINCT ean) as total_eans"),
                DB::raw("COUNT(DISTINCT area_auditoria_id) as total_areas"),
                DB::raw("MIN(datahora) as primeiro_registro"),
                DB::raw("MAX(datahora) as ultimo_registro"),
            )
            ->with("user")
            ->groupBy("user_id")
            ->orderByDesc("total_coletas")
            ->get()
            ->map(function ($item) {
                $inicio = $item->primeiro_registro ? \Carbon\Carbon::parse($item->primeiro_registro) : null;
                $fim = $item->ultimo_registro ? \Carbon\Carbon::parse($item->ultimo_registro) : null;
                if ($inicio && $fim) {
                    $diff = $fim->diffInMinutes($inicio);
                    $item->tempo_minutos = $diff;
                    $item->tempo_formatado = floor($diff / 60) . "h " . ($diff % 60) . "min";
                } else {
                    $item->tempo_minutos = 0;
                    $item->tempo_formatado = "-";
                }
                return $item;
            });

        $coletasExcluidas = Coleta::onlyTrashed()->count();

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
            "metricasUsuarios",
            "coletasExcluidas",
            "lojas",
            "auditores",
            "areas",
        ));
    }
}
