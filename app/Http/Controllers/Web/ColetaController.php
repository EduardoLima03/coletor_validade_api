<?php

namespace App\Http\Controllers\Web;

use App\Exports\ColetasExport;
use App\Http\Controllers\Controller;
use App\Models\AreaAuditoria;
use App\Models\Coleta;
use App\Models\Loja;
use App\Models\User;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ColetaController extends Controller
{
    public function index(Request $request)
    {
        $query = Coleta::with("loja", "areaAuditoria", "user");

        if ($request->filled("loja_id")) {
            $query->where("loja_id", $request->loja_id);
        }

        if ($request->filled("dias")) {
            $dias = (int) $request->dias;
            $query->whereDate("data_validade", "<=", now()->addDays($dias));
        }

        if ($request->filled("data_inicio")) {
            $query->whereDate("data_validade", ">=", $request->data_inicio);
        }

        if ($request->filled("data_fim")) {
            $query->whereDate("data_validade", "<=", $request->data_fim);
        }

        if ($request->filled("user_id")) {
            $query->where("user_id", $request->user_id);
        }

        if ($request->filled("ean")) {
            $query->where("ean", "like", "%{$request->ean}%");
        }

        if ($request->filled("descricao")) {
            $query->where("descricao", "like", "%{$request->descricao}%");
        }

        if ($request->filled("area_auditoria_id")) {
            $query->where("area_auditoria_id", $request->area_auditoria_id);
        }

        $coletas = $query->orderBy("id")->paginate(50)->appends(request()->query());
        $lojas = Loja::orderBy("nome")->get();
        $auditores = User::orderBy("name")->get();
        $areas = AreaAuditoria::orderBy("nome")->get();

        return view("admin.coletas.index", compact("coletas", "lojas", "auditores", "areas"));
    }

    public function exportXlsx(Request $request)
    {
        return Excel::download(
            new ColetasExport(
                $request->loja_id,
                $request->dias,
                $request->data_inicio,
                $request->data_fim
            ),
            "coletas.xlsx"
        );
    }

    public function exportCsv(Request $request)
    {
        return Excel::download(
            new ColetasExport(
                $request->loja_id,
                $request->dias,
                $request->data_inicio,
                $request->data_fim
            ),
            "coletas.csv"
        );
    }

    public function edit(Coleta $coleta)
    {
        $coleta->load("loja", "areaAuditoria");
        $lojas = Loja::orderBy("nome")->get();
        $areasAuditoria = AreaAuditoria::where("loja_id", $coleta->loja_id)
            ->orderBy("nome")
            ->get();
        return view("admin.coletas.edit", compact("coleta", "lojas", "areasAuditoria"));
    }

    public function update(Request $request, Coleta $coleta)
    {
        $validated = $request->validate([
            "area_auditoria_id" => "nullable|exists:areas_auditoria,id",
            "quantidade" => "required|integer|min:1",
            "data_validade" => "required|date",
        ]);

        $coleta->update($validated);

        AuditLog::log("Editou coleta #$coleta->id - EAN: $coleta->ean", "coleta", $coleta->id);

        return redirect()->route("admin.coletas.index")->with("success", "Coleta atualizada com sucesso!");
    }

    public function destroy(Coleta $coleta)
    {
        $id = $coleta->id;
        $coleta->delete();

        AuditLog::log("Excluiu coleta #$id", "coleta", $id);

        return redirect()->route("admin.coletas.index")->with("success", "Coleta excluída com sucesso!");
    }
}
