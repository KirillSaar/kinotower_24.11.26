<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Film;
use App\Models\Review;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReviewFilmController extends Controller
{
    public function index(Request $request, ?Film $film = null): View
    {
        $filmId = $film?->id ?? $request->integer('film_id');
        $status = $request->query('status', 'all');

        $reviews = Review::with([
            'film' => fn ($query) => $query->withTrashed(),
            'user' => fn ($query) => $query->withTrashed(),
        ])
            ->when($filmId, fn ($query) => $query->where('film_id', $filmId))
            ->when($status === 'approved', fn ($query) => $query->where('is_approved', 1))
            ->when($status === 'pending', fn ($query) => $query->where('is_approved', 0))
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        $films = Film::orderBy('name')->get();

        return view('admin.reviews.index', compact('reviews', 'films', 'filmId', 'status'));
    }

    public function approved(Review $review): RedirectResponse
    {
        $review->update(['is_approved' => 1]);

        return back()->with('success', 'Отзыв одобрен.');
    }

    public function destroy(Review $review): RedirectResponse
    {
        $review->delete();

        return back()->with('success', 'Отзыв удалён.');
    }
}
