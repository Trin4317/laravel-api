<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/generate-token', function () {
    $credentials = [
        'email'    => 'john@example.com',
        'password' => 'password'
    ];

    $user = User::firstOrCreate(
        ['email' => $credentials['email']],
        ['name' => 'John Doe', 'password' => Hash::make($credentials['password'])]
    );

    if (Auth::attempt($credentials)) {
        $adminToken  = $user->createToken('admin-token', ['create', 'update', 'delete']);
        $updateToken = $user->createToken('update-token', ['create', 'update']);
        $basicToken  = $user->createToken('basic-token', ['none']);

        return [
            'admin'  => $adminToken->plainTextToken,
            'update' => $updateToken->plainTextToken,
            'basic'  => $basicToken->plainTextToken,
        ];
    }

    abort(500);
});
