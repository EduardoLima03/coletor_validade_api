<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Coleta;
use App\Models\Barcode;
use App\Models\Loja;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ColetaController extends Controller
{
    private function lojaNome($lojaId): string
    {
        static $cache = [];
        if (!isset($cache[$lojaId])) {
            $loja = Loja::find($lojaId);
            $cache[$lojaId] = $loja?->nome ?? "#$lojaId";
        }
        return $cache[$lojaId];
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            "loja_id" => "required|exists:lojas,id",
            "area_auditoria_id" => "nullable|exists:areas_auditoria,id",
            "ean" => "required|string|max:20",
            "quantidade" => "required|integer|min:0",
            "validade" => "required|date",
            "descricao" => "nullable|string|max:255",
            "action" => "nullable|in:replace,add",
        ]);

        $descricao = $validated["descricao"] ?? $this->buscarDescricao($validated["ean"]);
        $action = $validated["action"] ?? null;

        $existing = Coleta::withTrashed()
            ->where("loja_id", $validated["loja_id"])
            ->where("area_auditoria_id", $validated["area_auditoria_id"])
            ->where("ean", $validated["ean"])
            ->where("data_validade", $validated["validade"])
            ->first();

        if (!$action && $existing) {
            return response()->json([
                "message" => "Já existe uma coleta com este EAN, área, loja e validade.",
                "existing" => $existing->load("loja", "user", "areaAuditoria"),
            ], 409);
        }

        $coleta = DB::transaction(function () use ($validated, $descricao, $action, $existing) {
            if ($action === "replace" && $existing) {
                $oldQty = $existing->quantidade;

                if ($validated["quantidade"] == 0) {
                    $existing->delete();
                    $lojaNome = $this->lojaNome($validated['loja_id']);
                    AuditLog::log(
                        "coleta.replace",
                        "Coleta",
                        $existing->id,
                        "Removeu coleta ID {$existing->id} (qty 0): EAN {$validated['ean']}, "
                        . "loja {$lojaNome}, quantidade {$oldQty} → 0"
                    );
                    return $existing->fresh()->load("loja", "user", "areaAuditoria");
                }

                if ($existing->trashed()) {
                    $existing->restore();
                }

                $existing->update(["quantidade" => $validated["quantidade"]]);

                $lojaNome = $this->lojaNome($validated['loja_id']);
                AuditLog::log(
                    "coleta.replace",
                    "Coleta",
                    $existing->id,
                    "Substituiu coleta ID {$existing->id}: EAN {$validated['ean']}, loja {$lojaNome}, "
                    . "quantidade {$oldQty} → {$validated['quantidade']}, validade {$validated['validade']}"
                );

                return $existing->fresh()->load("loja", "user", "areaAuditoria");
            }

            if ($action === "add" && $existing) {
                if ($existing->trashed()) {
                    $existing->restore();
                    $existing->update(["quantidade" => $validated["quantidade"]]);
                } else {
                    $oldQty = $existing->quantidade;
                    $newQty = $oldQty + $validated["quantidade"];
                    $existing->update(["quantidade" => $newQty]);
                }

                $lojaNome = $this->lojaNome($validated['loja_id']);
                AuditLog::log(
                    "coleta.add",
                    "Coleta",
                    $existing->id,
                    "Adicionou quantidade à coleta ID {$existing->id}: EAN {$validated['ean']}, "
                    . "loja {$lojaNome}, validade {$validated['validade']}"
                );

                return $existing->fresh()->load("loja", "user", "areaAuditoria");
            }

            $nova = Coleta::create([
                "loja_id" => $validated["loja_id"],
                "area_auditoria_id" => $validated["area_auditoria_id"],
                "user_id" => auth()->id(),
                "descricao" => $descricao,
                "ean" => $validated["ean"],
                "quantidade" => $validated["quantidade"],
                "data_validade" => $validated["validade"],
                "datahora" => now(),
            ]);

            $lojaNome = $this->lojaNome($validated['loja_id']);
            AuditLog::log(
                "coleta.create",
                "Coleta",
                $nova->id,
                "Criou coleta: EAN {$validated['ean']}, loja {$lojaNome}, "
                . "quantidade {$validated['quantidade']}, validade {$validated['validade']}"
            );

            return $nova;
        });

        $statusCode = $action ? 200 : 201;
        return response()->json($coleta->load("loja", "user", "areaAuditoria"), $statusCode);
    }

    public function update(Request $request, $id)
    {
        $coleta = Coleta::findOrFail($id);

        $validated = $request->validate([
            "quantidade" => "required|integer|min:0",
            "validade" => "required|date",
        ]);

        if ($validated["quantidade"] === 0) {
            $coleta->delete();

            $lojaNome = $this->lojaNome($coleta->loja_id);
            AuditLog::log(
                "coleta.delete",
                "Coleta",
                $coleta->id,
                "Removeu coleta ID {$coleta->id} (qty 0): EAN {$coleta->ean}, "
                . "loja {$lojaNome}"
            );

            return response()->json(["message" => "Coleta removida", "coleta" => $coleta]);
        }

        $oldQty = $coleta->quantidade;
        $coleta->update([
            "quantidade" => $validated["quantidade"],
            "data_validade" => $validated["validade"],
        ]);

        AuditLog::log(
            "coleta.update",
            "Coleta",
            $coleta->id,
            "Atualizou coleta ID {$coleta->id}: EAN {$coleta->ean}, "
            . "quantidade {$oldQty} → {$validated['quantidade']}, validade {$validated['validade']}"
        );

        return response()->json($coleta->load("loja", "user", "areaAuditoria"));
    }

    public function check(Request $request)
    {
        $validated = $request->validate([
            "loja_id" => "required|exists:lojas,id",
            "area_auditoria_id" => "nullable|exists:areas_auditoria,id",
            "ean" => "required|string|max:20",
            "validade" => "required|date",
        ]);

        $existing = Coleta::withTrashed()
            ->where("loja_id", $validated["loja_id"])
            ->where("area_auditoria_id", $validated["area_auditoria_id"])
            ->where("ean", $validated["ean"])
            ->where("data_validade", $validated["validade"])
            ->first();

        return response()->json([
            "exists" => $existing !== null,
            "trashed" => $existing ? $existing->trashed() : false,
            "coleta" => $existing ? $existing->load("loja", "user", "areaAuditoria") : null,
        ]);
    }

    public function trashed()
    {
        $coletas = Coleta::onlyTrashed()
            ->with("loja", "areaAuditoria", "user")
            ->orderBy("deleted_at", "desc")
            ->paginate(50);

        return response()->json($coletas);
    }

    public function restore($id)
    {
        $coleta = Coleta::withTrashed()->findOrFail($id);
        $coleta->restore();

        $lojaNome = $this->lojaNome($coleta->loja_id);
        AuditLog::log(
            "coleta.restore",
            "Coleta",
            $coleta->id,
            "Restaurou coleta ID {$coleta->id}: EAN {$coleta->ean}, "
            . "loja {$lojaNome}, quantidade {$coleta->quantidade}"
        );

        return response()->json($coleta->load("loja", "user", "areaAuditoria"));
    }

    private function buscarDescricao($ean)
    {
        $barcode = Barcode::where("ean", $ean)->with("product")->first();
        if ($barcode && $barcode->product) {
            return $barcode->product->description;
        }
        return "Produto não encontrado";
    }
}
