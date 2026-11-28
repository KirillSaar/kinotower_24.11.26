<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\FilmResource;
use App\Models\Film;
use App\Models\Rating;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class FilmController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $data = $request->validate([
            'page' => ['nullable', 'integer', 'min:1'],
            'size' => ['nullable', 'integer', 'min:1'],
            'sortBy' => ['nullable', Rule::in(['name', 'year', 'rating'])],
            'sortDir' => ['nullable', Rule::in(['asc', 'desc'])],
            'category' => ['nullable', 'integer', 'min:0'],
            'country' => ['nullable', 'integer', 'min:0'],
            'search' => ['nullable', 'string', 'max:150'],
        ]);

        $page = (int) ($data['page'] ?? 1);
        $size = (int) ($data['size'] ?? 10);
        $sortBy = $data['sortBy'] ?? 'name';
        $sortDir = $data['sortDir'] ?? 'asc';
        $category = (int) ($data['category'] ?? 0);
        $country = (int) ($data['country'] ?? 0);
        $search = $data['search'] ?? null;

        $query = $this->baseQuery();

        if ($category > 0) {
            $query->whereHas('categories', function ($q) use ($category) {
                $q->where('categories.id', $category);
            });
        }

        if ($country > 0) {
            $query->where('country_id', $country);
        }

        if ($search !== null && $search !== '') {
            $query->where('name', 'like', '%'.$search.'%');
        }

        if ($sortBy === 'year') {
            $query->orderBy('year_of_issue', $sortDir);
        } elseif ($sortBy === 'rating') {
            $query->orderBy(
                Rating::query()
                    ->selectRaw('COALESCE(AVG(ball), 0)')
                    ->whereColumn('ratings.film_id', 'films.id'),
                $sortDir
            );
        } else {
            $query->orderBy('name', $sortDir);
        }

        $query->orderBy('id');

        $paginator = $query->paginate($size, ['*'], 'page', $page);

        return response()->json([
            'page' => $page,
            'size' => $paginator->count(),
            'total' => $paginator->total(),
            'films' => $paginator->getCollection()
                ->map(fn ($film) => (new FilmResource($film))->resolve())
                ->values()
                ->all(),
        ]);
    }

    public function show(string $id): JsonResponse
    {
        $film = $this->baseQuery()->find($id);

        if (! $film) {
            return response()->json(['message' => 'Film not found'], 404);
        }

        return response()->json((new FilmResource($film))->resolve());
    }

    private function baseQuery()
    {
        return Film::query()
            ->with(['country', 'categories'])
            ->withAvg('ratings as rating_avg', 'ball')
            ->withCount([
                'reviews as review_count' => fn ($q) => $q->where('is_approved', 1),
            ]);
    }
}
