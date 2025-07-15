<?php

namespace Tests\Feature\Actions\Catalog;

use App\Actions\Catalog\UpdateCatalog;
use App\Models\Catalog;
use App\Models\User;
use App\Models\Visibility;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Exceptions;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;
use Illuminate\Support\Str;

class UpdateCatalogTest extends TestCase
{
    use RefreshDatabase;

    public function test_catalog_updated_by_user(): void
    {
        $existing_catalog = Catalog::factory()->create();
        
        $user = $existing_catalog->user;

        $update = app()->make(UpdateCatalog::class);

        $catalog = $update(
            catalog: $existing_catalog,
            title: "A title",
            description: "A description",
            user: $user,
        );

        $this->assertNotNull($catalog);
        $this->assertEquals("A title", $catalog->title);
        $this->assertEquals("A description", $catalog->description);
        $this->assertTrue($catalog->user()->is($user));
        $this->assertTrue($catalog->team()->is($user->currentTeam));
        $this->assertEquals(Visibility::PERSONAL, $catalog->visibility);
    }

    public function test_user_permission_verified_during_catalog_update(): void
    {
        $existing_catalog = Catalog::factory()->create();
        
        $user = User::factory()->guest()->create();

        $update = app()->make(UpdateCatalog::class);

        $this->assertThrows(fn() => $update(catalog: $existing_catalog, title: 'a title',user: $user), AuthorizationException::class);

        $catalog = Catalog::first();

        $this->assertNotNull($catalog);

        $this->assertEquals($existing_catalog->title, $catalog->title);
    }

    public static function invalid_title_provider(): array
    {
        return [
            'empty string' => ['', 'The title field is required.'],
            'whitespace only' => ['   ', 'The title field is required.'],
            'above limit' => [Str::random(300), 'The title field must not be greater than 255 characters.'],
        ];
    }

    #[DataProvider('invalid_title_provider')]
    public function test_title_validated_during_catalog_update(string $title, string $exceptionMessage): void
    {      
        $existing_catalog = Catalog::factory()->create();
        
        $user = $existing_catalog->user;

        $update = app()->make(UpdateCatalog::class);

        $this->assertThrows(fn() => $update(catalog: $existing_catalog, title: $title,user: $user), fn(ValidationException $e) => $e->getMessage() == $exceptionMessage);

        $catalog = Catalog::first();

        $this->assertNotNull($catalog);

        $this->assertEquals($existing_catalog->title, $catalog->title);
    }
}
