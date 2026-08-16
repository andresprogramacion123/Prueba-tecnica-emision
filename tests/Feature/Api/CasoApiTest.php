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

test('the casos listing sorts by fecha_inicio ascending', function () {
    $cliente = Cliente::factory()->create();
    $antiguo = Caso::factory()->for($cliente)->create(['fecha_inicio' => '2020-01-01']);
    $reciente = Caso::factory()->for($cliente)->create(['fecha_inicio' => '2024-01-01']);

    $response = $this->getJson('/api/casos?sort_by=fecha_inicio&sort_dir=asc');

    $response->assertJsonPath('data.0.id', $antiguo->id);
    $response->assertJsonPath('data.1.id', $reciente->id);
});

test('the casos listing defaults sort_dir to desc when only sort_by is given', function () {
    $cliente = Cliente::factory()->create();
    $antiguo = Caso::factory()->for($cliente)->create(['fecha_inicio' => '2020-01-01']);
    $reciente = Caso::factory()->for($cliente)->create(['fecha_inicio' => '2024-01-01']);

    $response = $this->getJson('/api/casos?sort_by=fecha_inicio');

    $response->assertJsonPath('data.0.id', $reciente->id);
    $response->assertJsonPath('data.1.id', $antiguo->id);
});

test('the casos listing sorts by estado ascending and descending', function () {
    $cliente = Cliente::factory()->create();
    Caso::factory()->for($cliente)->create(['estado' => EstadoCaso::Archivado]);
    Caso::factory()->for($cliente)->create(['estado' => EstadoCaso::EnTramite]);
    Caso::factory()->for($cliente)->create(['estado' => EstadoCaso::Finalizado]);
    Caso::factory()->for($cliente)->create(['estado' => EstadoCaso::Suspendido]);

    $asc = $this->getJson('/api/casos?sort_by=estado&sort_dir=asc');
    $asc->assertJsonPath('data.0.estado', 'archivado');
    $asc->assertJsonPath('data.3.estado', 'suspendido');

    $desc = $this->getJson('/api/casos?sort_by=estado&sort_dir=desc');
    $desc->assertJsonPath('data.0.estado', 'suspendido');
    $desc->assertJsonPath('data.3.estado', 'archivado');
});

test('the casos listing falls back to fecha_inicio desc for an invalid sort_by', function () {
    $cliente = Cliente::factory()->create();
    $antiguo = Caso::factory()->for($cliente)->create(['fecha_inicio' => '2020-01-01']);
    $reciente = Caso::factory()->for($cliente)->create(['fecha_inicio' => '2024-01-01']);

    $response = $this->getJson('/api/casos?sort_by=numero_expediente');

    $response->assertJsonPath('data.0.id', $reciente->id);
    $response->assertJsonPath('data.1.id', $antiguo->id);
});

test('the casos listing searches by numero_expediente, partial and case-insensitive', function () {
    $cliente = Cliente::factory()->create();
    $match = Caso::factory()->for($cliente)->create(['numero_expediente' => '2024-CIV-000123']);
    Caso::factory()->for($cliente)->create(['numero_expediente' => '2024-PEN-000999']);

    $response = $this->getJson('/api/casos?search=civ-000123');

    $response->assertJsonCount(1, 'data');
    $response->assertJsonPath('data.0.id', $match->id);
});

test('the casos listing searches by cliente nombre, partial and case-insensitive', function () {
    $clienteMatch = Cliente::factory()->create(['nombre' => 'Maria Fernanda Gomez']);
    $clienteOtro = Cliente::factory()->create(['nombre' => 'Carlos Perez']);
    $match = Caso::factory()->for($clienteMatch)->create();
    Caso::factory()->for($clienteOtro)->create();

    $response = $this->getJson('/api/casos?search=FERNANDA');

    $response->assertJsonCount(1, 'data');
    $response->assertJsonPath('data.0.id', $match->id);
});

test('the casos listing search returns no results when nothing matches', function () {
    Caso::factory()->for(Cliente::factory())->create();

    $response = $this->getJson('/api/casos?search=no-existe-esto');

    $response->assertJsonCount(0, 'data');
});

test('the casos listing filters by estado exacto', function (EstadoCaso $estado) {
    $cliente = Cliente::factory()->create();
    $match = Caso::factory()->for($cliente)->create(['estado' => $estado]);

    foreach (EstadoCaso::cases() as $otro) {
        if ($otro !== $estado) {
            Caso::factory()->for($cliente)->create(['estado' => $otro]);
        }
    }

    $response = $this->getJson("/api/casos?estado={$estado->value}");

    $response->assertJsonCount(1, 'data');
    $response->assertJsonPath('data.0.id', $match->id);
})->with(EstadoCaso::cases());

test('the casos listing combines search, estado, sort and pagination', function () {
    $cliente = Cliente::factory()->create(['nombre' => 'Ana Torres']);
    $otroCliente = Cliente::factory()->create(['nombre' => 'Ana Torres']);

    $match1 = Caso::factory()->for($cliente)->create([
        'estado' => EstadoCaso::EnTramite,
        'fecha_inicio' => '2022-01-01',
    ]);
    $match2 = Caso::factory()->for($otroCliente)->create([
        'estado' => EstadoCaso::EnTramite,
        'fecha_inicio' => '2023-01-01',
    ]);
    Caso::factory()->for($cliente)->create(['estado' => EstadoCaso::Archivado]);
    Caso::factory()->for(Cliente::factory()->create(['nombre' => 'Otro Nombre']))->create([
        'estado' => EstadoCaso::EnTramite,
    ]);

    $response = $this->getJson(
        '/api/casos?search=ana+torres&estado=en_tramite&sort_by=fecha_inicio&sort_dir=asc&per_page=1&page=2'
    );

    $response->assertJsonCount(1, 'data');
    $response->assertJsonPath('data.0.id', $match2->id);
    $response->assertJsonPath('meta.total', 2);
    $response->assertJsonPath('meta.current_page', 2);
});
