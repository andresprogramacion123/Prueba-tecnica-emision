<?php

use App\Enums\EstadoCaso;
use App\Models\Caso;
use App\Models\Cliente;
use Inertia\Testing\AssertableInertia as Assert;

test('guests can view the casos listing page', function () {
    $cliente = Cliente::factory()->create();
    $caso = Caso::factory()->for($cliente)->create();

    $this->get(route('home'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('casos/index')
            ->has('casos.data', 1)
            ->where('casos.data.0.id', $caso->id)
            ->where('casos.data.0.cliente.nombre', $cliente->nombre)
        );
});

test('the casos listing page paginates with a default of 10 per page', function () {
    Caso::factory()->for(Cliente::factory())->count(15)->create();

    $this->get(route('home'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('casos.data', 10)
            ->where('casos.meta.total', 15)
            ->where('casos.meta.last_page', 2)
        );
});

test('the casos listing page defaults filters when no query params are given', function () {
    Caso::factory()->for(Cliente::factory())->create();

    $this->get(route('home'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('filters.search', null)
            ->where('filters.estado', null)
            ->where('filters.sort_by', 'fecha_inicio')
            ->where('filters.sort_dir', 'desc')
        );
});

test('the casos listing page sorts by fecha_inicio ascending and reflects it in filters', function () {
    $cliente = Cliente::factory()->create();
    $antiguo = Caso::factory()->for($cliente)->create(['fecha_inicio' => '2020-01-01']);
    $reciente = Caso::factory()->for($cliente)->create(['fecha_inicio' => '2024-01-01']);

    $this->get(route('home', ['sort_by' => 'fecha_inicio', 'sort_dir' => 'asc']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('casos.data.0.id', $antiguo->id)
            ->where('casos.data.1.id', $reciente->id)
            ->where('filters.sort_by', 'fecha_inicio')
            ->where('filters.sort_dir', 'asc')
        );
});

test('the casos listing page sorts by estado', function () {
    $cliente = Cliente::factory()->create();
    Caso::factory()->for($cliente)->create(['estado' => EstadoCaso::Suspendido]);
    Caso::factory()->for($cliente)->create(['estado' => EstadoCaso::Archivado]);

    $this->get(route('home', ['sort_by' => 'estado', 'sort_dir' => 'asc']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('casos.data.0.estado', 'archivado')
            ->where('casos.data.1.estado', 'suspendido')
        );
});

test('the casos listing page searches by numero_expediente or cliente nombre', function () {
    $cliente = Cliente::factory()->create(['nombre' => 'Maria Fernanda Gomez']);
    $match = Caso::factory()->for($cliente)->create();
    Caso::factory()->for(Cliente::factory()->create(['nombre' => 'Otro Cliente']))->create();

    $this->get(route('home', ['search' => 'fernanda']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('casos.data', 1)
            ->where('casos.data.0.id', $match->id)
            ->where('filters.search', 'fernanda')
        );
});

test('the casos listing page shows no results when the search does not match', function () {
    Caso::factory()->for(Cliente::factory())->create();

    $this->get(route('home', ['search' => 'no-existe-esto']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('casos.data', 0)
        );
});

test('the casos listing page filters by estado exacto', function (EstadoCaso $estado) {
    $cliente = Cliente::factory()->create();
    $match = Caso::factory()->for($cliente)->create(['estado' => $estado]);

    foreach (EstadoCaso::cases() as $otro) {
        if ($otro !== $estado) {
            Caso::factory()->for($cliente)->create(['estado' => $otro]);
        }
    }

    $this->get(route('home', ['estado' => $estado->value]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('casos.data', 1)
            ->where('casos.data.0.id', $match->id)
            ->where('filters.estado', $estado->value)
        );
})->with(EstadoCaso::cases());

test('the casos listing page combines search, estado, sort and pagination', function () {
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

    $this->get(route('home', [
        'search' => 'ana torres',
        'estado' => 'en_tramite',
        'sort_by' => 'fecha_inicio',
        'sort_dir' => 'asc',
        'per_page' => 1,
        'page' => 2,
    ]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('casos.data', 1)
            ->where('casos.data.0.id', $match2->id)
            ->where('casos.meta.total', 2)
            ->where('casos.meta.current_page', 2)
        );
});

test('guests can view the caso detail page with only the id as a prop', function () {
    $caso = Caso::factory()->for(Cliente::factory())->create();

    $this->get(route('casos.show', $caso->id))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('casos/show')
            ->where('id', $caso->id)
            ->missing('caso')
        );
});

test('the caso detail page does not require the caso to exist', function () {
    $this->get(route('casos.show', 999999))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('casos/show')
            ->where('id', 999999)
        );
});

test('the caso detail route rejects non numeric ids', function () {
    $this->get('/casos/not-a-number')->assertNotFound();
});
