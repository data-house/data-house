<?php

namespace Tests\Feature;

use App\Models\Preference;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class UserPreferenceControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_preference_saved(): void
    {
        $user = User::factory()->guest()->create();

        $response = $this->actingAs($user)
            ->from(route('documents.library'))
            ->put('/user-preferences', [
                'preference' => 'VISUALIZATION_LAYOUT',
                'value' => 'list',
            ]);

        $response->assertRedirect(route('documents.library'));

        $preferences = $user->fresh()->userPreferences;

        $this->assertCount(1, $preferences);

        $this->assertEquals(Preference::VISUALIZATION_LAYOUT, $preferences->first()->setting);
        $this->assertEquals('list', $preferences->first()->value);
    }
    
    public function test_preference_not_saved_when_value_invalid(): void
    {
        $user = User::factory()->guest()->create();

        $response = $this->actingAs($user)
            ->from(route('documents.library'))
            ->put('/user-preferences', [
                'preference' => 'VISUALIZATION_LAYOUT',
                'value' => 'detail',
            ]);

        $response->assertRedirect(route('documents.library'));

        $response->assertSessionHasErrors(['value' => 'Invalid preference value.']);

        $preferences = $user->fresh()->userPreferences;

        $this->assertCount(0, $preferences);
    }
    
    public function test_preference_not_saved_when_preference_invalid(): void
    {
        $user = User::factory()->guest()->create();

        $response = $this->actingAs($user)
            ->from(route('documents.library'))
            ->put('/user-preferences', [
                'preference' => 'VISUALIZATION',
                'value' => 'grid',
            ]);

        $response->assertRedirect(route('documents.library'));

        $response->assertSessionHasErrors(['preference' => 'Invalid preference.']);

        $preferences = $user->fresh()->userPreferences;

        $this->assertCount(0, $preferences);
    }
}
