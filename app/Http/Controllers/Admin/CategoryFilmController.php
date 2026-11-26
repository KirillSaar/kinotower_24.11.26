<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Film;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CategoryFilmController extends Controller
{
    public function index(Film $film): View
    {
        $categories = $film->categories()->with('parent')->orderBy('name')->get();

        return view('admin.films.categories.index', compact('film', 'categories'));
    }

    public function create(Film $film): View
    {
        $categories = Category::whereNotIn('id', $this->attachedIds($film))
            ->orderBy('name')
            ->get();

        return view('admin.films.categories.create', compact('film', 'categories'));
    }

    public function store(Request $request, Film $film): RedirectResponse
    {
        $data = $request->validate([
            'category_id' => [
                'required',
                Rule::exists('categories', 'id')->whereNull('deleted_at'),
                Rule::notIn($this->attachedIds($film)),
            ],
        ], [
            'category_id.required' => 'Выберите жанр.',
            'category_id.exists' => 'Выбран несуществующий жанр.',
            'category_id.not_in' => 'Этот жанр уже добавлен к фильму.',
        ]);

        $film->categories()->attach($data['category_id']);

        return redirect()->route('admin.films.categories.index', $film)
            ->with('success', 'Жанр добавлен к фильму.');
    }

    public function destroy(Film $film, Category $category): RedirectResponse
    {
        $film->categories()->detach($category->id);

        return redirect()->route('admin.films.categories.index', $film)
            ->with('success', 'Жанр удалён у фильма.');
    }

    private function attachedIds(Film $film): array
    {
        return $film->categories()->pluck('categories.id')->all();
    }
}
