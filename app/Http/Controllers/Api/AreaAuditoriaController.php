<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AreaAuditoria;
use Illuminate\Http\Request;

class AreaAuditoriaController extends Controller
{
    public function index(Request $request)
    {
        $query = AreaAuditoria::orderBy("nome");

        if ($request->filled("loja_id")) {
            $query->where("loja_id", $request->loja_id);
        }

        return response()->json($query->get());
    }
}
