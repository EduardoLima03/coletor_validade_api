<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Loja;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LojaController extends Controller
{
    public function index()
    {
        $lojas = Loja::orderBy("nome")->paginate(20)->withQueryString();
        return view("admin.lojas.index", compact("lojas"));
    }

    public function create()
    {
        return view("admin.lojas.create");
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            "nome" => "required|string|max:255|unique:lojas,nome",
        ]);

        $loja = Loja::create($validated);

        AuditLog::log("Criou a loja: " . $loja->nome, "loja", $loja->id);

        return redirect()->route("admin.lojas.index")->with("success", "Loja cadastrada com sucesso!");
    }

    public function edit(Loja $loja)
    {
        $returnUrl = request('return_url', route('admin.lojas.index'));
        return view("admin.lojas.edit", compact("loja", "returnUrl"));
    }

    public function update(Request $request, Loja $loja)
    {
        $validated = $request->validate([
            "nome" => ["required", "string", "max:255", Rule::unique("lojas", "nome")->ignore($loja->id)],
        ]);

        $loja->update($validated);

        AuditLog::log("Editou a loja: " . $loja->nome, "loja", $loja->id);

        $returnUrl = $request->return_url ?? route('admin.lojas.index');

        return redirect($returnUrl)->with("success", "Loja atualizada com sucesso!");
    }

    public function destroy(Request $request, Loja $loja)
    {
        $nome = $loja->nome;
        $id = $loja->id;
        $loja->delete();

        AuditLog::log("Excluiu a loja: " . $nome, "loja", $id);

        $returnUrl = $request->return_url ?? route('admin.lojas.index');

        return redirect($returnUrl)->with("success", "Loja excluída com sucesso!");
    }
}
