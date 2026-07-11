<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Coleta;
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
            "quantidade" => "required|numeric|min:0",
            "unidade" => "nullable|string|max:10",
            "validade" => "required|date",
            "action" => "nullable|in:replace,add",
        ]);

        $action = $validated["action"] ?? null;

        $areaAuditoriaId = $validated["area_auditoria_id"] ?? null;

        $existing = Coleta::withTrashed()
            ->where("loja_id", $validated["loja_id"])
            ->where("area_auditoria_id", $areaAuditoriaId)
            ->where("ean", $validated["ean"])
            ->where("data_validade", $validated["validade"])
            ->whereNull("recolhido_em")
            ->first();

        if (!$action && $existing) {
            return response()->json([
                "message" => "Já existe uma coleta com este EAN, área, loja e validade.",
                "existing" => $existing->load("loja", "user", "areaAuditoria", "barcode.product"),
            ], 409);
        }

        $coleta = DB::transaction(function () use ($validated, $action, $existing, $areaAuditoriaId) {
            if ($action === "replace" && $existing) {
                $oldQty = $existing->quantidade;

                if ((float) $validated["quantidade"] == 0) {
                    $existing->delete();
                    $lojaNome = $this->lojaNome($validated['loja_id']);
                    AuditLog::log(
                        "coleta.replace",
                        "Coleta",
                        $existing->id,
                        "Removeu coleta ID {$existing->id} (qty 0): EAN {$validated['ean']}, "
                        . "loja {$lojaNome}, quantidade {$oldQty} → 0"
                    );
                    return $existing->fresh()->load("loja", "user", "areaAuditoria", "barcode.product");
                }

                if ($existing->trashed()) {
                    $existing->restore();
                }

                $existing->update([
                    "quantidade" => $validated["quantidade"],
                    "unidade" => $validated["unidade"] ?? "un",
                    "user_id" => auth()->id(),
                    "datahora" => now(),
                ]);

                $lojaNome = $this->lojaNome($validated['loja_id']);
                AuditLog::log(
                    "coleta.replace",
                    "Coleta",
                    $existing->id,
                    "Substituiu coleta ID {$existing->id}: EAN {$validated['ean']}, loja {$lojaNome}, "
                    . "quantidade {$oldQty} → {$validated['quantidade']}, validade {$validated['validade']}"
                );

                return $existing->fresh()->load("loja", "user", "areaAuditoria", "barcode.product");
            }

            if ($action === "add" && $existing) {
                if ((float) $validated["quantidade"] == 0) {
                    $existing->delete();
                    $lojaNome = $this->lojaNome($validated['loja_id']);
                    AuditLog::log(
                        "coleta.add",
                        "Coleta",
                        $existing->id,
                        "Removeu coleta ID {$existing->id} (qty 0): EAN {$validated['ean']}, "
                        . "loja {$lojaNome}"
                    );
                    return $existing->fresh()->load("loja", "user", "areaAuditoria", "barcode.product");
                }

                if ($existing->trashed()) {
                    $existing->restore();
                    $existing->update([
                        "quantidade" => $validated["quantidade"],
                        "unidade" => $validated["unidade"] ?? "un",
                        "datahora" => now(),
                    ]);
                } else {
                    $oldQty = (float) $existing->quantidade;
                    $newQty = $oldQty + (float) $validated["quantidade"];
                    $existing->update([
                        "quantidade" => $newQty,
                        "unidade" => $validated["unidade"] ?? "un",
                        "datahora" => now(),
                    ]);
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
                "area_auditoria_id" => $areaAuditoriaId,
                "user_id" => auth()->id(),
                "ean" => $validated["ean"],
                "quantidade" => $validated["quantidade"],
                "unidade" => $validated["unidade"] ?? "un",
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
        return response()->json($coleta->load("loja", "user", "areaAuditoria", "barcode.product"), $statusCode);
    }

    public function update(Request $request, $id)
    {
        $coleta = Coleta::findOrFail($id);

        $validated = $request->validate([
            "quantidade" => "required|numeric|min:0",
            "unidade" => "nullable|string|max:10",
            "validade" => "required|date",
        ]);

        if ((float) $validated["quantidade"] == 0) {
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
            "unidade" => $validated["unidade"] ?? $coleta->unidade ?? "un",
            "data_validade" => $validated["validade"],
        ]);

        AuditLog::log(
            "coleta.update",
            "Coleta",
            $coleta->id,
            "Atualizou coleta ID {$coleta->id}: EAN {$coleta->ean}, "
            . "quantidade {$oldQty} → {$validated['quantidade']}, validade {$validated['validade']}"
        );

        return response()->json($coleta->load("loja", "user", "areaAuditoria", "barcode.product"));
    }

    public function check(Request $request)
    {
        $validated = $request->validate([
            "loja_id" => "required|exists:lojas,id",
            "area_auditoria_id" => "nullable|exists:areas_auditoria,id",
            "ean" => "required|string|max:20",
            "validade" => "required|date",
        ]);

        $areaAuditoriaId = $validated["area_auditoria_id"] ?? null;

        $existing = Coleta::withTrashed()
            ->where("loja_id", $validated["loja_id"])
            ->where("area_auditoria_id", $areaAuditoriaId)
            ->where("ean", $validated["ean"])
            ->where("data_validade", $validated["validade"])
            ->whereNull("recolhido_em")
            ->first();

        return response()->json([
            "exists" => $existing !== null,
            "trashed" => $existing ? $existing->trashed() : false,
            "coleta" => $existing ? $existing->load("loja", "user", "areaAuditoria", "barcode.product") : null,
        ]);
    }

    public function trashed()
    {
        $coletas = Coleta::onlyTrashed()
            ->with("loja", "areaAuditoria", "user", "barcode.product")
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

        return response()->json($coleta->load("loja", "user", "areaAuditoria", "barcode.product"));
    }

}
