<?php

use App\Models\Gender;
use App\Models\Rating;
use App\Models\Review;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

it('returns the profile of the current user', function () {
    $gender = Gender::factory()->create(['name' => 'Мужской']);
    $user = User::factory()->create([
        'fio' => 'Ivanov Ivan',
        'email' => 'ivanov@ivan.kz',
        'birthday' => '2006-04-15',
        'gender_id' => $gender->id,
    ]);

    Review::factory()->count(2)->create(['user_id' => $user->id]);
    Rating::factory()->count(3)->create(['user_id' => $user->id]);
    Review::factory()->create();
    Rating::factory()->create();

    Sanctum::actingAs($user);

    $this->getJson('/api/v1/users/'.$user->id)
        ->assertOk()
        ->assertExactJson([
            'id' => $user->id,
            'fio' => 'Ivanov Ivan',
            'email' => 'ivanov@ivan.kz',
            'birthday' => '2006-04-15',
            'gender' => ['id' => $gender->id, 'name' => 'Мужской'],
            'reviewCount' => 2,
            'ratingCount' => 3,
        ]);
});

it('returns 404 when the user does not exist', function () {
    Sanctum::actingAs(User::factory()->create());

    $this->getJson('/api/v1/users/9999')
        ->assertStatus(404)
        ->assertExactJson(['message' => 'User not found']);
});

it('forbids reading the profile of another user', function () {
    $other = User::factory()->create();
    Sanctum::actingAs(User::factory()->create());

    $this->getJson('/api/v1/users/'.$other->id)
        ->assertStatus(403)
        ->assertExactJson(['message' => 'Forbidden']);
});

it('requires authentication to read a profile', function () {
    $user = User::factory()->create();

    $this->getJson('/api/v1/users/'.$user->id)
        ->assertStatus(401)
        ->assertExactJson(['message' => 'Unauthenticated']);
});

it('updates the personal data of the current user', function () {
    $user = User::factory()->create(['email' => 'old@ivan.kz']);
    $gender = Gender::factory()->create();
    Sanctum::actingAs($user);

    $this->putJson('/api/v1/users', [
        'fio' => 'Petrov Petr',
        'email' => 'new@ivan.kz',
        'birthday' => '2001-02-03',
        'gender_id' => $gender->id,
    ])
        ->assertOk()
        ->assertExactJson(['status' => 'success']);

    $fresh = $user->fresh();

    expect($fresh->fio)->toBe('Petrov Petr');
    expect($fresh->email)->toBe('new@ivan.kz');
    expect($fresh->birthday->format('Y-m-d'))->toBe('2001-02-03');
    expect($fresh->gender_id)->toBe($gender->id);
});

it('allows keeping the current email when updating', function () {
    $user = User::factory()->create(['email' => 'same@ivan.kz']);
    Sanctum::actingAs($user);

    $this->putJson('/api/v1/users', [
        'fio' => 'Same Email',
        'email' => 'same@ivan.kz',
        'birthday' => '2001-02-03',
        'gender_id' => $user->gender_id,
    ])->assertOk();
});

it('rejects an email that belongs to another user', function () {
    User::factory()->create(['email' => 'taken@ivan.kz']);
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $this->putJson('/api/v1/users', [
        'fio' => 'Ivanov Ivan',
        'email' => 'taken@ivan.kz',
        'birthday' => '2001-02-03',
        'gender_id' => $user->gender_id,
    ])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['email']);
});

it('validates the update payload', function () {
    Sanctum::actingAs(User::factory()->create());

    $this->putJson('/api/v1/users', [])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['fio', 'email', 'birthday', 'gender_id']);

    $this->putJson('/api/v1/users', [
        'fio' => 'I',
        'email' => 'bad',
        'birthday' => 'nope',
        'gender_id' => 9999,
    ])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['fio', 'email', 'birthday', 'gender_id']);
});

it('requires authentication to update the profile', function () {
    $this->putJson('/api/v1/users', [])->assertStatus(401);
});

it('deletes the account of the current user', function () {
    $user = User::factory()->create();
    $user->createToken('one');
    $user->createToken('two');
    Sanctum::actingAs($user);

    $this->deleteJson('/api/v1/users')
        ->assertNoContent();

    $this->assertSoftDeleted($user);
    $this->assertDatabaseCount('personal_access_tokens', 0);
});

it('requires authentication to delete the account', function () {
    $this->deleteJson('/api/v1/users')->assertStatus(401);
});
