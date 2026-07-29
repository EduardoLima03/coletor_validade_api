<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\License;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class LicenseController extends Controller
{
    public function index()
    {
        $licenses = License::orderBy('created_at', 'desc')->paginate(20);
        return view('admin.licenses.index', compact('licenses'));
    }

    public function create()
    {
        return view('admin.licenses.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_name' => 'required|string|max:255',
            'cnpj' => 'required|string|max:18|unique:licenses,cnpj',
            'plan' => 'required|in:basic,pro,enterprise',
            'max_stores' => 'required|integer|min:1',
            'max_users' => 'required|integer|min:1',
            'valid_from' => 'required|date',
            'valid_until' => 'required|date|after:valid_from',
        ]);

        $validated['license_key'] = License::generateKey();
        $validated['active'] = true;

        License::create($validated);

        return redirect()->route('admin.licenses.index')
            ->with('success', 'Licença criada com sucesso!');
    }

    public function edit(License $license)
    {
        return view('admin.licenses.edit', compact('license'));
    }

    public function update(Request $request, License $license)
    {
        $validated = $request->validate([
            'client_name' => 'required|string|max:255',
            'cnpj' => 'required|string|max:18|unique:licenses,cnpj,' . $license->id,
            'plan' => 'required|in:basic,pro,enterprise',
            'max_stores' => 'required|integer|min:1',
            'max_users' => 'required|integer|min:1',
            'valid_from' => 'required|date',
            'valid_until' => 'required|date|after:valid_from',
            'active' => 'boolean',
        ]);

        $validated['active'] = $request->boolean('active');

        $license->update($validated);

        return redirect()->route('admin.licenses.index')
            ->with('success', 'Licença atualizada com sucesso!');
    }

    public function destroy(License $license)
    {
        $license->delete();

        return redirect()->route('admin.licenses.index')
            ->with('success', 'Licença removida com sucesso!');
    }
}
