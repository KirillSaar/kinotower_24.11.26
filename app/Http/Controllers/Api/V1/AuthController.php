<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\PersonalAccessToken;

class AuthController extends Controller
{
    public function signup(Request $request): JsonResponse
    {
        $data = $request->validate([
            'fio' => ['required', 'string', 'min:2', 'max:150'],
            'email' => ['required', 'email', 'min:4', 'max:50', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6', 'max:65536'],
            'birthday' => ['required', 'date'],
            'gender_id' => ['required', 'integer', 'exists:genders,id'],
        ]);

        $user = User::create($data);

        return response()->json([
            'status' => 'success',
            'token' => $user->createToken('api')->plainTextToken,
            'id' => $user->id,
            'fio' => $user->fio,
        ], 201);
    }

    public function signin(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email', 'min:4', 'max:50'],
            'password' => ['required', 'string', 'min:6', 'max:65536'],
        ]);

        $user = User::where('email', $data['email'])->first();

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            return response()->json([
                'status' => 'invalid',
                'message' => 'Wrong email or password',
            ], 401);
        }

        return response()->json([
            'status' => 'success',
            'token' => $user->createToken('api')->plainTextToken,
            'id' => $user->id,
            'fio' => $user->fio,
        ]);
    }

    public function signout(Request $request): JsonResponse
    {
        $token = $request->user()->currentAccessToken();

        if ($token instanceof PersonalAccessToken) {
            $token->delete();
        }

        return response()->json(['status' => 'success']);
    }
}
