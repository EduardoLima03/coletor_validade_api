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
    protected function lojaFilter($query)
    {
        $user = auth()->user();
        if ($user->position !== 'ADMIN') {
            $lojaIds = $user->lojasAcessoIds();
            if (!empty($lojaIds)) {
                $query->whereIn("loja_id", $lojaIds);
            }
        }
        return $query;
    }

    protected function lojasDisponiveis()
    {
        return auth()->user()->lojasAcesso();
    }

    public function index(Request $request)
    {
        $query = Coleta::with("loja", "areaAuditoria", "user", "barcode.product");
        $query = $this->lojaFilter($query);

        if ($request->filled("loja_id")) {
            $query->where("loja_id", $request->loja_id);
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

        if ($request->filled("data_coleta_inicio")) {
            $query->whereDate("datahora", ">=", $request->data_coleta_inicio);
        }

        if ($request->filled("data_coleta_fim")) {
            $query->whereDate("datahora", "<=", $request->data_coleta_fim);
        }

        $coletas = $query->orderBy("id")->paginate(50)->appends(request()->query());
        $lojas = $this->lojasDisponiveis();
        $auditores = User::orderBy("name")->get();
        $areas = AreaAuditoria::orderBy("nome")->get();

        $user = auth()->user();
        $podeEditar = $user->podeEditarColeta();
        $podeExcluir = $user->podeExcluirColeta();

        return view("admin.coletas.index", compact(
            "coletas", "lojas", "auditores", "areas", "podeEditar", "podeExcluir"
        ));
    }

    public function exportXlsx(Request $request)
    {
        return Excel::download(
            new ColetasExport(
                $request->loja_id,
                $request->dias,
                $request->data_inicio,
                $request->data_fim,
                auth()->user(),
                $request->user_id,
                $request->ean,
                $request->descricao,
                $request->area_auditoria_id,
                $request->data_coleta_inicio,
                $request->data_coleta_fim
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
                $request->data_fim,
                auth()->user(),
                $request->user_id,
                $request->ean,
                $request->descricao,
                $request->area_auditoria_id,
                $request->data_coleta_inicio,
                $request->data_coleta_fim
            ),
            "coletas.csv"
        );
    }

    public function edit(Coleta $coleta)
    {
        $user = auth()->user();

        if (!$user->podeEditarColeta()) {
            abort(403, 'Você não tem permissão para editar coletas.');
        }

        $lojaIds = $user->lojasAcessoIds();
        if (!empty($lojaIds) && !in_array($coleta->loja_id, $lojaIds)) {
            abort(403, 'Você não tem acesso a esta loja.');
        }

        $returnUrl = request('return_url', route("admin.coletas.index"));

        $coleta->load("loja", "areaAuditoria", "barcode.product");
        $lojas = $this->lojasDisponiveis();
    $areasAuditoria = AreaAuditoria::whereHas("lojas", function ($q) use ($coleta) {
            $q->where("lojas.id", $coleta->loja_id);
        })
        ->orderBy("nome")
        ->get();
        return view("admin.coletas.edit", compact("coleta", "lojas", "areasAuditoria", "returnUrl"));
    }

    public function update(Request $request, Coleta $coleta)
    {
        $user = auth()->user();

        if (!$user->podeEditarColeta()) {
            abort(403, 'Você não tem permissão para editar coletas.');
        }

        $lojaIds = $user->lojasAcessoIds();
        if (!empty($lojaIds) && !in_array($coleta->loja_id, $lojaIds)) {
            abort(403, 'Você não tem acesso a esta loja.');
        }

        $validated = $request->validate([
            "area_auditoria_id" => "nullable|exists:areas_auditoria,id",
            "quantidade" => "required|string|max:50",
            "unidade" => "nullable|string|max:10",
            "data_validade" => "required|date",
        ]);

        $coleta->update($validated);

        AuditLog::log("Editou coleta #$coleta->id - EAN: $coleta->ean", "coleta", $coleta->id);

        $returnUrl = $request->return_url ?? route("admin.coletas.index");

        return redirect($returnUrl)->with("success", "Coleta atualizada com sucesso!");
    }

    public function destroy(Request $request, Coleta $coleta)
    {
        $user = auth()->user();

        if (!$user->podeExcluirColeta()) {
            abort(403, 'Você não tem permissão para excluir coletas.');
        }

        $lojaIds = $user->lojasAcessoIds();
        if (!empty($lojaIds) && !in_array($coleta->loja_id, $lojaIds)) {
            abort(403, 'Você não tem acesso a esta loja.');
        }

        $returnUrl = $request->return_url ?? route("admin.coletas.index");

        $id = $coleta->id;
        $coleta->delete();

        AuditLog::log("Excluiu coleta #$id", "coleta", $id);

        return redirect($returnUrl)->with("success", "Coleta excluída com sucesso!");
    }

    public function trashed()
    {
        $query = Coleta::onlyTrashed()->with("loja", "areaAuditoria", "user", "barcode.product");
        $query = $this->lojaFilter($query);

        $coletas = $query->orderBy("deleted_at", "desc")->paginate(50);
        $lojas = $this->lojasDisponiveis();

        return view("admin.coletas.trashed", compact("coletas", "lojas"));
    }

    public function restore($id)
    {
        $coleta = Coleta::withTrashed()->findOrFail($id);

        $lojaIds = auth()->user()->lojasAcessoIds();
        if (!empty($lojaIds) && !in_array($coleta->loja_id, $lojaIds)) {
            abort(403, 'Você não tem acesso a esta loja.');
        }

        $coleta->restore();

        AuditLog::log("Restaurou coleta #$coleta->id - EAN: $coleta->ean", "coleta", $coleta->id);

        return redirect()->route("admin.coletas.trashed")->with("success", "Coleta restaurada com sucesso!");
    }
}
