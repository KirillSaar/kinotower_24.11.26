<?php

use App\Models\Film;
use App\Models\Review;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

it('creates a review for a film', function () {
    $user = User::factory()->create();
    $film = Film::factory()->create(['name' => 'Black Mirror']);
    Sanctum::actingAs($user);

    $response = $this->postJson('/api/v1/users/'.$user->id.'/reviews', [
        'film_id' => $film->id,
        'message' => 'Very Good!',
    ])
        ->assertStatus(201)
        ->assertJsonStructure([
            'id',
            'film' => ['id', 'name'],
            'message',
            'is_approved',
            'created_at',
        ])
        ->assertJsonPath('film.id', $film->id)
        ->assertJsonPath('film.name', 'Black Mirror')
        ->assertJsonPath('message', 'Very Good!')
        ->assertJsonPath('is_approved', 0);

    expect($response->json('created_at'))
        ->toMatch('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}\.\d{3}Z$/');

    $this->assertDatabaseHas('reviews', [
        'id' => $response->json('id'),
        'film_id' => $film->id,
        'user_id' => $user->id,
        'message' => 'Very Good!',
        'is_approved' => 0,
    ]);
});

it('validates the review payload', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $this->postJson('/api/v1/users/'.$user->id.'/reviews', [])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['film_id', 'message']);

    $this->postJson('/api/v1/users/'.$user->id.'/reviews', [
        'film_id' => 9999,
        'message' => 'abc',
    ])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['film_id', 'message']);

    $this->postJson('/api/v1/users/'.$user->id.'/reviews', [
        'film_id' => Film::factory()->create()->id,
        'message' => str_repeat('a', 1025),
    ])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['message']);
});

it('does not allow a review for a deleted film', function () {
    $user = User::factory()->create();
    $film = Film::factory()->create();
    $film->delete();
    Sanctum::actingAs($user);

    $this->postJson('/api/v1/users/'.$user->id.'/reviews', [
        'film_id' => $film->id,
        'message' => 'Very Good!',
    ])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['film_id']);
});

it('lists the reviews of the user', function () {
    $user = User::factory()->create();
    $film = Film::factory()->create(['name' => 'Black Mirror']);

    $approved = Review::factory()->approved()->create([
        'user_id' => $user->id,
        'film_id' => $film->id,
        'message' => 'Very Good!',
    ]);
    $pending = Review::factory()->create(['user_id' => $user->id, 'film_id' => $film->id]);
    Review::factory()->create();

    Sanctum::actingAs($user);

    $this->getJson('/api/v1/users/'.$user->id.'/reviews')
        ->assertOk()
        ->assertJsonCount(2, 'reviews')
        ->assertJsonStructure([
            'reviews' => [
                '*' => ['id', 'film' => ['id', 'name'], 'message', 'is_approved', 'created_at'],
            ],
        ])
        ->assertJsonPath('reviews.0.id', $approved->id)
        ->assertJsonPath('reviews.0.film.name', 'Black Mirror')
        ->assertJsonPath('reviews.0.is_approved', 1)
        ->assertJsonPath('reviews.1.id', $pending->id)
        ->assertJsonPath('reviews.1.is_approved', 0);
});

it('returns an empty list when the user has no reviews', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $this->getJson('/api/v1/users/'.$user->id.'/reviews')
        ->assertOk()
        ->assertExactJson(['reviews' => []]);
});

it('deletes a review of the user', function () {
    $user = User::factory()->create();
    $review = Review::factory()->create(['user_id' => $user->id]);
    Sanctum::actingAs($user);

    $this->deleteJson('/api/v1/users/'.$user->id.'/reviews/'.$review->id)
        ->assertNoContent();

    $this->assertSoftDeleted($review);
});

it('returns 404 when the review does not exist', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $this->deleteJson('/api/v1/users/'.$user->id.'/reviews/9999')
        ->assertStatus(404)
        ->assertExactJson(['message' => 'Review not found']);
});

it('does not delete a review that belongs to another user', function () {
    $user = User::factory()->create();
    $foreign = Review::factory()->create();
    Sanctum::actingAs($user);

    $this->deleteJson('/api/v1/users/'.$user->id.'/reviews/'.$foreign->id)
        ->assertStatus(404)
        ->assertExactJson(['message' => 'Review not found']);

    $this->assertNotSoftDeleted($foreign);
});

it('returns 404 when the user does not exist', function () {
    Sanctum::actingAs(User::factory()->create());

    $this->getJson('/api/v1/users/9999/reviews')
        ->assertStatus(404)
        ->assertExactJson(['message' => 'User not found']);

    $this->postJson('/api/v1/users/9999/reviews', ['film_id' => 1, 'message' => 'Very Good!'])
        ->assertStatus(404)
        ->assertExactJson(['message' => 'User not found']);

    $this->deleteJson('/api/v1/users/9999/reviews/1')
        ->assertStatus(404)
        ->assertExactJson(['message' => 'User not found']);
});

it('forbids working with the reviews of another user', function () {
    $other = User::factory()->create();
    $review = Review::factory()->create(['user_id' => $other->id]);
    $film = Film::factory()->create();
    Sanctum::actingAs(User::factory()->create());

    $this->getJson('/api/v1/users/'.$other->id.'/reviews')->assertStatus(403);

    $this->postJson('/api/v1/users/'.$other->id.'/reviews', [
        'film_id' => $film->id,
        'message' => 'Very Good!',
    ])->assertStatus(403);

    $this->deleteJson('/api/v1/users/'.$other->id.'/reviews/'.$review->id)->assertStatus(403);

    $this->assertNotSoftDeleted($review);
});

it('requires authentication for the review endpoints', function () {
    $user = User::factory()->create();

    $this->getJson('/api/v1/users/'.$user->id.'/reviews')->assertStatus(401);
    $this->postJson('/api/v1/users/'.$user->id.'/reviews', [])->assertStatus(401);
    $this->deleteJson('/api/v1/users/'.$user->id.'/reviews/1')->assertStatus(401);
});
