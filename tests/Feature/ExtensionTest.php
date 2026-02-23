<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

use App\Models\User;
use App\Models\Receta;
use App\Models\Ingrediente;
use App\Models\Comentario;
use Laravel\Sanctum\Sanctum;

class ExtensionTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function a_user_can_add_ingredients_to_their_recipe()
    {
        $user = User::factory()->create();
        $receta = Receta::factory()->create(['user_id' => $user->id]);

        Sanctum::actingAs($user);

        $response = $this->postJson("/api/recetas/{$receta->id}/ingredientes", [
            'nombre' => 'Sal',
            'cantidad' => 5.5,
            'unidad' => 'g',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure(['data' => ['id', 'nombre', 'cantidad', 'unidad']]);

        $this->assertDatabaseHas('ingredientes', [
            'receta_id' => $receta->id,
            'nombre' => 'Sal',
        ]);
    }

    /** @test */
    public function a_user_cannot_add_ingredients_to_others_recipe()
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $receta = Receta::factory()->create(['user_id' => $otherUser->id]);

        Sanctum::actingAs($user);

        $response = $this->postJson("/api/recetas/{$receta->id}/ingredientes", [
            'nombre' => 'Azúcar',
            'cantidad' => 10,
            'unidad' => 'g',
        ]);

        $response->assertStatus(403);
    }

    /** @test */
    public function a_user_can_like_and_unlike_a_recipe()
    {
        $user = User::factory()->create();
        $receta = Receta::factory()->create();

        Sanctum::actingAs($user);

        // Like
        $response = $this->postJson("/api/recetas/{$receta->id}/likes");
        $response->assertCreated();
        $this->assertEquals(1, $receta->likes()->count());

        // Unlike
        $response = $this->deleteJson("/api/recetas/{$receta->id}/likes");
        $response->assertOk();
        $this->assertEquals(0, $receta->likes()->count());
    }

    /** @test */
    public function a_user_can_comment_on_a_recipe()
    {
        $user = User::factory()->create();
        $receta = Receta::factory()->create();

        Sanctum::actingAs($user);

        $response = $this->postJson("/api/recetas/{$receta->id}/comentarios", [
            'texto' => '¡Increíble receta!',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure(['data' => ['id', 'user_name', 'texto']]);

        $this->assertDatabaseHas('comentarios', [
            'receta_id' => $receta->id,
            'user_id' => $user->id,
            'texto' => '¡Increíble receta!',
        ]);
    }

    /** @test */
    public function a_user_can_delete_their_own_comment()
    {
        $user = User::factory()->create();
        $receta = Receta::factory()->create();
        $comentario = Comentario::create([
            'receta_id' => $receta->id,
            'user_id' => $user->id,
            'texto' => 'Bórrame',
        ]);

        Sanctum::actingAs($user);

        $response = $this->deleteJson("/api/comentarios/{$comentario->id}");
        $response->assertStatus(200);

        $this->assertDatabaseMissing('comentarios', ['id' => $comentario->id]);
    }

    /** @test */
    public function a_user_cannot_delete_others_comment()
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $receta = Receta::factory()->create();
        $comentario = Comentario::create([
            'receta_id' => $receta->id,
            'user_id' => $otherUser->id,
            'texto' => 'No puedes borrarme',
        ]);

        Sanctum::actingAs($user);

        $response = $this->deleteJson("/api/comentarios/{$comentario->id}");
        $response->assertStatus(403);
    }

    /** @test */
    public function a_user_can_upload_an_image_to_their_recipe()
    {
        \Illuminate\Support\Facades\Storage::fake('public');
        
        $user = User::factory()->create();
        $receta = Receta::factory()->create(['user_id' => $user->id]);

        Sanctum::actingAs($user);

        $file = \Illuminate\Http\UploadedFile::fake()->create('receta.jpg', 100, 'image/jpeg');

        $response = $this->postJson("/api/recetas/{$receta->id}/imagen", [
            'imagen' => $file,
        ]);

        $response->assertStatus(200);
        $this->assertNotNull($receta->fresh()->imagen);
        \Illuminate\Support\Facades\Storage::disk('public')->assertExists($receta->fresh()->imagen);
    }

    /** @test */
    public function a_user_can_filter_recetas_by_min_likes()
    {
        $user = User::factory()->create();
        $receta1 = Receta::factory()->create();
        $receta2 = Receta::factory()->create();
        
        $receta1->likes()->attach($user->id); // 1 like

        Sanctum::actingAs($user);

        $response = $this->getJson("/api/recetas?min_likes=1");
        $response->assertJsonCount(1, 'data');
        $response->assertJsonFragment(['id' => $receta1->id]);
    }

    /** @test */
    public function a_user_can_search_recetas_by_ingredient_name()
    {
        $user = User::factory()->create();
        $receta = Receta::factory()->create(['titulo' => 'Pasta']);
        $receta->ingredientes()->create([
            'nombre' => 'Tomate frito',
            'cantidad' => 100,
            'unidad' => 'ml'
        ]);

        Sanctum::actingAs($user);

        $response = $this->getJson("/api/recetas?q=Tomate");
        $response->assertJsonCount(1, 'data');
        $response->assertJsonFragment(['id' => $receta->id]);
    }
}
