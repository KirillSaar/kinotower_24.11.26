<?php

use App\Models\Category;
use App\Models\Country;
use App\Models\Film;
use App\Models\Rating;
use App\Models\Review;

it('returns a film by id', function () {
    $country = Country::factory()->create(['name' => 'Pakistan']);
    $film = Film::factory()->create([
        'name' => 'Black Mirror',
        'country_id' => $country->id,
        'duration' => 150,
        'year_of_issue' => 2010,
        'age' => 18,
        'link_video' => 'https://example.com/video',
    ]);

    $fantasy = Category::factory()->create(['name' => 'Fantasy']);
    $film->categories()->attach($fantasy->id);

    Rating::factory()->create(['film_id' => $film->id, 'ball' => 4]);
    Rating::factory()->create(['film_id' => $film->id, 'ball' => 5]);
    Review::factory()->approved()->create(['film_id' => $film->id]);
    Review::factory()->create(['film_id' => $film->id]);

    $response = $this->getJson('/api/v1/films/'.$film->id)
        ->assertOk()
        ->assertJsonStructure([
            'id',
            'name',
            'duration',
            'year_of_issue',
            'age',
            'link_img',
            'link_kinopoisk',
            'link_video',
            'created_at',
            'country' => ['id', 'name'],
            'categories' => [['id', 'name']],
            'ratingAvg',
            'reviewCount',
        ])
        ->assertJsonPath('id', $film->id)
        ->assertJsonPath('name', 'Black Mirror')
        ->assertJsonPath('duration', 150)
        ->assertJsonPath('year_of_issue', 2010)
        ->assertJsonPath('age', 18)
        ->assertJsonPath('link_img', null)
        ->assertJsonPath('link_kinopoisk', null)
        ->assertJsonPath('link_video', 'https://example.com/video')
        ->assertJsonPath('country.name', 'Pakistan')
        ->assertJsonPath('categories.0.name', 'Fantasy')
        ->assertJsonPath('reviewCount', 1);

    expect($response->json('ratingAvg'))->toEqual(4.5);
    expect($response->json('created_at'))->toMatch('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}\.\d{3}Z$/');
});

it('returns 404 when the film does not exist', function () {
    $this->getJson('/api/v1/films/9999')
        ->assertStatus(404)
        ->assertExactJson(['message' => 'Film not found']);
});

it('returns 404 for a deleted film', function () {
    $film = Film::factory()->create();
    $film->delete();

    $this->getJson('/api/v1/films/'.$film->id)
        ->assertStatus(404)
        ->assertExactJson(['message' => 'Film not found']);
});
