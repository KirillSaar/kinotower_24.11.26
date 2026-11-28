<?php

use App\Models\User;

it('signs in with correct credentials', function () {
    $user = User::factory()->create([
        'fio' => 'Ivanov Ivan',
        'email' => 'ivanov@ivan.kz',
        'password' => 'asdf1234',
    ]);

    $this->postJson('/api/v1/auth/signin', [
        'email' => 'ivanov@ivan.kz',
        'password' => 'asdf1234',
    ])
        ->assertOk()
        ->assertJsonStructure(['status', 'token', 'id', 'fio'])
        ->assertJsonPath('status', 'success')
        ->assertJsonPath('id', $user->id)
        ->assertJsonPath('fio', 'Ivanov Ivan');
});

it('returns a token that works on protected endpoints', function () {
    $user = User::factory()->create(['email' => 'ivanov@ivan.kz', 'password' => 'asdf1234']);

    $token = $this->postJson('/api/v1/auth/signin', [
        'email' => 'ivanov@ivan.kz',
        'password' => 'asdf1234',
    ])->json('token');

    $this->withToken($token)
        ->getJson('/api/v1/users/'.$user->id)
        ->assertOk()
        ->assertJsonPath('id', $user->id);
});

it('rejects a wrong password', function () {
    User::factory()->create(['email' => 'ivanov@ivan.kz', 'password' => 'asdf1234']);

    $this->postJson('/api/v1/auth/signin', [
        'email' => 'ivanov@ivan.kz',
        'password' => 'wrong-password',
    ])
        ->assertStatus(401)
        ->assertExactJson([
            'status' => 'invalid',
            'message' => 'Wrong email or password',
        ]);
});

it('rejects an unknown email', function () {
    $this->postJson('/api/v1/auth/signin', [
        'email' => 'nobody@ivan.kz',
        'password' => 'asdf1234',
    ])
        ->assertStatus(401)
        ->assertExactJson([
            'status' => 'invalid',
            'message' => 'Wrong email or password',
        ]);
});

it('does not sign in a deleted user', function () {
    $user = User::factory()->create(['email' => 'ivanov@ivan.kz', 'password' => 'asdf1234']);
    $user->delete();

    $this->postJson('/api/v1/auth/signin', [
        'email' => 'ivanov@ivan.kz',
        'password' => 'asdf1234',
    ])->assertStatus(401);
});

it('validates the signin payload', function () {
    $this->postJson('/api/v1/auth/signin', [])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['email', 'password']);

    $this->postJson('/api/v1/auth/signin', ['email' => 'ivanov@ivan.kz', 'password' => '123'])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['password']);
});

it('signs out and removes the current token', function () {
    User::factory()->create(['email' => 'ivanov@ivan.kz', 'password' => 'asdf1234']);

    $token = $this->postJson('/api/v1/auth/signin', [
        'email' => 'ivanov@ivan.kz',
        'password' => 'asdf1234',
    ])->json('token');

    $this->assertDatabaseCount('personal_access_tokens', 1);

    $this->withToken($token)
        ->postJson('/api/v1/auth/signout')
        ->assertOk()
        ->assertExactJson(['status' => 'success']);

    $this->assertDatabaseCount('personal_access_tokens', 0);
});

it('requires authentication to sign out', function () {
    $this->postJson('/api/v1/auth/signout')->assertStatus(401);
});

it('rejects a garbage bearer token', function () {
    $this->withToken('garbage-token')
        ->postJson('/api/v1/auth/signout')
        ->assertStatus(401);
});

it('rejects the token of a deleted user', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test')->plainTextToken;
    $user->delete();

    $this->withToken($token)
        ->getJson('/api/v1/users/'.$user->id)
        ->assertStatus(401);
});
