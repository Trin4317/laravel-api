<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginUserRequest;
use App\Http\Requests\Auth\StoreUserRequest;
use App\Models\User;
use App\Traits\ApiResponser;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    use ApiResponser;

    public function register(StoreUserRequest $request)
    {
        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password)
        ]);

        return $this->successResponse([
            'user'  => $user->setVisible(['name', 'email']),
            'token' => $user->createToken('basic_token', ['none'])->plainTextToken
        ], 'Account created.');
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

        return $this->successResponse([
            'user'  => $user->setVisible(['name', 'email']),
            'token' => $user->createToken('basic_token', ['none'])->plainTextToken
        ], 'Logged in.');
    }

    public function logout()
    {
        Auth::user()->currentAccessToken()->delete();

        return $this->successResponse(null, 'Successfully logged out.');
    }

    // TODO: allow generating token with 'create', 'update', 'delete' abilities
    // and remove dummy /generate-token web route
}
