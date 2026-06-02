<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Coleta;
use App\Models\Loja;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        $totalProdutos = Product::count();
        $totalColetas = Coleta::count();
        $totalLojas = Loja::count();
        $totalUsuarios = User::count();

        return view("admin.dashboard", compact(
            "totalProdutos", "totalColetas", "totalLojas", "totalUsuarios"
        ));
    }
}
