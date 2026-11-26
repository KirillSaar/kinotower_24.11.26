<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Country;
use App\Models\Film;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class FilmController extends Controller
{
    private const AGES = [0, 6, 12, 16, 18];

    public function index(Request $request): View
    {
        $films = Film::with(['country', 'categories'])
            ->when($request->filled('country_id'), function ($query) use ($request) {
                $query->where('country_id', $request->integer('country_id'));
            })
            ->when($request->filled('category_id'), function ($query) use ($request) {
                $query->whereHas('categories', function ($q) use ($request) {
                    $q->where('categories.id', $request->integer('category_id'));
                });
            })
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        $countries = Country::orderBy('name')->get();
        $categories = Category::orderBy('name')->get();

        return view('admin.films.index', compact('films', 'countries', 'categories'));
    }

    public function create(): View
    {
        return view('admin.films.form', [
            'film' => new Film(),
            'countries' => Country::orderBy('name')->get(),
            'ages' => self::AGES,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Film::create($this->validated($request));

        return redirect()->route('admin.films.index')->with('success', 'Фильм добавлен.');
    }

    public function show(Film $film): View
    {
        $film->load(['country', 'categories']);

        return view('admin.films.show', compact('film'));
    }

    public function edit(Film $film): View
    {
        return view('admin.films.form', [
            'film' => $film,
            'countries' => Country::orderBy('name')->get(),
            'ages' => self::AGES,
        ]);
    }

    public function update(Request $request, Film $film): RedirectResponse
    {
        $film->update($this->validated($request));

        return redirect()->route('admin.films.index')->with('success', 'Фильм изменён.');
    }

    public function destroy(Film $film): RedirectResponse
    {
        $film->delete();

        return redirect()->route('admin.films.index')->with('success', 'Фильм удалён.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'country_id' => ['required', 'integer', 'exists:countries,id'],
            'duration' => ['required', 'integer', 'min:1', 'max:65535'],
            'year_of_issue' => ['required', 'integer', 'min:1901', 'max:2155'],
            'age' => ['required', 'integer', Rule::in(self::AGES)],
            'link_img' => ['nullable', 'url:http,https', 'max:255'],
            'link_kinopoisk' => ['nullable', 'url:http,https', 'max:255'],
            'link_video' => ['required', 'url:http,https', 'max:255'],
        ], [
            'name.required' => 'Введите название фильма.',
            'name.max' => 'Название не должно быть длиннее 150 символов.',
            'country_id.required' => 'Выберите страну.',
            'country_id.exists' => 'Выбрана несуществующая страна.',
            'duration.required' => 'Укажите длительность в минутах.',
            'duration.integer' => 'Длительность должна быть целым числом.',
            'duration.min' => 'Длительность должна быть не меньше 1 минуты.',
            'duration.max' => 'Слишком большая длительность.',
            'year_of_issue.required' => 'Укажите год выпуска.',
            'year_of_issue.integer' => 'Год выпуска должен быть числом.',
            'year_of_issue.min' => 'Год выпуска должен быть не меньше 1901.',
            'year_of_issue.max' => 'Год выпуска должен быть не больше 2155.',
            'age.required' => 'Выберите возрастное ограничение.',
            'age.in' => 'Выбрано недопустимое возрастное ограничение.',
            'link_img.url' => 'Введите корректную ссылку (http или https).',
            'link_kinopoisk.url' => 'Введите корректную ссылку (http или https).',
            'link_video.required' => 'Укажите ссылку на видео.',
            'link_video.url' => 'Введите корректную ссылку (http или https).',
            'link_img.max' => 'Ссылка не должна быть длиннее 255 символов.',
            'link_kinopoisk.max' => 'Ссылка не должна быть длиннее 255 символов.',
            'link_video.max' => 'Ссылка не должна быть длиннее 255 символов.',
        ]);

        $data['link_img'] = $data['link_img'] ?? null;
        $data['link_kinopoisk'] = $data['link_kinopoisk'] ?? null;

        return $data;
    }
}
