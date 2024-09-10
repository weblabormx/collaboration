<?php

namespace WeblaborMx\Collaboration\Tests\Unit;

use App\Models\Park;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PointsTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function a_user_gains_points_when_creating_a_new_park()
    {
        // Crear un usuario
        $user = User::factory()->create();
        $this->actingAs($user);

        // Simular la creación de un parque
        $park = Park::create([
            'name' => 'Central Park',
            'location' => 'New York',
            'description' => 'One of the most famous parks in the world',
        ]);

        $this->assertEquals($user->id, $park->created_by);

        // Verificar que el parque fue creado
        $this->assertDatabaseHas('parks', [
            'name' => 'Central Park',
            'location' => 'New York',
        ]);

        // Verificar que el usuario ganó 50 puntos
        $this->assertEquals(50, $user->points);
    }

    /** @test */
    public function a_user_gains_points_when_correcting_incorrect_park_information()
    {
        // Crear un usuario y un parque
        $user = User::factory()->create();
        $this->actingAs($user);

        $park = Park::factory()->create([
            'name' => 'Central Prak',  // Dato incorrecto a propósito
            'location' => 'New York',
        ]);

        // Another user
        $user = User::factory()->create();
        $this->actingAs($user);

        // Simular corrección del nombre
        $fieldUpdated = $park->updateField('name', 'Central Park');
        $this->assertEquals('PartialUpdate', $fieldUpdated->type);
        $this->assertEquals(20, $fieldUpdated->points);

        // Verificar que el usuario ganó 20 puntos
        $this->assertEquals(20, $user->points);
    }

    /** @test */
    public function a_user_doesnt_gain_more_points_if_he_created_the_place()
    {
        // Crear un usuario y un parque
        $user = User::factory()->create();
        $this->actingAs($user);

        $park = Park::factory()->create([
            'name' => 'Central Prak',  // Dato incorrecto a propósito
            'location' => 'New York',
        ]);

        // Simular corrección del nombre
        $fieldUpdated = $park->updateField('name', 'Central Park');
        $this->assertEquals('PartialUpdate', $fieldUpdated->type);
        $this->assertEquals(0, $fieldUpdated->points);

        // Verificar que el cambio fue realizado, aqui si se verifica ya que fue el mismo que lo creó
        $this->assertDatabaseHas('parks', [
            'name' => 'Central Park',
        ]);

        // Verificar que el usuario ganó 20 puntos
        $this->assertEquals(50, $user->points);
    }

    /** @test */
    public function a_user_gains_points_when_adding_missing_park_information()
    {
        // Crear un usuario y un parque sin descripción
        $user = User::factory()->create();
        $this->actingAs($user);

        $park = Park::factory()->create([
            'name' => 'Central Park',
            'location' => 'New York',
            'description' => null,  // Falta información
        ]);

        // Another user
        $user = User::factory()->create();
        $this->actingAs($user);

        // Simular agregar descripción faltante
        $fieldUpdated = $park->updateField('description', 'One of the most famous parks in the world');
        $this->assertEquals('New', $fieldUpdated->type);
        $this->assertEquals(15, $fieldUpdated->points);

        // Verificar que el usuario ganó 15 puntos
        $this->assertEquals(15, $user->points);
    }

    /** @test */
    public function a_user_gains_points_when_marking_a_park_as_false()
    {
        // Crear un usuario y un parque
        $user = User::factory()->create();
        $this->actingAs($user);

        $park = Park::factory()->create([
            'name' => 'Fictional Park',
            'location' => 'Nowhere',
        ]);

        $user = User::factory()->create();
        $this->actingAs($user);

        // Simular la acción de marcar el parque como falso
        $park->markAsFalse("It's a fictional park");

        // Verificar que el parque está marcado como falso
        $this->assertTrue($park->is_reported);

        // Verificar que el usuario ganó 10 puntos
        $this->assertEquals(10, $user->points);
    }

    /** @test */
    public function a_user_doesnt_gains_points_when_marking_a_park_as_false_if_is_the_same()
    {
        // Crear un usuario y un parque
        $user = User::factory()->create();
        $this->actingAs($user);

        $park = Park::factory()->create([
            'name' => 'Fictional Park',
            'location' => 'Nowhere',
        ]);

        // Simular la acción de marcar el parque como falso
        $park->markAsFalse();

        // Remove the park if the user that created it marked as invalid
        $this->assertTrue(! is_null($park->deleted_at));

        // Verificar que el usuario ganó 10 puntos
        $this->assertEquals(0, $user->points);
    }

    /** @test */
    public function a_user_gains_points_when_validating_park_information()
    {
        // Crear un usuario y un parque
        $user = User::factory()->create();
        $park = Park::factory()->create([
            'name' => 'Central Park',
            'location' => 'New York',
            'description' => 'One of the most famous parks in the world',
            'neighboorhood' => 'Manhattan',
        ]);

        // Simular la validación del parque como correcto
        $field = $park->validateField('neighboorhood');

        // Verificar que la validación fue registrada
        $this->assertFalse($field->is_validated);

        // Verificar que el usuario ganó 5 puntos
        $this->assertEquals(5, $user->points);

        // if 5 users with reputation vote we accept the field
        for ($i = 0; $i < 5; $i++) {
            $user = User::factory()->create();
            $user->addPoints(10);
            $field = $park->validateField('neighboorhood');
        }

        $this->assertTrue($field->is_validated);
    }

    /** @test */
    public function a_user_gains_bonus_points_for_streak_of_successful_modifications()
    {
        // Crear un usuario
        $user = User::factory()->create();

        // Simular 10 modificaciones correctas en parques
        for ($i = 0; $i < 10; $i++) {
            $park = Park::factory()->create();
            $park->update(['name' => 'Corrected Park Name '.$i]);
        }

        // Simular el bono por 10 modificaciones correctas consecutivas
        $user->addPoints(25); // 25 puntos adicionales

        // Verificar que el usuario ganó el bono
        $this->assertEquals(225, $user->points); // 200 puntos por modificaciones + 25 por bono
    }
}
