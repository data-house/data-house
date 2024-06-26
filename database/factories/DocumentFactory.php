<?php

namespace Database\Factories;

use App\Models\Disk;
use App\Models\MimeType;
use App\Models\Team;
use App\Models\User;
use App\Models\Visibility;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Document>
 */
class DocumentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'disk_name' => Disk::DOCUMENTS->value,
            'disk_path' => fake()->md5() . '.pdf',
            'draft' => true,
            'visibility' => Visibility::TEAM,
            'title' => fake()->text(40),
            'mime' => MimeType::APPLICATION_PDF->value,
            'uploaded_by' => User::factory(),
            'team_id' => Team::factory(),
            'languages' => [],
            'description' => null,
            'thumbnail_disk_name' => null,
            'thumbnail_disk_path' => null,
            'published_at' => null,
            'published_by' => null,
            'published_to_url' => null,
            'properties' => ['has_textual_content' => true],
        ];
    }

    public function visibleByTeamMembers()
    {
        return $this->state(function (array $attributes) {
            return [
                'visibility' => Visibility::TEAM,
            ];
        });
    }

    public function visibleByAnyUser()
    {
        return $this->state(function (array $attributes) {
            return [
                'visibility' => Visibility::PROTECTED,
            ];
        });
    }
    
    /**
     * Generates a document that is only visible by the uploader
     *
     * @param \App\Models\User|null The user the document is visible to. Default to null hence no user is set as uploader
     */
    public function visibleByUploader(?User $user = null)
    {
        return $this
            ->when($user, fn($f) => $this->for($user, 'uploader'))
            ->state(function (array $attributes) {
            return [
                'visibility' => Visibility::PERSONAL,
            ];
        });
    }
    
    public function visiblePublicly()
    {
        return $this->state(function (array $attributes) {
            return [
                'visibility' => Visibility::PUBLIC,
            ];
        });
    }
}
