<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\AreaAuditoria;
use App\Models\Loja;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AreaAuditoriaController extends Controller
{
    public function index()
    {
        $areas = AreaAuditoria::with('loja')->orderBy('loja_id')->orderBy('nome')->paginate(20);
        return view('admin.areas-auditoria.index', compact('areas'));
    }

    public function create()
    {
        $lojas = Loja::orderBy('nome')->get();
        return view('admin.areas-auditoria.create', compact('lojas'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'loja_id' => 'required|exists:lojas,id',
            'nome' => 'required|string|max:255',
            'descricao' => 'nullable|string|max:500',
        ]);

        $existing = AreaAuditoria::where('loja_id', $validated['loja_id'])
            ->where('nome', $validated['nome'])
            ->first();

        if ($existing) {
            return back()->withErrors(['nome' => 'Já existe uma área com este nome nesta loja.'])->withInput();
        }

        $area = AreaAuditoria::create($validated);

        AuditLog::log('create', 'area_auditoria', $area->id, "Criou área de auditoria: {$area->nome}");

        return redirect()->route('admin.areas-auditoria.index')
            ->with('success', 'Área de auditoria cadastrada com sucesso!');
    }

    public function edit(AreaAuditoria $areaAuditorium)
    {
        $lojas = Loja::orderBy('nome')->get();
        return view('admin.areas-auditoria.edit', compact('areaAuditorium', 'lojas'));
    }

    public function update(Request $request, AreaAuditoria $areaAuditorium)
    {
        $validated = $request->validate([
            'loja_id' => 'required|exists:lojas,id',
            'nome' => ['required', 'string', 'max:255'],
            'descricao' => 'nullable|string|max:500',
        ]);

        $existing = AreaAuditoria::where('loja_id', $validated['loja_id'])
            ->where('nome', $validated['nome'])
            ->where('id', '!=', $areaAuditorium->id)
            ->first();

        if ($existing) {
            return back()->withErrors(['nome' => 'Já existe uma área com este nome nesta loja.'])->withInput();
        }

        $areaAuditorium->update($validated);

        AuditLog::log('update', 'area_auditoria', $areaAuditorium->id, "Atualizou área de auditoria: {$areaAuditorium->nome}");

        return redirect()->route('admin.areas-auditoria.index')
            ->with('success', 'Área de auditoria atualizada com sucesso!');
    }

    public function destroy(AreaAuditoria $areaAuditorium)
    {
        $nome = $areaAuditorium->nome;
        $areaAuditorium->delete();

        AuditLog::log('delete', 'area_auditoria', $areaAuditorium->id, "Excluiu área de auditoria: {$nome}");

        return redirect()->route('admin.areas-auditoria.index')
            ->with('success', 'Área de auditoria excluída com sucesso!');
    }
}
