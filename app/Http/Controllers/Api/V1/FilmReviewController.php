<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\PublicReviewResource;
use App\Models\Film;
use Illuminate\Http\JsonResponse;

class FilmReviewController extends Controller
{
    public function index(string $id): JsonResponse
    {
        $film = Film::find($id);

        if (! $film) {
            return response()->json(['message' => 'Film not found'], 404);
        }

        $reviews = $film->reviews()
            ->where('is_approved', 1)
            ->with(['user' => fn ($q) => $q->withTrashed()])
            ->orderBy('id')
            ->get();

        return response()->json([
            'reviews' => $reviews
                ->map(fn ($review) => (new PublicReviewResource($review))->resolve())
                ->values()
                ->all(),
        ]);
    }
}
