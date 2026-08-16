<?php

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
