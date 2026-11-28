<?php

use App\Models\Category;
use App\Models\Country;
use App\Models\Film;
use App\Models\Gender;

it('returns categories with parent category and film count', function () {
    $parent = Category::factory()->create(['name' => 'Action']);
    $child = Category::factory()->create(['name' => 'Western', 'parent_id' => $parent->id]);

    Film::factory()->count(2)->create()->each(fn ($film) => $film->categories()->attach($child->id));

    $response = $this->getJson('/api/v1/categories')
        ->assertOk()
        ->assertJsonStructure([
            'categories' => [
                '*' => ['id', 'name', 'parentCategory', 'filmCount'],
            ],
        ])
        ->assertJsonCount(2, 'categories');

    expect($response->json('categories.0'))->toBe([
        'id' => $parent->id,
        'name' => 'Action',
        'parentCategory' => null,
        'filmCount' => 0,
    ]);

    expect($response->json('categories.1'))->toBe([
        'id' => $child->id,
        'name' => 'Western',
        'parentCategory' => ['id' => $parent->id, 'name' => 'Action'],
        'filmCount' => 2,
    ]);
});

it('does not return deleted categories', function () {
    Category::factory()->create(['name' => 'Visible']);
    Category::factory()->create(['name' => 'Hidden'])->delete();

    $this->getJson('/api/v1/categories')
        ->assertOk()
        ->assertJsonCount(1, 'categories')
        ->assertJsonPath('categories.0.name', 'Visible');
});

it('returns countries with film count', function () {
    $pakistan = Country::factory()->create(['name' => 'Pakistan']);
    $empty = Country::factory()->create(['name' => 'Chile']);

    Film::factory()->count(3)->create(['country_id' => $pakistan->id]);

    $response = $this->getJson('/api/v1/countries')
        ->assertOk()
        ->assertJsonStructure([
            'countries' => [
                '*' => ['id', 'name', 'filmCount'],
            ],
        ])
        ->assertJsonCount(2, 'countries');

    expect($response->json('countries.0'))->toBe([
        'id' => $pakistan->id,
        'name' => 'Pakistan',
        'filmCount' => 3,
    ]);

    expect($response->json('countries.1'))->toBe([
        'id' => $empty->id,
        'name' => 'Chile',
        'filmCount' => 0,
    ]);
});

it('returns genders', function () {
    $male = Gender::factory()->create(['name' => 'Мужской']);
    $female = Gender::factory()->create(['name' => 'Женский']);

    $this->getJson('/api/v1/genders')
        ->assertOk()
        ->assertExactJson([
            'genders' => [
                ['id' => $male->id, 'name' => 'Мужской'],
                ['id' => $female->id, 'name' => 'Женский'],
            ],
        ]);
});
