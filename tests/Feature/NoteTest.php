<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Note;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NoteTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test note creation.
     *
     * @return void
     */
    public function test_user_can_create_note()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $data = [
            'title' => 'Test Note',
            'content' => 'This is the content of the test note.'
        ];

        $response = $this->postJson('/api/notes', $data);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'note' => [
                'id',
                'user_id',
                'title',
                'content',
                'created_at',
                'updated_at',
            ],
            'message'
        ]);

        $this->assertDatabaseHas('notes', [
            'user_id' => $user->id,
            'title' => 'Test Note',
            'content' => 'This is the content of the test note.'
        ]);
    }

    /**
     * Test validation errors on note creation.
     *
     * @return void
     */
    public function test_note_creation_validation_errors()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $data = [
            'title' => '',
            'content' => 'Short'
        ];

        $response = $this->postJson('/api/notes', $data);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['title', 'content']);
    }

    /**
     * Test note creation with unauthenticated user.
     *
     * @return void
     */
    public function test_unauthenticated_user_cannot_create_note()
    {
        $data = [
            'title' => 'Test Note',
            'content' => 'This is the content of the test note.'
        ];

        $response = $this->postJson('/api/notes', $data);

        $response->assertStatus(401);
    }

    /**
     * Test handling of internal server error during note creation.
     *
     * @return void
     */
    public function test_internal_server_error_handling()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // Simulate an internal server error by mocking the notesModel
        $this->mock(Note::class, function ($mock) {
            $mock->shouldReceive('create')
                ->andThrow(new \Exception('Simulated exception'));
        });

        $data = [
            'title' => 'Test Note',
            'content' => 'This is the content of the test note.'
        ];

        $response = $this->postJson('/api/notes', $data);

        $response->assertStatus(500);
        $response->assertJson(['error' => 'Internal Server Error']);
    }
}
