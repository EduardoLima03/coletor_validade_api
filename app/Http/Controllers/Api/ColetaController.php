<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Coleta;
use App\Models\Barcode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ColetaController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            "loja_id" => "required|exists:lojas,id",
            "area_auditoria_id" => "nullable|exists:areas_auditoria,id",
            "ean" => "required|string|max:20",
            "quantidade" => "required|integer|min:1",
            "validade" => "required|date",
            "descricao" => "nullable|string|max:255",
            "force" => "nullable|boolean",
        ]);

        $descricao = $validated["descricao"] ?? $this->buscarDescricao($validated["ean"]);

        $force = $validated["force"] ?? false;

        if (!$force) {
            $existing = Coleta::where("loja_id", $validated["loja_id"])
                ->where("area_auditoria_id", $validated["area_auditoria_id"])
                ->where("ean", $validated["ean"])
                ->where("data_validade", $validated["validade"])
                ->first();

            if ($existing) {
                return response()->json([
                    "message" => "Já existe uma coleta com este EAN, área, loja e validade.",
                    "existing" => $existing->load("loja", "user", "areaAuditoria"),
                ], 409);
            }
        }

        $coleta = DB::transaction(function () use ($validated, $descricao, $force) {
            if ($force) {
                Coleta::where("loja_id", $validated["loja_id"])
                    ->where("area_auditoria_id", $validated["area_auditoria_id"])
                    ->where("ean", $validated["ean"])
                    ->where("data_validade", $validated["validade"])
                    ->delete();
            }

            return Coleta::create([
                "loja_id" => $validated["loja_id"],
                "area_auditoria_id" => $validated["area_auditoria_id"],
                "user_id" => auth()->id(),
                "descricao" => $descricao,
                "ean" => $validated["ean"],
                "quantidade" => $validated["quantidade"],
                "data_validade" => $validated["validade"],
            ]);
        });

        return response()->json($coleta->load("loja", "user", "areaAuditoria"), 201);
    }

    public function update(Request $request, $id)
    {
        $coleta = Coleta::findOrFail($id);

        $validated = $request->validate([
            "quantidade" => "required|integer|min:1",
            "validade" => "required|date",
        ]);

        $coleta->update([
            "quantidade" => $validated["quantidade"],
            "data_validade" => $validated["validade"],
        ]);

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

        $existing = Coleta::where("loja_id", $validated["loja_id"])
            ->where("area_auditoria_id", $validated["area_auditoria_id"])
            ->where("ean", $validated["ean"])
            ->where("data_validade", $validated["validade"])
            ->first();

        return response()->json([
            "exists" => $existing !== null,
            "coleta" => $existing ? $existing->load("loja", "user", "areaAuditoria") : null,
        ]);
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
