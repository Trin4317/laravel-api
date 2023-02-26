<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginUserRequest;
use App\Http\Requests\Auth\StoreUserRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

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
            abort(401, 'Credentials do not match.');
        }

        $user = User::whereEmail($request->email)->first();

        return response()->json([
            'user'  => $user->setVisible(['name', 'email']),
            'token' => $user->createToken('basic_token', ['none'])->plainTextToken
        ]);
    }
}
