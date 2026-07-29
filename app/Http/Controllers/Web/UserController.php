<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Loja;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');

        $query = User::query();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('position', 'like', "%{$search}%");
            });
        }

        $users = $query->orderBy('name')->paginate(15)->withQueryString();

        return view('admin.users.index', compact('users', 'search'));
    }

    public function create()
    {
        $lojas = Loja::orderBy('nome')->get();
        return view('admin.users.create', compact('lojas'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'position' => 'required|string|in:ADMIN,GERENCIA,COLETOR',
            'password' => 'required|string|min:6|confirmed',
            'coleta_edit' => 'nullable|boolean',
            'coleta_delete' => 'nullable|boolean',
            'lojas' => 'nullable|array',
            'lojas.*' => 'exists:lojas,id',
        ]);

        $data['password'] = Hash::make($data['password']);

        $user = User::create($data);

        if ($request->filled('lojas')) {
            $user->lojas()->sync($request->lojas);
        }

        AuditLog::log('create', 'user', $user->id, "Criou usuário {$user->name} ({$user->email}) como {$user->position}");

        return redirect()->route('admin.users.index')
            ->with('success', 'Usuário cadastrado com sucesso.');
    }

    public function show($id)
    {
        $user = User::with('lojas')->findOrFail($id);
        return view('admin.users.show', compact('user'));
    }

    public function edit($id)
    {
        $returnUrl = request('return_url', route('admin.users.index'));
        $user = User::with('lojas')->findOrFail($id);
        $lojas = Loja::orderBy('nome')->get();
        return view('admin.users.edit', compact('user', 'lojas', 'returnUrl'));
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'position' => 'required|string|in:ADMIN,GERENCIA,COLETOR',
            'password' => 'nullable|string|min:6|confirmed',
            'coleta_edit' => 'nullable|boolean',
            'coleta_delete' => 'nullable|boolean',
            'lojas' => 'nullable|array',
            'lojas.*' => 'exists:lojas,id',
        ]);

        if ($data['password'] ?? false) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $user->update($data);

        if ($request->has('lojas')) {
            $user->lojas()->sync($request->lojas ?? []);
        }

        AuditLog::log('update', 'user', $user->id, "Atualizou usuário {$user->name} ({$user->email})");

        $returnUrl = $request->return_url ?? route('admin.users.index');

        return redirect($returnUrl)
            ->with('success', 'Usuário atualizado com sucesso.');
    }

    public function destroy(Request $request, $id)
    {
        $user = User::findOrFail($id);

        if ($user->id === auth()->id()) {
            return redirect()->route('admin.users.index')
                ->with('error', 'Você não pode excluir o próprio usuário.');
        }

        $name = $user->name;
        $user->delete();

        AuditLog::log('delete', 'user', $id, "Deletou usuário {$name}");

        $returnUrl = $request->return_url ?? route('admin.users.index');

        return redirect($returnUrl)
            ->with('success', 'Usuário deletado com sucesso.');
    }
}
