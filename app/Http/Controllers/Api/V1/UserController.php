<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Concerns\ResolvesUser;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    use ResolvesUser;

    public function show(Request $request, string $id): JsonResponse
    {
        $user = $this->resolveUser($request, $id);

        if ($user instanceof JsonResponse) {
            return $user;
        }

        $user->load('gender')->loadCount(['reviews', 'ratings']);

        return response()->json([
            'id' => $user->id,
            'fio' => $user->fio,
            'email' => $user->email,
            'birthday' => $user->birthday?->format('Y-m-d'),
            'gender' => [
                'id' => $user->gender->id,
                'name' => $user->gender->name,
            ],
            'reviewCount' => (int) $user->reviews_count,
            'ratingCount' => (int) $user->ratings_count,
        ]);
    }

    public function update(Request $request): JsonResponse
    {
        $user = $request->user();

        $data = $request->validate([
            'fio' => ['required', 'string', 'min:2', 'max:150'],
            'email' => [
                'required',
                'email',
                'min:4',
                'max:50',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'birthday' => ['required', 'date'],
            'gender_id' => ['required', 'integer', 'exists:genders,id'],
        ]);

        $user->update($data);

        return response()->json(['status' => 'success']);
    }

    public function destroy(Request $request): Response
    {
        $user = $request->user();

        $user->tokens()->delete();
        $user->delete();

        return response()->noContent();
    }
}
