<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginUserRequest;
use App\Http\Requests\Auth\StoreUserRequest;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(StoreUserRequest $request)
    {
        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password)
        ]);

        return response()->json([
            'user'  => $user->setVisible(['name', 'email']),
            'token' => $user->createToken('basic_token', ['none'])->plainTextToken
        ]);
    }

    public function login(LoginUserRequest $request)
    {
        if (!Auth::attempt(
                $request
                 ->safe()
                 ->only(['email', 'password'])
            )) {
            throw ValidationException::withMessages([
                'credentials' => 'The provided credentials do not match our records.'
            ]);
        }

        $user = User::whereEmail($request->email)->first();

        return response()->json([
            'user'  => $user->setVisible(['name', 'email']),
            'token' => $user->createToken('basic_token', ['none'])->plainTextToken
        ]);
    }

    public function logout()
    {
        Auth::user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out.'
        ]);
    }

    // TODO: allow generating token with 'create', 'update', 'delete' abilities
    // and remove dummy /generate-token web route
}
