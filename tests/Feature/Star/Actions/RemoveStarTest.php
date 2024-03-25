<?php

namespace Tests\Feature\Star\Actions;

use App\Actions\Star\RemoveStar;
use App\Events\StarRemoved;
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

class RemoveStarTest extends TestCase
{
    use RefreshDatabase;
    
    public function test_star_removed(): void
    {
        Event::fake();

        $user = User::factory()->manager()->withPersonalTeam()->create()->withAccessToken(new TransientToken);

        $star = Star::factory()
            ->recycle($user)
            ->for(
                Document::factory()->recycle($user), 'starrable'
            )
            ->create();

        $starrable = $star->starrable;

        (new RemoveStar)($user, $star);

        $this->assertNull($star->fresh());

        Event::assertDispatched(StarRemoved::class, function($evt) use ($starrable, $user) {
            return 
                $evt->starrable->is($starrable) &&
                $evt->user->is($user);
        });
    }
    
    public function test_star_from_other_users_not_removed(): void
    {
        Event::fake();

        $user = User::factory()->guest()->create()->withAccessToken(new TransientToken);

        $star = Star::factory()
            ->for(
                Document::factory(), 'starrable'
            )
            ->create();

        $this->expectException(AuthorizationException::class);

        (new RemoveStar)($user, $star);

        Event::assertNotDispatched(StarRemoved::class);
    }
}
