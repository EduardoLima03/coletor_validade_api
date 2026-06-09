<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AreaAuditoria;
use Illuminate\Http\Request;

class AreaAuditoriaController extends Controller
{
    public function index(Request $request)
    {
        $query = AreaAuditoria::with("lojas")->orderBy("nome");

        if ($request->filled("loja_id")) {
            $query->whereHas("lojas", fn($q) => $q->where("loja_id", $request->loja_id));
        }

        $areas = $query->get();

        $areas->each(function ($area) {
            $area->loja_id = $area->lojas->first()?->id;
        });

        return response()->json($areas);
    }
}
