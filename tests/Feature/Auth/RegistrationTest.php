<?php

test('registration screen can be rendered', function () {
    $response = $this->get('/register');

    $response->assertStatus(200);
});

test('new users can register', function () {
    $response = $this->post('/register', [
        'name' => 'Test User',
        'cpf' => '123.456.789-00',
        'phone' => '(15) 99876-5432',
        'cep' => '18035-410',
        'city' => 'Sorocaba',
        'neighborhood' => 'Jardim das Flores',
        'street' => 'Rua das Acácias, 120',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('dashboard', absolute: false));
    $this->assertDatabaseHas('users', [
        'email' => 'test@example.com',
        'cpf' => '123.456.789-00',
        'city' => 'Sorocaba',
    ]);
});

test('registration requires profile fields', function () {
    $this->post('/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ])->assertSessionHasErrors(['cpf', 'phone', 'cep', 'city', 'neighborhood', 'street']);
});
