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

test('guests can list casos without authentication', function () {
    Caso::factory()->for(Cliente::factory())->create();

    $this->getJson('/api/casos')->assertSuccessful();
});

test('the casos listing defaults to 10 per page and includes pagination meta', function () {
    Caso::factory()->for(Cliente::factory())->count(15)->create();

    $response = $this->getJson('/api/casos');

    $response->assertSuccessful();
    $response->assertJsonCount(10, 'data');
    $response->assertJsonPath('meta.per_page', 10);
    $response->assertJsonPath('meta.total', 15);
    $response->assertJsonPath('meta.last_page', 2);
});

test('the casos listing respects the per_page query parameter', function () {
    Caso::factory()->for(Cliente::factory())->count(5)->create();

    $response = $this->getJson('/api/casos?per_page=2');

    $response->assertSuccessful();
    $response->assertJsonCount(2, 'data');
    $response->assertJsonPath('meta.per_page', 2);
});

test('the casos listing orders casos by fecha_inicio descending', function () {
    $cliente = Cliente::factory()->create();
    $antiguo = Caso::factory()->for($cliente)->create(['fecha_inicio' => '2020-01-01']);
    $reciente = Caso::factory()->for($cliente)->create(['fecha_inicio' => '2024-01-01']);

    $response = $this->getJson('/api/casos');

    $response->assertJsonPath('data.0.id', $reciente->id);
    $response->assertJsonPath('data.1.id', $antiguo->id);
});

test('the casos listing excludes soft deleted casos', function () {
    $cliente = Cliente::factory()->create();
    $activo = Caso::factory()->for($cliente)->create();
    $eliminado = Caso::factory()->for($cliente)->create();
    $eliminado->delete();

    $response = $this->getJson('/api/casos');

    $response->assertJsonCount(1, 'data');
    $response->assertJsonPath('data.0.id', $activo->id);
});
