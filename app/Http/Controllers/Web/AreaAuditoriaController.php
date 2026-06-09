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
    public function index(Request $request)
    {
        $query = AreaAuditoria::with('lojas');

        if ($request->filled('loja_id')) {
            $query->whereHas('lojas', fn($q) => $q->where('loja_id', $request->loja_id));
        }

        $areas = $query->orderBy('nome')->paginate(20);
        $lojas = Loja::orderBy('nome')->get();

        return view('admin.areas-auditoria.index', compact('areas', 'lojas'));
    }

    public function create()
    {
        $lojas = Loja::orderBy('nome')->get();
        return view('admin.areas-auditoria.create', compact('lojas'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'loja_ids' => 'required|array|min:1',
            'loja_ids.*' => 'exists:lojas,id',
            'nome' => 'required|string|max:255',
            'descricao' => 'nullable|string|max:500',
        ]);

        $existing = AreaAuditoria::where('nome', $validated['nome'])->first();

        if ($existing) {
            return back()->withErrors(['nome' => 'Já existe uma área com este nome.'])->withInput();
        }

        $area = AreaAuditoria::create([
            'nome' => $validated['nome'],
            'descricao' => $validated['descricao'],
        ]);

        $area->lojas()->sync($validated['loja_ids']);

        AuditLog::log('create', 'area_auditoria', $area->id, "Criou área de auditoria: {$area->nome}");

        return redirect()->route('admin.areas-auditoria.index')
            ->with('success', 'Área de auditoria cadastrada com sucesso!');
    }

    public function edit(AreaAuditoria $areaAuditorium)
    {
        $areaAuditorium->load('lojas');
        $lojas = Loja::orderBy('nome')->get();
        return view('admin.areas-auditoria.edit', compact('areaAuditorium', 'lojas'));
    }

    public function update(Request $request, AreaAuditoria $areaAuditorium)
    {
        $validated = $request->validate([
            'loja_ids' => 'required|array|min:1',
            'loja_ids.*' => 'exists:lojas,id',
            'nome' => ['required', 'string', 'max:255'],
            'descricao' => 'nullable|string|max:500',
        ]);

        $existing = AreaAuditoria::where('nome', $validated['nome'])
            ->where('id', '!=', $areaAuditorium->id)
            ->first();

        if ($existing) {
            return back()->withErrors(['nome' => 'Já existe uma área com este nome.'])->withInput();
        }

        $areaAuditorium->update([
            'nome' => $validated['nome'],
            'descricao' => $validated['descricao'],
        ]);

        $areaAuditorium->lojas()->sync($validated['loja_ids']);

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
