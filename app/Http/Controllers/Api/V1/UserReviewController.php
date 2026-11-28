<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Concerns\ResolvesUser;
use App\Http\Controllers\Controller;
use App\Http\Resources\ReviewResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;

class UserReviewController extends Controller
{
    use ResolvesUser;

    public function index(Request $request, string $userId): JsonResponse
    {
        $user = $this->resolveUser($request, $userId);

        if ($user instanceof JsonResponse) {
            return $user;
        }

        $reviews = $user->reviews()
            ->with(['film' => fn ($q) => $q->withTrashed()])
            ->orderBy('id')
            ->get();

        return response()->json([
            'reviews' => $reviews
                ->map(fn ($review) => (new ReviewResource($review))->resolve())
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
            'message' => ['required', 'string', 'min:4', 'max:1024'],
        ]);

        $review = $user->reviews()->create([
            'film_id' => $data['film_id'],
            'message' => $data['message'],
            'is_approved' => 0,
        ]);

        $review->load('film');

        return response()->json((new ReviewResource($review))->resolve(), 201);
    }

    public function destroy(Request $request, string $userId, string $id): JsonResponse|Response
    {
        $user = $this->resolveUser($request, $userId);

        if ($user instanceof JsonResponse) {
            return $user;
        }

        $review = $user->reviews()->find($id);

        if (! $review) {
            return response()->json(['message' => 'Review not found'], 404);
        }

        $review->delete();

        return response()->noContent();
    }
}
