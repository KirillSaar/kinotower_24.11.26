<?php

use App\Models\Category;
use App\Models\Country;
use App\Models\Film;
use App\Models\Rating;
use App\Models\Review;

it('returns a paginated list of films with default parameters', function () {
    Film::factory()->count(12)->create();

    $response = $this->getJson('/api/v1/films');

    $response->assertOk()
        ->assertJsonPath('page', 1)
        ->assertJsonPath('size', 10)
        ->assertJsonPath('total', 12)
        ->assertJsonCount(10, 'films')
        ->assertJsonStructure([
            'page',
            'size',
            'total',
            'films' => [
                '*' => [
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
                    'categories',
                    'ratingAvg',
                    'reviewCount',
                ],
            ],
        ]);
});

it('returns the requested page and the actual page size', function () {
    Film::factory()->count(12)->create();

    $this->getJson('/api/v1/films?page=3&size=5')
        ->assertOk()
        ->assertJsonPath('page', 3)
        ->assertJsonPath('size', 2)
        ->assertJsonPath('total', 12)
        ->assertJsonCount(2, 'films');
});

it('returns an empty list when the page is out of range', function () {
    Film::factory()->count(3)->create();

    $this->getJson('/api/v1/films?page=5&size=10')
        ->assertOk()
        ->assertJsonPath('total', 3)
        ->assertJsonPath('size', 0)
        ->assertJsonCount(0, 'films');
});

it('formats created_at as an ISO-8601 timestamp', function () {
    Film::factory()->create();

    $createdAt = $this->getJson('/api/v1/films')->json('films.0.created_at');

    expect($createdAt)->toMatch('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}\.\d{3}Z$/');
});

it('returns link_img and link_kinopoisk as null when they are not set', function () {
    Film::factory()->create(['link_img' => null, 'link_kinopoisk' => null]);

    $this->getJson('/api/v1/films')
        ->assertOk()
        ->assertJsonPath('films.0.link_img', null)
        ->assertJsonPath('films.0.link_kinopoisk', null);
});

it('does not return deleted films', function () {
    Film::factory()->create(['name' => 'Visible']);
    Film::factory()->create(['name' => 'Hidden'])->delete();

    $this->getJson('/api/v1/films')
        ->assertOk()
        ->assertJsonPath('total', 1)
        ->assertJsonPath('films.0.name', 'Visible');
});

it('sorts films by name ascending by default', function () {
    Film::factory()->create(['name' => 'Bravo']);
    Film::factory()->create(['name' => 'Alpha']);
    Film::factory()->create(['name' => 'Charlie']);

    $names = collect($this->getJson('/api/v1/films')->json('films'))->pluck('name')->all();

    expect($names)->toBe(['Alpha', 'Bravo', 'Charlie']);
});

it('sorts films by name descending', function () {
    Film::factory()->create(['name' => 'Bravo']);
    Film::factory()->create(['name' => 'Alpha']);
    Film::factory()->create(['name' => 'Charlie']);

    $names = collect($this->getJson('/api/v1/films?sortBy=name&sortDir=desc')->json('films'))
        ->pluck('name')
        ->all();

    expect($names)->toBe(['Charlie', 'Bravo', 'Alpha']);
});

it('sorts films by year', function () {
    Film::factory()->create(['name' => 'A', 'year_of_issue' => 2000]);
    Film::factory()->create(['name' => 'B', 'year_of_issue' => 1990]);
    Film::factory()->create(['name' => 'C', 'year_of_issue' => 2010]);

    $asc = collect($this->getJson('/api/v1/films?sortBy=year&sortDir=asc')->json('films'))
        ->pluck('year_of_issue')
        ->all();
    $desc = collect($this->getJson('/api/v1/films?sortBy=year&sortDir=desc')->json('films'))
        ->pluck('year_of_issue')
        ->all();

    expect($asc)->toBe([1990, 2000, 2010]);
    expect($desc)->toBe([2010, 2000, 1990]);
});

it('sorts films by average rating', function () {
    $high = Film::factory()->create(['name' => 'High']);
    $low = Film::factory()->create(['name' => 'Low']);
    Film::factory()->create(['name' => 'None']);

    Rating::factory()->create(['film_id' => $high->id, 'ball' => 5]);
    Rating::factory()->create(['film_id' => $high->id, 'ball' => 4]);
    Rating::factory()->create(['film_id' => $low->id, 'ball' => 2]);

    $desc = collect($this->getJson('/api/v1/films?sortBy=rating&sortDir=desc')->json('films'))
        ->pluck('name')
        ->all();
    $asc = collect($this->getJson('/api/v1/films?sortBy=rating&sortDir=asc')->json('films'))
        ->pluck('name')
        ->all();

    expect($desc)->toBe(['High', 'Low', 'None']);
    expect($asc)->toBe(['None', 'Low', 'High']);
});

it('filters films by category', function () {
    $category = Category::factory()->create();
    $other = Category::factory()->create();

    $inCategory = Film::factory()->create(['name' => 'In category']);
    $inCategory->categories()->attach($category->id);
    $outside = Film::factory()->create(['name' => 'Other category']);
    $outside->categories()->attach($other->id);
    Film::factory()->create(['name' => 'No category']);

    $this->getJson('/api/v1/films?category='.$category->id)
        ->assertOk()
        ->assertJsonPath('total', 1)
        ->assertJsonPath('films.0.name', 'In category');
});

it('does not filter by category when category is 0', function () {
    $category = Category::factory()->create();
    Film::factory()->create()->categories()->attach($category->id);
    Film::factory()->create();

    $this->getJson('/api/v1/films?category=0')
        ->assertOk()
        ->assertJsonPath('total', 2);
});

it('filters films by country', function () {
    $country = Country::factory()->create();

    Film::factory()->create(['name' => 'Local', 'country_id' => $country->id]);
    Film::factory()->create(['name' => 'Foreign']);

    $this->getJson('/api/v1/films?country='.$country->id)
        ->assertOk()
        ->assertJsonPath('total', 1)
        ->assertJsonPath('films.0.name', 'Local');
});

it('does not filter by country when country is 0', function () {
    Film::factory()->count(2)->create();

    $this->getJson('/api/v1/films?country=0')
        ->assertOk()
        ->assertJsonPath('total', 2);
});

it('searches films by name', function () {
    Film::factory()->create(['name' => 'Black Mirror']);
    Film::factory()->create(['name' => 'Inception']);

    $this->getJson('/api/v1/films?search=Mirror')
        ->assertOk()
        ->assertJsonPath('total', 1)
        ->assertJsonPath('films.0.name', 'Black Mirror');
});

it('combines filters, search and pagination', function () {
    $category = Category::factory()->create();
    $country = Country::factory()->create();

    foreach (['Space One', 'Space Two', 'Space Three'] as $name) {
        Film::factory()->create(['name' => $name, 'country_id' => $country->id])
            ->categories()
            ->attach($category->id);
    }
    Film::factory()->create(['name' => 'Space Four']);

    $this->getJson("/api/v1/films?category={$category->id}&country={$country->id}&search=Space&size=2&page=2")
        ->assertOk()
        ->assertJsonPath('total', 3)
        ->assertJsonPath('size', 1)
        ->assertJsonCount(1, 'films');
});

it('includes country, categories, average rating and review count', function () {
    $country = Country::factory()->create(['name' => 'Kazakhstan']);
    $film = Film::factory()->create(['country_id' => $country->id]);

    $fantasy = Category::factory()->create(['name' => 'Fantasy']);
    $drama = Category::factory()->create(['name' => 'Drama']);
    $film->categories()->attach([$fantasy->id, $drama->id]);

    Rating::factory()->create(['film_id' => $film->id, 'ball' => 4]);
    Rating::factory()->create(['film_id' => $film->id, 'ball' => 5]);

    Review::factory()->approved()->count(2)->create(['film_id' => $film->id]);
    Review::factory()->create(['film_id' => $film->id]);

    $response = $this->getJson('/api/v1/films')->assertOk();

    expect($response->json('films.0.country'))->toBe(['id' => $country->id, 'name' => 'Kazakhstan']);
    expect($response->json('films.0.categories'))->toHaveCount(2);
    expect($response->json('films.0.ratingAvg'))->toEqual(4.5);
    expect($response->json('films.0.reviewCount'))->toBe(2);
});

it('returns zero rating and zero reviews for a film without them', function () {
    Film::factory()->create();

    $this->getJson('/api/v1/films')
        ->assertOk()
        ->assertJsonPath('films.0.reviewCount', 0)
        ->assertJsonPath('films.0.categories', []);

    $response = $this->getJson('/api/v1/films');

    expect($response->json('films.0.ratingAvg'))->toEqual(0);
});

it('rejects invalid query parameters', function (string $query, string $field) {
    $this->getJson('/api/v1/films?'.$query)
        ->assertStatus(422)
        ->assertJsonValidationErrors([$field]);
})->with([
    'page below 1' => ['page=0', 'page'],
    'page not a number' => ['page=abc', 'page'],
    'size below 1' => ['size=0', 'size'],
    'unknown sortBy' => ['sortBy=color', 'sortBy'],
    'unknown sortDir' => ['sortDir=up', 'sortDir'],
]);
