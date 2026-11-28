<?php

namespace App\Http\Controllers\Api\V1\Concerns;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

trait ResolvesUser
{
    protected function resolveUser(Request $request, string $userId): User|JsonResponse
    {
        $user = User::find($userId);

        if (! $user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        if ((int) $request->user()->id !== (int) $user->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        return $user;
    }
}
