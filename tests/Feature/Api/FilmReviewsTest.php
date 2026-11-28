<?php

use App\Models\Film;
use App\Models\Review;
use App\Models\User;

it('returns only approved reviews of the film', function () {
    $film = Film::factory()->create();
    $author = User::factory()->create(['fio' => 'Ivanov Ivan']);

    $approved = Review::factory()->approved()->create([
        'film_id' => $film->id,
        'user_id' => $author->id,
        'message' => 'Very Good!',
    ]);
    Review::factory()->create(['film_id' => $film->id, 'message' => 'Not approved yet']);
    Review::factory()->approved()->create(['message' => 'Review of another film']);

    $response = $this->getJson('/api/v1/films/'.$film->id.'/reviews')
        ->assertOk()
        ->assertJsonCount(1, 'reviews')
        ->assertJsonStructure([
            'reviews' => [
                '*' => ['id', 'user' => ['id', 'fio'], 'message', 'created_at'],
            ],
        ])
        ->assertJsonPath('reviews.0.id', $approved->id)
        ->assertJsonPath('reviews.0.user.id', $author->id)
        ->assertJsonPath('reviews.0.user.fio', 'Ivanov Ivan')
        ->assertJsonPath('reviews.0.message', 'Very Good!');

    expect($response->json('reviews.0.created_at'))
        ->toMatch('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}\.\d{3}Z$/');
});

it('does not return deleted reviews', function () {
    $film = Film::factory()->create();

    Review::factory()->approved()->create(['film_id' => $film->id])->delete();

    $this->getJson('/api/v1/films/'.$film->id.'/reviews')
        ->assertOk()
        ->assertJsonCount(0, 'reviews');
});

it('returns an empty list when the film has no reviews', function () {
    $film = Film::factory()->create();

    $this->getJson('/api/v1/films/'.$film->id.'/reviews')
        ->assertOk()
        ->assertExactJson(['reviews' => []]);
});

it('returns 404 when the film does not exist', function () {
    $this->getJson('/api/v1/films/9999/reviews')
        ->assertStatus(404)
        ->assertExactJson(['message' => 'Film not found']);
});
