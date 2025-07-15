<?php

namespace Tests\Feature\Actions\Catalog;

use App\Actions\Catalog\CreateCatalogField;
use App\CatalogFieldType;
use App\Models\Catalog;
use App\Models\CatalogField;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;
use \Illuminate\Support\Str;

class CreateCatalogFieldTest extends TestCase
{
    use RefreshDatabase;
    
    public function test_field_added_to_catalog(): void
    {
        $catalog = Catalog::factory()->create();
        
        $user = $catalog->user;

        $create = app()->make(CreateCatalogField::class);

        $field = $create(
            catalog: $catalog,
            title: 'A field',
            fieldType: CatalogFieldType::TEXT,
            description: 'A description',
            user: $user,
        );

        $this->assertNotNull($field);
        $this->assertEquals("A field", $field->title);
        $this->assertEquals("A description", $field->description);
        $this->assertEquals(CatalogFieldType::TEXT, $field->data_type);
        $this->assertTrue($field->catalog()->is($catalog));
        $this->assertTrue($field->user()->is($user));
    }
    
    public function test_skos_collection_required_when_adding_skos_based_field(): void
    {
        $catalog = Catalog::factory()->create();
        
        $user = $catalog->user;

        $create = app()->make(CreateCatalogField::class);

        $this->assertThrows(fn() => $create(catalog: $catalog, title: 'A field', fieldType: CatalogFieldType::SKOS_CONCEPT, user: $user), fn(ValidationException $e) => $e->getMessage() == 'The skos collection field is required.');

    }

    public function test_user_permission_verified_during_catalog_field_creation(): void
    {       
        $catalog = Catalog::factory()->create();
        
        $user = User::factory()->manager()->create();

        $create = app()->make(CreateCatalogField::class);

        $this->assertThrows(fn() => $create(catalog: $catalog, title: 'A title', fieldType: CatalogFieldType::TEXT, user: $user), AuthorizationException::class);

        $this->assertNull(CatalogField::first());
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
    public function test_title_validated_during_catalog_field_creation(string $title, string $exceptionMessage): void
    {      
        $catalog = Catalog::factory()->create();
        
        $user = $catalog->user;

        $create = app()->make(CreateCatalogField::class);

        $this->assertThrows(fn() => $create(catalog: $catalog, title: $title, fieldType: CatalogFieldType::TEXT, user: $user), fn(ValidationException $e) => $e->getMessage() == $exceptionMessage);

        $this->assertNull(CatalogField::first());
    }
}
