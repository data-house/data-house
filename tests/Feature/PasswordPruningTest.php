<?php

namespace Tests\Feature;

use App\Models\Password;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class PasswordPruningTest extends TestCase
{
    use RefreshDatabase;

    public function test_all_records_are_pruned_when_no_configuration_specified(): void
    {
        config(['auth.password_validation.historical_password_amount' => null]);

        Password::factory()->create();

        $this->artisan('model:prune', ['--model' => Password::class]);

        $this->assertEquals(0, Password::count());
    }

    public function test_all_records_are_pruned_when_zero_historical_passwords(): void
    {
        config(['auth.password_validation.historical_password_amount' => 0]);

        Password::factory()->create();

        $this->artisan('model:prune', ['--model' => Password::class]);

        $this->assertEquals(0, Password::count());
    }

    public function test_historical_records_amount_preserved_when_pruning(): void
    {
        config(['auth.password_validation.historical_password_amount' => 2]);

        $user = User::factory()->create();

        Password::factory()
            ->recycle($user)
            ->count(3)
            ->state(new Sequence(
                fn ($sequence) => ['created_at' => now()->subDays($sequence->index)]
            ))
            ->create();
        
        Password::factory()->count(2)->create();

        $this->artisan('model:prune', ['--model' => Password::class]);

        $this->assertEquals(4, Password::count());
        $this->assertEquals(2, Password::whereBelongsTo($user)->count());
        $this->assertEquals([now()->toDateString(), now()->subDays(1)->toDateString()], Password::whereBelongsTo($user)->latest()->get()->map(fn($p) => $p->created_at->toDateString())->toArray());
    }
}
