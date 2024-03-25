<?php

namespace Tests\Feature\Star\Actions;

use App\Actions\Star\AddStar;
use App\Events\StarCreated;
use App\Models\Document;
use App\Models\Star;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Event;
use InvalidArgumentException;
use Laravel\Sanctum\TransientToken;
use Tests\TestCase;

class AddStarTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_not_starrable(): void
    {
        Event::fake();

        $user = User::factory()->manager()->create()->withAccessToken(new TransientToken);

        $this->expectException(InvalidArgumentException::class);

        (new AddStar)($user, $user);

        Event::assertNotDispatched(StarCreated::class);
    }
    
    public function test_document_starred_when_team_visible(): void
    {
        Event::fake();

        $user = User::factory()->manager()->withPersonalTeam()->create()->withAccessToken(new TransientToken);

        $document = Document::factory()
            ->recycle($user->currentTeam)
            ->visibleByTeamMembers()
            ->create();

        $starred = (new AddStar)($user, $document);

        $this->assertNotNull($starred);

        $this->assertInstanceOf(Star::class, $starred);

        $this->assertTrue($starred->starrable->is($document));
        
        $this->assertTrue($starred->user->is($user));

        Event::assertDispatched(StarCreated::class, function($evt) use ($document, $starred, $user) {
            return 
                $evt->star->is($starred) &&
                $evt->starrable->is($document) &&
                $evt->user->is($user);
        });
    }
    
    public function test_document_starred_when_protected(): void
    {
        Event::fake();
        
        $user = User::factory()->guest()->create()->withAccessToken(new TransientToken);

        $document = Document::factory()
            ->visibleByAnyUser()
            ->create();

        $starred = (new AddStar)($user, $document);

        $this->assertNotNull($starred);

        $this->assertInstanceOf(Star::class, $starred);

        $this->assertTrue($starred->starrable->is($document));
        
        $this->assertTrue($starred->user->is($user));

        Event::assertDispatched(StarCreated::class, function($evt) use ($document, $starred, $user) {
            return 
                $evt->star->is($starred) &&
                $evt->starrable->is($document) &&
                $evt->user->is($user);
        });
    }
    
    public function test_document_not_starred_when_personal(): void
    {
        Event::fake();

        $user = User::factory()->guest()->create()->withAccessToken(new TransientToken);

        $document = Document::factory()
            ->visibleByUploader()
            ->create();

        $this->expectException(AuthorizationException::class);

        (new AddStar)($user, $document);

        Event::assertNotDispatched(StarCreated::class);
    }
}
