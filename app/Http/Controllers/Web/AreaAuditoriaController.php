<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\AreaAuditoria;
use App\Models\Loja;
use App\Models\AuditLog;
use App\Models\Coleta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AreaAuditoriaController extends Controller
{
    public function show($id)
    {
        return redirect()->route('admin.areas-auditoria.index');
    }

    public function index(Request $request)
    {
        $query = AreaAuditoria::with('lojas');

        if ($request->filled('loja_id')) {
            $query->whereHas('lojas', function ($q) use ($request) {
                $q->where('lojas.id', $request->loja_id);
            });
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
        $returnUrl = request('return_url', route('admin.areas-auditoria.index'));
        $areaAuditorium->load('lojas');
        $lojas = Loja::orderBy('nome')->get();
        return view('admin.areas-auditoria.edit', compact('areaAuditorium', 'lojas', 'returnUrl'));
    }

    public function update(Request $request, AreaAuditoria $areaAuditorium)
    {
        $validated = $request->validate([
            'loja_ids' => 'required|array|min:1',
            'loja_ids.*' => 'exists:lojas,id',
            'nome' => 'required|string|max:255',
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

        $returnUrl = $request->return_url ?? route('admin.areas-auditoria.index');

        return redirect($returnUrl)
            ->with('success', 'Área de auditoria atualizada com sucesso!');
    }

    public function destroy(Request $request, AreaAuditoria $areaAuditorium)
    {
        $nome = $areaAuditorium->nome;

        $areaAuditorium->lojas()->detach();
        $areaAuditorium->delete();

        AuditLog::log('delete', 'area_auditoria', $areaAuditorium->id, "Excluiu área de auditoria: {$nome}");

        $returnUrl = $request->return_url ?? route('admin.areas-auditoria.index');

        return redirect($returnUrl)
            ->with('success', 'Área de auditoria excluída com sucesso!');
    }

    public function mergeDuplicates(Request $request)
    {
        $duplicates = AreaAuditoria::select('nome', DB::raw('COUNT(*) as total'))
            ->groupBy('nome')
            ->having('total', '>', 1)
            ->get();

        $merged = 0;

        DB::transaction(function () use ($duplicates, &$merged) {
            foreach ($duplicates as $group) {
                $areas = AreaAuditoria::where('nome', $group->nome)
                    ->orderBy('id')
                    ->get();

                $keep = $areas->shift();
                $deleteIds = $areas->pluck('id');

                $allLojaIds = DB::table('area_auditoria_loja')
                    ->whereIn('area_auditoria_id', $deleteIds->merge([$keep->id]))
                    ->distinct()
                    ->pluck('loja_id');

                $keep->lojas()->sync($allLojaIds);

                Coleta::whereIn('area_auditoria_id', $deleteIds)
                    ->update(['area_auditoria_id' => $keep->id]);

                AreaAuditoria::whereIn('id', $deleteIds)->each(function ($area) {
                    $area->lojas()->detach();
                    $area->delete();
                });

                $merged += count($deleteIds);
            }
        });

        AuditLog::log('merge', 'area_auditoria', 0, "Mesclou {$merged} áreas de auditoria duplicadas");

        return redirect()->route('admin.areas-auditoria.index')
            ->with('success', "{$merged} áreas duplicadas mescladas com sucesso!");
    }
}