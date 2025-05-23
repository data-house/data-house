<?php

namespace Tests\Feature\Actions\Catalog;

use App\Actions\Catalog\CreateCatalog;
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

class CreateCatalogTest extends TestCase
{
    use RefreshDatabase;

    public function test_catalog_created_by_user(): void
    {
        $user = User::factory()->manager()->withPersonalTeam()->create();

        $create = app()->make(CreateCatalog::class);

        $catalog = $create(
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

    public function test_user_permission_verified_during_catalog_creation(): void
    {       
        $user = User::factory()->guest()->create();

        $create = app()->make(CreateCatalog::class);

        $this->assertThrows(fn() => $create(title: 'a title',user: $user), AuthorizationException::class);

        $this->assertNull(Catalog::first());
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
    public function test_title_validated_during_catalog_creation(string $title, string $exceptionMessage): void
    {      
        $user = User::factory()->withPersonalTeam()->create();

        $create = app()->make(CreateCatalog::class);

        $this->assertThrows(fn() => $create(title: $title,user: $user), fn(ValidationException $e) => $e->getMessage() == $exceptionMessage);

        $this->assertNull(Catalog::first());
    }
}
