<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\RecolhimentoRegra;
use Illuminate\Http\Request;

class RecolhimentoRegraController extends Controller
{
    public function index()
    {
        $regras = RecolhimentoRegra::orderBy('dia_semana')->orderBy('dias_antecedencia')->get();
        return view('admin.recolhimento-regras.index', compact('regras'));
    }

    public function create()
    {
        $diasSemana = RecolhimentoRegra::$diasSemana;
        return view('admin.recolhimento-regras.form', compact('diasSemana'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'dia_semana' => 'required|integer|between:0,6',
            'dias_antecedencia' => 'required|integer|min:1|max:365',
            'ativo' => 'sometimes|boolean',
        ]);

        $validated['ativo'] = $request->boolean('ativo');

        RecolhimentoRegra::create($validated);

        return redirect()->route('admin.recolhimento-regras.index')
            ->with('success', 'Regra criada com sucesso.');
    }

    public function edit(RecolhimentoRegra $recolhimentoRegra)
    {
        $diasSemana = RecolhimentoRegra::$diasSemana;
        return view('admin.recolhimento-regras.form', [
            'regra' => $recolhimentoRegra,
            'diasSemana' => $diasSemana,
        ]);
    }

    public function update(Request $request, RecolhimentoRegra $recolhimentoRegra)
    {
        $validated = $request->validate([
            'dia_semana' => 'required|integer|between:0,6',
            'dias_antecedencia' => 'required|integer|min:1|max:365',
            'ativo' => 'sometimes|boolean',
        ]);

        $validated['ativo'] = $request->boolean('ativo');

        $recolhimentoRegra->update($validated);

        return redirect()->route('admin.recolhimento-regras.index')
            ->with('success', 'Regra atualizada com sucesso.');
    }

    public function destroy(RecolhimentoRegra $recolhimentoRegra)
    {
        $recolhimentoRegra->delete();

        return redirect()->route('admin.recolhimento-regras.index')
            ->with('success', 'Regra excluída com sucesso.');
    }
}
