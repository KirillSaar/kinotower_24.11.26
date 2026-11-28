<?php

use App\Models\Film;
use App\Models\Rating;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

it('creates a rating for a film', function () {
    $user = User::factory()->create();
    $film = Film::factory()->create(['name' => 'Black Mirror']);
    Sanctum::actingAs($user);

    $response = $this->postJson('/api/v1/users/'.$user->id.'/ratings', [
        'film_id' => $film->id,
        'ball' => 5,
    ])
        ->assertStatus(201)
        ->assertJsonStructure([
            'id',
            'film' => ['id', 'name'],
            'score',
            'created_at',
        ])
        ->assertJsonPath('film.id', $film->id)
        ->assertJsonPath('film.name', 'Black Mirror')
        ->assertJsonPath('score', 5);

    expect($response->json('created_at'))
        ->toMatch('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}\.\d{3}Z$/');

    $this->assertDatabaseHas('ratings', [
        'id' => $response->json('id'),
        'film_id' => $film->id,
        'user_id' => $user->id,
        'ball' => 5,
    ]);
});

it('does not allow a second rating for the same film', function () {
    $user = User::factory()->create();
    $film = Film::factory()->create();
    Rating::factory()->create(['user_id' => $user->id, 'film_id' => $film->id, 'ball' => 3]);
    Sanctum::actingAs($user);

    $this->postJson('/api/v1/users/'.$user->id.'/ratings', [
        'film_id' => $film->id,
        'ball' => 5,
    ])
        ->assertStatus(401)
        ->assertExactJson([
            'status' => 'invalid',
            'message' => 'Score exist',
        ]);

    $this->assertDatabaseCount('ratings', 1);
});

it('allows different users to rate the same film', function () {
    $film = Film::factory()->create();
    Rating::factory()->create(['film_id' => $film->id]);

    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $this->postJson('/api/v1/users/'.$user->id.'/ratings', [
        'film_id' => $film->id,
        'ball' => 4,
    ])->assertStatus(201);
});

it('validates the rating payload', function () {
    $user = User::factory()->create();
    $film = Film::factory()->create();
    Sanctum::actingAs($user);

    $this->postJson('/api/v1/users/'.$user->id.'/ratings', [])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['film_id', 'ball']);

    $this->postJson('/api/v1/users/'.$user->id.'/ratings', ['film_id' => 9999, 'ball' => 3])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['film_id']);

    $this->postJson('/api/v1/users/'.$user->id.'/ratings', ['film_id' => $film->id, 'ball' => 0])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['ball']);

    $this->postJson('/api/v1/users/'.$user->id.'/ratings', ['film_id' => $film->id, 'ball' => 6])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['ball']);
});

it('does not allow a rating for a deleted film', function () {
    $user = User::factory()->create();
    $film = Film::factory()->create();
    $film->delete();
    Sanctum::actingAs($user);

    $this->postJson('/api/v1/users/'.$user->id.'/ratings', [
        'film_id' => $film->id,
        'ball' => 5,
    ])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['film_id']);
});

it('lists the ratings of the user', function () {
    $user = User::factory()->create();
    $film = Film::factory()->create(['name' => 'Black Mirror']);
    $rating = Rating::factory()->create(['user_id' => $user->id, 'film_id' => $film->id, 'ball' => 5]);
    Rating::factory()->create();

    Sanctum::actingAs($user);

    $this->getJson('/api/v1/users/'.$user->id.'/ratings')
        ->assertOk()
        ->assertJsonCount(1, 'ratings')
        ->assertJsonStructure([
            'ratings' => [
                '*' => ['id', 'film' => ['id', 'name'], 'score', 'created_at'],
            ],
        ])
        ->assertJsonPath('ratings.0.id', $rating->id)
        ->assertJsonPath('ratings.0.film.name', 'Black Mirror')
        ->assertJsonPath('ratings.0.score', 5);
});

it('returns an empty list when the user has no ratings', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $this->getJson('/api/v1/users/'.$user->id.'/ratings')
        ->assertOk()
        ->assertExactJson(['ratings' => []]);
});

it('deletes a rating of the user', function () {
    $user = User::factory()->create();
    $rating = Rating::factory()->create(['user_id' => $user->id]);
    Sanctum::actingAs($user);

    $this->deleteJson('/api/v1/users/'.$user->id.'/ratings/'.$rating->id)
        ->assertNoContent();

    $this->assertDatabaseMissing('ratings', ['id' => $rating->id]);
});

it('returns 404 when the rating does not exist', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $this->deleteJson('/api/v1/users/'.$user->id.'/ratings/9999')
        ->assertStatus(404)
        ->assertExactJson(['message' => 'Rating not found']);
});

it('does not delete a rating that belongs to another user', function () {
    $user = User::factory()->create();
    $foreign = Rating::factory()->create();
    Sanctum::actingAs($user);

    $this->deleteJson('/api/v1/users/'.$user->id.'/ratings/'.$foreign->id)
        ->assertStatus(404)
        ->assertExactJson(['message' => 'Rating not found']);

    $this->assertDatabaseHas('ratings', ['id' => $foreign->id]);
});

it('returns 404 when the user does not exist', function () {
    Sanctum::actingAs(User::factory()->create());

    $this->getJson('/api/v1/users/9999/ratings')
        ->assertStatus(404)
        ->assertExactJson(['message' => 'User not found']);

    $this->postJson('/api/v1/users/9999/ratings', ['film_id' => 1, 'ball' => 5])
        ->assertStatus(404)
        ->assertExactJson(['message' => 'User not found']);

    $this->deleteJson('/api/v1/users/9999/ratings/1')
        ->assertStatus(404)
        ->assertExactJson(['message' => 'User not found']);
});

it('forbids working with the ratings of another user', function () {
    $other = User::factory()->create();
    $rating = Rating::factory()->create(['user_id' => $other->id]);
    $film = Film::factory()->create();
    Sanctum::actingAs(User::factory()->create());

    $this->getJson('/api/v1/users/'.$other->id.'/ratings')->assertStatus(403);

    $this->postJson('/api/v1/users/'.$other->id.'/ratings', [
        'film_id' => $film->id,
        'ball' => 5,
    ])->assertStatus(403);

    $this->deleteJson('/api/v1/users/'.$other->id.'/ratings/'.$rating->id)->assertStatus(403);

    $this->assertDatabaseHas('ratings', ['id' => $rating->id]);
});

it('requires authentication for the rating endpoints', function () {
    $user = User::factory()->create();

    $this->getJson('/api/v1/users/'.$user->id.'/ratings')->assertStatus(401);
    $this->postJson('/api/v1/users/'.$user->id.'/ratings', [])->assertStatus(401);
    $this->deleteJson('/api/v1/users/'.$user->id.'/ratings/1')->assertStatus(401);
});
