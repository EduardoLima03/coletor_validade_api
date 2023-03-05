<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function login(Request $request){
        if(Auth::attempt(['email' => $request -> email, 'password' => $request -> password])){
            $user = Auth::user();
            $token = $user->createToken('JWT');

            return response()->json($token->plainTextToken, 200);
        }
        return response()->json('Usuario invalido', 401);
    }
}
