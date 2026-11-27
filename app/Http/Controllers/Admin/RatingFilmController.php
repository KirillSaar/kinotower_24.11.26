<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Film;
use App\Models\Rating;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RatingFilmController extends Controller
{
    public function index(Request $request, ?Film $film = null): View
    {
        $filmId = $film?->id ?? $request->integer('film_id');

        $query = Rating::when($filmId, fn ($q) => $q->where('film_id', $filmId));

        $average = (clone $query)->avg('ball');
        $total = (clone $query)->count();

        $ratings = $query
            ->with([
                'film' => fn ($q) => $q->withTrashed(),
                'user' => fn ($q) => $q->withTrashed(),
            ])
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        $films = Film::orderBy('name')->get();

        return view('admin.ratings.index', compact('ratings', 'films', 'filmId', 'average', 'total'));
    }

    public function destroy(Rating $rating): RedirectResponse
    {
        $rating->delete();

        return back()->with('success', 'Оценка удалена.');
    }
}
