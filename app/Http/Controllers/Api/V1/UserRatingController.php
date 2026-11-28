<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Concerns\ResolvesUser;
use App\Http\Controllers\Controller;
use App\Http\Resources\RatingResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;

class UserRatingController extends Controller
{
    use ResolvesUser;

    public function index(Request $request, string $userId): JsonResponse
    {
        $user = $this->resolveUser($request, $userId);

        if ($user instanceof JsonResponse) {
            return $user;
        }

        $ratings = $user->ratings()
            ->with(['film' => fn ($q) => $q->withTrashed()])
            ->orderBy('id')
            ->get();

        return response()->json([
            'ratings' => $ratings
                ->map(fn ($rating) => (new RatingResource($rating))->resolve())
                ->values()
                ->all(),
        ]);
    }

    public function store(Request $request, string $userId): JsonResponse
    {
        $user = $this->resolveUser($request, $userId);

        if ($user instanceof JsonResponse) {
            return $user;
        }

        $data = $request->validate([
            'film_id' => [
                'required',
                'integer',
                Rule::exists('films', 'id')->whereNull('deleted_at'),
            ],
            'ball' => ['required', 'integer', 'min:1', 'max:5'],
        ]);

        if ($user->ratings()->where('film_id', $data['film_id'])->exists()) {
            return response()->json([
                'status' => 'invalid',
                'message' => 'Score exist',
            ], 401);
        }

        $rating = $user->ratings()->create([
            'film_id' => $data['film_id'],
            'ball' => $data['ball'],
        ]);

        $rating->load('film');

        return response()->json((new RatingResource($rating))->resolve(), 201);
    }

    public function destroy(Request $request, string $userId, string $id): JsonResponse|Response
    {
        $user = $this->resolveUser($request, $userId);

        if ($user instanceof JsonResponse) {
            return $user;
        }

        $rating = $user->ratings()->find($id);

        if (! $rating) {
            return response()->json(['message' => 'Rating not found'], 404);
        }

        $rating->delete();

        return response()->noContent();
    }
}
