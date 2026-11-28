<?php

use App\Models\Gender;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

function signupPayload(array $overrides = []): array
{
    return array_merge([
        'fio' => 'Ivanov Ivan',
        'email' => 'ivanov@ivan.kz',
        'password' => 'asdf1234',
        'birthday' => '2006-04-15',
        'gender_id' => Gender::factory()->create()->id,
    ], $overrides);
}

it('registers a new user and returns a token', function () {
    $payload = signupPayload();

    $response = $this->postJson('/api/v1/auth/signup', $payload)
        ->assertStatus(201)
        ->assertJsonStructure(['status', 'token', 'id', 'fio'])
        ->assertJsonPath('status', 'success')
        ->assertJsonPath('fio', 'Ivanov Ivan');

    $user = User::where('email', 'ivanov@ivan.kz')->first();

    expect($user)->not->toBeNull();
    expect($response->json('id'))->toBe($user->id);
    expect($response->json('token'))->toBeString()->not->toBeEmpty();
    expect(Hash::check('asdf1234', $user->password))->toBeTrue();
    expect($user->birthday->format('Y-m-d'))->toBe('2006-04-15');
    expect($user->gender_id)->toBe($payload['gender_id']);
});

it('issues a token that can be used on protected endpoints', function () {
    $token = $this->postJson('/api/v1/auth/signup', signupPayload())->json('token');

    $this->withToken($token)
        ->getJson('/api/v1/users/'.User::first()->id)
        ->assertOk();
});

it('requires all fields', function () {
    $this->postJson('/api/v1/auth/signup', [])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['fio', 'email', 'password', 'birthday', 'gender_id']);
});

it('rejects a fio that is too short or too long', function () {
    $this->postJson('/api/v1/auth/signup', signupPayload(['fio' => 'I']))
        ->assertStatus(422)
        ->assertJsonValidationErrors(['fio']);

    $this->postJson('/api/v1/auth/signup', signupPayload(['fio' => str_repeat('a', 151)]))
        ->assertStatus(422)
        ->assertJsonValidationErrors(['fio']);
});

it('rejects an invalid or too long email', function () {
    $this->postJson('/api/v1/auth/signup', signupPayload(['email' => 'not-an-email']))
        ->assertStatus(422)
        ->assertJsonValidationErrors(['email']);

    $this->postJson('/api/v1/auth/signup', signupPayload(['email' => str_repeat('a', 45).'@b.kz']))
        ->assertStatus(422)
        ->assertJsonValidationErrors(['email']);
});

it('rejects an email that is already taken', function () {
    User::factory()->create(['email' => 'ivanov@ivan.kz']);

    $this->postJson('/api/v1/auth/signup', signupPayload())
        ->assertStatus(422)
        ->assertJsonValidationErrors(['email']);
});

it('rejects a password shorter than 6 characters', function () {
    $this->postJson('/api/v1/auth/signup', signupPayload(['password' => '12345']))
        ->assertStatus(422)
        ->assertJsonValidationErrors(['password']);
});

it('rejects an invalid birthday', function () {
    $this->postJson('/api/v1/auth/signup', signupPayload(['birthday' => 'yesterday-ish']))
        ->assertStatus(422)
        ->assertJsonValidationErrors(['birthday']);
});

it('rejects a gender that does not exist', function () {
    $this->postJson('/api/v1/auth/signup', signupPayload(['gender_id' => 9999]))
        ->assertStatus(422)
        ->assertJsonValidationErrors(['gender_id']);
});
