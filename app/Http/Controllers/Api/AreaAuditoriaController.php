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
            $query->whereHas("lojas", function ($q) use ($request) {
                $q->where("lojas.id", $request->loja_id);
            });
        }

        return response()->json($query->paginate(50));
    }
}
