<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\AreaAuditoria;
use App\Models\Loja;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class AreaAuditoriaController extends Controller
{
    public function show($id)
    {
        return redirect()->route('admin.areas-auditoria.index');
    }

    public function index(Request $request)
    {
        $query = AreaAuditoria::with('loja');

        if ($request->filled('loja_id')) {
            $query->where('loja_id', $request->loja_id);
        }

        $areas = $query->orderBy('nome')->paginate(20)->withQueryString();
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
            'loja_id' => 'required|exists:lojas,id',
            'nome' => 'required|string|max:255',
            'descricao' => 'nullable|string|max:500',
        ]);

        $existing = AreaAuditoria::where('nome', $validated['nome'])->first();

        if ($existing) {
            return back()->withErrors(['nome' => 'Já existe uma área com este nome.'])->withInput();
        }

        $area = AreaAuditoria::create($validated);

        AuditLog::log('create', 'area_auditoria', $area->id, "Criou área de auditoria: {$area->nome}");

        return redirect()->route('admin.areas-auditoria.index')
            ->with('success', 'Área de auditoria cadastrada com sucesso!');
    }

    public function edit(AreaAuditoria $areaAuditorium)
    {
        $returnUrl = request('return_url', route('admin.areas-auditoria.index'));
        $areaAuditorium->load('loja');
        $lojas = Loja::orderBy('nome')->get();
        return view('admin.areas-auditoria.edit', compact('areaAuditorium', 'lojas', 'returnUrl'));
    }

    public function update(Request $request, AreaAuditoria $areaAuditorium)
    {
        $validated = $request->validate([
            'loja_id' => 'required|exists:lojas,id',
            'nome' => 'required|string|max:255',
            'descricao' => 'nullable|string|max:500',
        ]);

        $existing = AreaAuditoria::where('nome', $validated['nome'])
            ->where('id', '!=', $areaAuditorium->id)
            ->first();

        if ($existing) {
            return back()->withErrors(['nome' => 'Já existe uma área com este nome.'])->withInput();
        }

        $areaAuditorium->update($validated);

        AuditLog::log('update', 'area_auditoria', $areaAuditorium->id, "Atualizou área de auditoria: {$areaAuditorium->nome}");

        $returnUrl = $request->return_url ?? route('admin.areas-auditoria.index');

        return redirect($returnUrl)
            ->with('success', 'Área de auditoria atualizada com sucesso!');
    }

    public function destroy(Request $request, AreaAuditoria $areaAuditorium)
    {
        $nome = $areaAuditorium->nome;

        $areaAuditorium->delete();

        AuditLog::log('delete', 'area_auditoria', $areaAuditorium->id, "Excluiu área de auditoria: {$nome}");

        $returnUrl = $request->return_url ?? route('admin.areas-auditoria.index');

        return redirect($returnUrl)
            ->with('success', 'Área de auditoria excluída com sucesso!');
    }
}
