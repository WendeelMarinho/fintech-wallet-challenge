<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(RegisterRequest $request)
    {
        $data = $request->validated();

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        Wallet::create([
            'user_id' => $user->id,
            'balance' => 1000.00,
        ]);

        $token = $user->createToken('auth')->plainTextToken;

        return $this->success([
            'user' => $user,
            'token' => $token,
        ]);
    }

    public function login(LoginRequest $request)
    {
        $data = $request->validated();
        $user = User::query()->where('email', $data['email'])->first();

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            return $this->error('Credenciais inválidas.', 401);
        }

        $token = $user->createToken('auth')->plainTextToken;

        return $this->success([
            'user' => $user,
            'token' => $token,
        ]);
    }

    public function me(Request $request)
    {
        return $this->success($request->user());
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()?->delete();

        return $this->success([], 'Logout realizado.');
    }
}
