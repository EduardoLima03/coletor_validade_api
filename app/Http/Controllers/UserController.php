<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
USE Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    public function login(Request $request){
        if(Auth::attempt(['email' => $request -> email, 'password' => $request -> password])){
            $user = Auth::user();
            $isToken = $user->tokens;
            if($isToken != null){
                $user->tokens()->delete();
            }
            $token = $user->createToken('JWT');

            return response()->json(['token' => $token->plainTextToken, 'user' => ['id' => $user->id, 'name' => $user->name, 'function' => $user->function]], 200);
        }
        return response()->json('Usuario invalido', 401);
    }

    public function store(Request $request, User $user)
    {
        $userData = $request->only('name', 'email', 'password', 'function');
        $userData['password'] = Hash::make($userData['password']);
        if(!$user = $user->create($userData)){
            abort(500, "Erro");
        }

        return response()->json([$user]);

        /*$data = User::create($request->all());
        return response()->json(['success' => 'Usuario criado com sucesso.'], 200);*/
    }

    public function update(Request $request, $id)
    {
       
        try{
            $user = User::findOrFail($id);
            $user->update($request->all());
            return response()->json(['success' => 'Update realizado'], 200);
        }catch(Exception $e){
            return response()->json(['error' => $e->getMessage()], 500);
        }
        
        return $user;
    }
}
