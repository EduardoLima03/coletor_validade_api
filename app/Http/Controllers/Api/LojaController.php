<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Loja;

class LojaController extends Controller
{
    public function index()
    {
        return Loja::orderBy('nome')->paginate(50, ['id', 'nome']);
    }
}
