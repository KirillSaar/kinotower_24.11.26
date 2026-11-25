<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Country;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CountryController extends Controller
{
    public function index(): View
    {
        $countries = Country::orderBy('id')->paginate(10);

        return view('admin.countries.index', compact('countries'));
    }

    public function create(): View
    {
        return view('admin.countries.form', ['country' => new Country()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        Country::create($data);

        return redirect()->route('admin.countries.index')->with('success', 'Страна добавлена.');
    }

    public function edit(Country $country): View
    {
        return view('admin.countries.form', compact('country'));
    }

    public function update(Request $request, Country $country): RedirectResponse
    {
        $data = $this->validated($request, $country);

        $country->update($data);

        return redirect()->route('admin.countries.index')->with('success', 'Страна изменена.');
    }

    public function destroy(Country $country): RedirectResponse
    {
        if ($country->films()->exists()) {
            return redirect()->route('admin.countries.index')
                ->with('error', 'Нельзя удалить страну, к которой привязаны фильмы.');
        }

        $country->delete();

        return redirect()->route('admin.countries.index')->with('success', 'Страна удалена.');
    }

    private function validated(Request $request, ?Country $country = null): array
    {
        return $request->validate([
            'name' => [
                'required',
                'string',
                'max:50',
                Rule::unique('countries', 'name')->ignore($country?->id),
            ],
        ], [
            'name.required' => 'Введите название страны.',
            'name.max' => 'Название не должно быть длиннее 50 символов.',
            'name.unique' => 'Такая страна уже есть.',
        ]);
    }
}
