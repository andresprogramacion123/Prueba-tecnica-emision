<?php

use App\Enums\EstadoCaso;
use App\Models\Abogado;
use App\Models\Caso;
use App\Models\Cliente;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

test('guests cannot access the caso endpoint', function () {
    $caso = Caso::factory()->for(Cliente::factory())->create();

    $this->getJson("/api/casos/{$caso->id}")->assertUnauthorized();
});

test('authenticated users can fetch a caso with its cliente and abogados', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $cliente = Cliente::factory()->create();
    $abogados = Abogado::factory()->count(2)->create();
    $caso = Caso::factory()->for($cliente)->create([
        'numero_expediente' => '2024-CIV-000123',
        'estado' => EstadoCaso::EnTramite,
    ]);
    $caso->abogados()->attach($abogados);

    $response = $this->getJson("/api/casos/{$caso->id}");

    $response->assertSuccessful();
    $response->assertJson([
        'data' => [
            'id' => $caso->id,
            'numero_expediente' => '2024-CIV-000123',
            'estado' => 'en_tramite',
            'cliente' => [
                'id' => $cliente->id,
                'cedula' => $cliente->cedula,
                'nombre' => $cliente->nombre,
            ],
        ],
    ]);
    $response->assertJsonCount(2, 'data.abogados');
});

test('a valid bearer token generated via createToken can access the caso endpoint', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test-token')->plainTextToken;
    $caso = Caso::factory()->for(Cliente::factory())->create();

    $response = $this->withToken($token)->getJson("/api/casos/{$caso->id}");

    $response->assertSuccessful();
});

test('returns 404 when the caso does not exist', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $this->getJson('/api/casos/999999')->assertNotFound();
});

test('returns 404 when the caso is soft deleted', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $caso = Caso::factory()->for(Cliente::factory())->create();
    $caso->delete();

    $this->getJson("/api/casos/{$caso->id}")->assertNotFound();
});
