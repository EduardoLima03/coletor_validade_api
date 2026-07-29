<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
USE Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            $user = Auth::user();

            if ($user->tokens()->exists()) {
                $user->tokens()->delete();
            }

            $token = $user->createToken('JWT');

            return response()->json([
                'token' => $token->plainTextToken,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'position' => $user->position,
                ],
            ], 200);
        }

        return response()->json(['error' => 'Credenciais inválidas'], 401);
    }

    public function store(Request $request, User $user)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'position' => 'required|in:COLETOR,GERENCIA,ADMIN',
        ]);

        $data['password'] = Hash::make($data['password']);

        if (!$user = $user->create($data)) {
            abort(500, 'Erro ao criar usuário');
        }

        return response()->json(['success' => 'Usuário criado com sucesso.', 'user' => $user], 201);
    }

    public function updateProfile(Request $request)
    {
        $user = auth()->user();

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'password' => 'nullable|string|min:6|confirmed',
        ]);

        $updateData = ['name' => $data['name']];

        if (!empty($data['password'])) {
            $updateData['password'] = Hash::make($data['password']);
        }

        $user->update($updateData);

        return response()->json([
            'success' => 'Perfil atualizado com sucesso.',
            'user' => ['id' => $user->id, 'name' => $user->name, 'email' => $user->email, 'position' => $user->position]
        ], 200);
    }

    public function update(Request $request, $id)
    {
        try {
            $user = User::findOrFail($id);

            $data = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email,' . $user->id,
                'position' => 'required|in:COLETOR,GERENCIA,ADMIN',
                'password' => 'nullable|string|min:6',
            ]);

            $updateData = [
                'name' => $data['name'],
                'email' => $data['email'],
                'position' => $data['position'],
            ];

            if (!empty($data['password'])) {
                $updateData['password'] = Hash::make($data['password']);
            }

            $user->update($updateData);

            return response()->json(['success' => 'Usuário atualizado com sucesso.'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
