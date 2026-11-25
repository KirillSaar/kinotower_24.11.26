<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(): View
    {
        $categories = Category::with('parent')->orderBy('id')->paginate(15);

        return view('admin.categories.index', compact('categories'));
    }

    public function create(): View
    {
        $category = new Category();
        $parents = Category::orderBy('name')->get();

        return view('admin.categories.form', compact('category', 'parents'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        Category::create($data);

        return redirect()->route('admin.categories.index')->with('success', 'Жанр добавлен.');
    }

    public function edit(Category $category): View
    {
        $parents = Category::whereNotIn('id', $this->selfAndDescendantIds($category))
            ->orderBy('name')
            ->get();

        return view('admin.categories.form', compact('category', 'parents'));
    }

    public function update(Request $request, Category $category): RedirectResponse
    {
        $data = $this->validated($request, $category);

        $category->update($data);

        return redirect()->route('admin.categories.index')->with('success', 'Жанр изменён.');
    }

    public function destroy(Category $category): RedirectResponse
    {
        if ($category->children()->exists()) {
            return redirect()->route('admin.categories.index')
                ->with('error', 'Нельзя удалить жанр, у которого есть дочерние жанры.');
        }

        $category->delete();

        return redirect()->route('admin.categories.index')->with('success', 'Жанр удалён.');
    }

    private function validated(Request $request, ?Category $category = null): array
    {
        $excluded = $category ? $this->selfAndDescendantIds($category) : [];

        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'parent_id' => [
                'nullable',
                Rule::exists('categories', 'id')->whereNull('deleted_at'),
                Rule::notIn($excluded),
            ],
        ], [
            'name.required' => 'Введите название жанра.',
            'name.max' => 'Название не должно быть длиннее 150 символов.',
            'parent_id.exists' => 'Выбран несуществующий родительский жанр.',
            'parent_id.not_in' => 'Жанр не может быть вложен сам в себя или в свой дочерний жанр.',
        ]);

        $data['parent_id'] = $data['parent_id'] ?? null;

        return $data;
    }

    private function selfAndDescendantIds(Category $category): array
    {
        $ids = [$category->id];
        $level = [$category->id];

        while ($level) {
            $level = Category::whereIn('parent_id', $level)->pluck('id')->all();
            $ids = array_merge($ids, $level);
        }

        return $ids;
    }
}
