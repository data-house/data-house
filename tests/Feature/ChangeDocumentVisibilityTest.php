<?php

namespace Tests\Feature;

use App\Actions\ChangeDocumentVisibility;
use App\Models\Document;
use App\Models\User;
use App\Models\Visibility;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use InvalidArgumentException;
use Tests\TestCase;

class ChangeDocumentVisibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_required()
    {
        $document = Document::factory()
            ->create();

        $changeVisibility = new ChangeDocumentVisibility();

        $this->expectException(InvalidArgumentException::class);

        $changeVisibility($document, Visibility::PROTECTED);

        $this->assertEquals(Visibility::TEAM, $document->fresh()->visibility);
    }

    public function test_user_not_authorized_to_update_document()
    {
        $user = User::factory()
            ->withCurrentTeam()
            ->guest()
            ->create();

        $document = Document::factory()
            ->recycle($user->currentTeam)
            ->create();

        $changeVisibility = new ChangeDocumentVisibility();

        $this->expectException(AuthorizationException::class);

        $changeVisibility($document, Visibility::PROTECTED, $user);

        $this->assertEquals(Visibility::TEAM, $document->fresh()->visibility);
    }

    public function test_document_not_visible_by_user()
    {
        $user = User::factory()
            ->withPersonalTeam()
            ->manager()
            ->create();

        $document = Document::factory()
            ->create();

        $changeVisibility = new ChangeDocumentVisibility();
        
        $this->expectException(AuthorizationException::class);

        $done = $changeVisibility($document, Visibility::PROTECTED, $user);

        $this->assertEquals(Visibility::TEAM, $document->fresh()->visibility);
    }

    
    public function test_visibility_can_be_changed(): void
    {
        $user = User::factory()
            ->withPersonalTeam()
            ->manager()
            ->create();

        $document = Document::factory()
            ->recycle($user)
            ->recycle($user->currentTeam)
            ->create([
                'visibility' => Visibility::TEAM
            ]);

        $changeVisibility = new ChangeDocumentVisibility();

        $done = $changeVisibility($document, Visibility::PROTECTED, $user);

        $this->assertTrue($done);

        $this->assertEquals(Visibility::PROTECTED, $document->fresh()->visibility);
    }
}
