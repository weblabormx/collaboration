<?php
namespace WeblaborMx\Collaboration\Tests\Unit;

use App\Models\Park;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VisibilityTest extends TestCase
{
    use RefreshDatabase;

   /** @test */
   public function a_new_user_sees_their_own_park_but_its_not_publicly_visible_until_validated()
   {
       // Crear un usuario nuevo sin validaciones previas
       $user = User::factory()->create();
       $this->actingAs($user);

       // Simular la creación de un parque
       $park = Park::create([
           'name' => 'Hidden Park',
           'location' => 'Unknown Location',
       ]);

       // Verificar que el usuario puede ver su propio parque
       $this->assertTrue($park->is_visible);

       // Verificar que el parque no es visible para otros usuarios
       $otherUser = User::factory()->create();
       $this->actingAs($user);
       $this->assertFalse($park->is_visible);
   }

    /** @test */
    public function an_admin_or_validated_user_can_validate_and_publish_a_park()
    {
        // Crear un usuario nuevo y un administrador
        $user = User::factory()->create();
        $this->actingAs($user);
        
        // Crear un parque que aún no está validado
        $park = Park::create([
            'name' => 'Hidden Park',
            'location' => 'Unknown Location',
        ]);

        $this->assertFalse($park->is_validated);

        // El parque no debe ser visible para otros usuarios
        $otherUser = User::factory()->create();
        $this->actingAs($otherUser);
        $this->assertFalse($park->is_visible);

        // Simular la validación por parte de un administrador o usuario con suficientes puntos
        $admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($admin);
        $park->validate();

        // Verificar que el parque ahora es visible para todos los usuarios
        $this->assertTrue($park->is_visible);
        $this->assertTrue($park->is_validated);
    }

    /** @test */
    public function a_validated_user_can_publish_a_park_immediately_without_validation()
    {
        // Crear un usuario validado (suficientes puntos o buenas colaboraciones)
        $validatedUser = User::factory()->create(['reputation' => 100]);
        $this->actingAs($validatedUser);

        // Crear un parque como usuario validado
        $park = Park::create([
            'name' => 'Public Park',
            'location' => 'Known Location',
        ]);

        // Verificar que el parque es visible inmediatamente para otros usuarios
        $otherUser = User::factory()->create();
        $this->actingAs($otherUser);
        $this->assertTrue($park->is_visible);

        // Verificar que el parque está marcado como validado
        $this->assertTrue($park->is_validated);
    }

    /** @test */
    public function a_new_user_has_data_validated_then_publishes_immediately_after_becoming_validated()
    {
        // Crear un usuario nuevo
        $user = User::factory()->create();
        $this->actingAs($user);

        // Crear un parque que no se publica inmediatamente
        $park = Park::create([
            'name' => 'Pending Park',
            'location' => 'Unknown Location',
        ]);

        // Verificar que no es visible para otros usuarios
        $otherUser = User::factory()->create();
        $this->actingAs($otherUser);
        $this->assertFalse($park->is_visible);

        // Admin valida el parque
        $admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($admin);
        $park->validate();
        $this->assertTrue($park->is_validated);
        $this->assertTrue($park->is_visible);
    }
}