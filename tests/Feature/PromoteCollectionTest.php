<?php

namespace Tests\Feature;

use App\Actions\Collection\PromoteCollection;
use App\Models\Collection;
use App\Models\User;
use App\Models\Visibility;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;
use Laravel\Sanctum\TransientToken;
use Tests\TestCase;

class PromoteCollectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_collection_promotion_not_authorized_if_user_cannot_update_collection(): void
    {
        $user = User::factory()->guest()->create();

        $collection = Collection::factory()
            ->create(['visibility' => Visibility::PERSONAL]);

        $this->expectException(AuthorizationException::class);

        (new PromoteCollection)($user, $collection, Visibility::TEAM);
        
    }

    public function test_collection_visibility_cannot_be_downgraded(): void
    {
        $user = User::factory()->manager()->create()->withAccessToken(new TransientToken);

        $collection = Collection::factory()
            ->for($user)
            ->create(['visibility' => Visibility::PROTECTED]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Downgrade collection visibility not allowed.');

        (new PromoteCollection)($user, $collection, Visibility::TEAM);
    }
    
    public function test_system_collection_cannot_be_promoted(): void
    {
        $user = User::factory()->manager()->withPersonalTeam()->create()->withAccessToken(new TransientToken);

        $collection = Collection::factory()
            ->for($user)
            ->create(['visibility' => Visibility::SYSTEM]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Collection cannot be promoted.');

        (new PromoteCollection)($user, $collection, Visibility::TEAM);
    }
    
    public function test_team_collection_cannot_be_promoted_if_not_owned_by_a_team(): void
    {
        $user = User::factory()->manager()->withPersonalTeam()->create()->withAccessToken(new TransientToken);

        $collection = Collection::factory()
            ->for($user)
            ->create(['visibility' => Visibility::TEAM]);

        $this->expectException(AuthorizationException::class);
        $this->expectExceptionMessage('Cannot identify owning team.');

        (new PromoteCollection)($user, $collection, Visibility::PROTECTED);
    }

    public function test_collection_promoted_to_team(): void
    {
        $user = User::factory()->manager()->withPersonalTeam()->create()->withAccessToken(new TransientToken);

        $collection = Collection::factory()->for($user)->create([
            'visibility' => Visibility::PERSONAL,
        ]);

        $promotedCollection = (new PromoteCollection)($user, $collection, Visibility::TEAM);

        $this->assertTrue($collection->is($promotedCollection));

        $this->assertEquals(Visibility::TEAM, $promotedCollection->visibility);

        $this->assertTrue($user->currentTeam->is($promotedCollection->team), 'Different collection team and user teams');
    }


    public function test_team_collection_promoted_to_library(): void
    {
        $user = User::factory()->manager()->withPersonalTeam()->create()->withAccessToken(new TransientToken);

        $collection = Collection::factory()
            ->for($user)
            ->recycle($user->currentTeam)
            ->team()
            ->create();

        $promotedCollection = (new PromoteCollection)($user, $collection, Visibility::PROTECTED);

        $this->assertTrue($collection->is($promotedCollection));

        $this->assertEquals(Visibility::PROTECTED, $promotedCollection->visibility);

        $this->assertTrue($user->currentTeam->is($promotedCollection->team), 'Different collection team and user teams');

    }
    
    public function test_promotion_from_personal_to_public_denied(): void
    {
        $user = User::factory()->manager()->withPersonalTeam()->create()->withAccessToken(new TransientToken);

        $collection = Collection::factory()
            ->for($user)
            ->create();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Collection cannot be promoted.');

        (new PromoteCollection)($user, $collection, Visibility::PUBLIC);
    }
    
    public function test_promotion_from_team_to_public_denied(): void
    {
        $user = User::factory()->manager()->withPersonalTeam()->create()->withAccessToken(new TransientToken);

        $collection = Collection::factory()
            ->for($user)
            ->recycle($user->currentTeam)
            ->team()
            ->create();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Collection cannot be promoted.');

        (new PromoteCollection)($user, $collection, Visibility::PUBLIC);
    }


    public function test_personal_collection_not_promoted_if_team_collection_with_same_name_exists(): void
    {
        $user = User::factory()->manager()->withPersonalTeam()->create()->withAccessToken(new TransientToken);

        $alreadyExisting = Collection::factory()->for($user)->recycle($user->currentTeam)->team()->create();

        $collection = Collection::factory()->for($user)->create([
            'visibility' => Visibility::PERSONAL,
            'title' => $alreadyExisting->title,
        ]);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('A collection with the same name already is already present at Team level.');

        $promotedCollection = (new PromoteCollection)($user, $collection, Visibility::TEAM);
    }

    public function test_team_collection_not_promoted_if_library_collection_with_same_name_exists(): void
    {
        $user = User::factory()->manager()->withPersonalTeam()->create()->withAccessToken(new TransientToken);

        $alreadyExisting = Collection::factory()->for($user)->recycle($user->currentTeam)->library()->create();

        $collection = Collection::factory()->for($user)->recycle($user->currentTeam)->team()->create([
            'title' => $alreadyExisting->title,
        ]);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('A collection with the same name already is already present at Protected level.');

        $promotedCollection = (new PromoteCollection)($user, $collection, Visibility::PROTECTED);
    }
}
