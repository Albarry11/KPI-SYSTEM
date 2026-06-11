<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:100',
            'username' => 'required|string|max:50|unique:users',
            'email'    => 'nullable|email|max:150|unique:users',
            'password' => 'required|string|min:6',
        ]);

        $user = User::create([
            'name'      => $request->name,
            'username'  => $request->username,
            'email'     => $request->email,
            'password'  => Hash::make($request->password),
            'role'      => 'employee',
            'is_active' => true,
        ]);

        $abilities = ['employee:progress', 'employee:view'];
        $token = $user->createToken('kpi-token', $abilities)->plainTextToken;

        return response()->json([
            'success' => true,
            'token'   => $token,
            'user'    => $user,
        ], 201);
    }

    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $user = User::where('is_active', true)
                    ->where(function ($query) use ($request) {
                        $query->where('username', $request->username)
                              ->orWhere('email', $request->username);
                    })
                    ->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'username' => ['Username atau password salah.'],
            ]);
        }

        $user->tokens()->delete();

        $abilities = $user->isManager() ? ['manager:all'] : ['employee:progress', 'employee:view'];
        $token = $user->createToken('kpi-token', $abilities)->plainTextToken;

        return response()->json([
            'success' => true,
            'token'   => $token,
            'user'    => $user,
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();
        return response()->json(['success' => true, 'message' => 'Berhasil logout.']);
    }

    public function me(Request $request)
    {
        return response()->json([
            'success' => true,
            'data'    => $request->user(),
        ]);
    }

    public function refresh(Request $request)
    {
        $user = $request->user();
        $user->tokens()->delete();

        $abilities = $user->isManager() ? ['manager:all'] : ['employee:progress', 'employee:view'];
        $token = $user->createToken('kpi-token', $abilities)->plainTextToken;

        return response()->json(['success' => true, 'token' => $token]);
    }

    public function setupManager(Request $request)
    {
        if (User::where('role', 'manager')->exists()) {
            return response()->json(['message' => 'Manager already exists'], 400);
        }

        $request->validate([
            'name'     => 'required|string',
            'username' => 'required|string|unique:users',
            'password' => 'required|string',
        ]);

        $user = User::create([
            'name'      => $request->name,
            'username'  => $request->username,
            'email'     => $request->username . '@kpi.app',
            'password'  => Hash::make($request->password),
            'role'      => 'manager',
            'is_active' => true,
        ]);

        return response()->json(['message' => 'Manager created', 'user' => $user]);
    }
}
