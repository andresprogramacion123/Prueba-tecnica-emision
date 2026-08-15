<?php

use App\Models\Abogado;
use App\Models\Caso;
use App\Models\Cliente;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

test('guests cannot download the casos por abogado report', function () {
    $this->getJson('/api/reportes/casos-por-abogado')->assertUnauthorized();
});

test('authenticated users can download the excel report', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $abogado = Abogado::factory()->create();
    $caso = Caso::factory()->for(Cliente::factory())->create();
    $abogado->casos()->attach($caso);

    $response = $this->get('/api/reportes/casos-por-abogado');

    $response->assertSuccessful();
    $response->assertDownload();
    $response->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
});
