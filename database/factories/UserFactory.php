<?php

namespace Database\Factories;

use App\Models\Preference;
use App\Models\Role;
use App\Models\Team;
use App\Models\User;
use App\Models\UserPreference;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Laravel\Jetstream\Features;

class UserFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = User::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'remember_token' => Str::random(10),
            'profile_photo_path' => null,
            'current_team_id' => null,
        ];
    }

    /**
     * Indicate that the user's role is admin.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function admin()
    {
        return $this->state(function (array $attributes) {
            return [
                'role' => Role::ADMIN,
            ];
        });
    }

    /**
     * Indicate that the user's role is manager.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function manager()
    {
        return $this->state(function (array $attributes) {
            return [
                'role' => Role::MANAGER,
            ];
        });
    }

    /**
     * Indicate that the user's role is guest.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function guest()
    {
        return $this->state(function (array $attributes) {
            return [
                'role' => Role::GUEST,
            ];
        });
    }

    /**
     * Indicate that the model's email address should be unverified.
     *
     * @return $this
     */
    public function unverified(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'email_verified_at' => null,
            ];
        });
    }

    /**
     * Indicate that the user should have a personal team.
     *
     * @return $this
     */
    public function withPersonalTeam(array $teamState = []): static
    {
        if (! Features::hasTeamFeatures()) {
            return $this->state([]);
        }

        return $this->has(
            Team::factory()
                ->state(fn (array $attributes, User $user) => [
                    'name' => $user->name.'\'s Team',
                    'user_id' => $user->id,
                    'personal_team' => true,
                    ...$teamState,
                ]),
            'ownedTeams'
        );
    }

    /**
     * Indicate that the user should have a personal team.
     *
     * @return $this
     */
    public function withCurrentTeam(): static
    {
        if (! Features::hasTeamFeatures()) {
            return $this->state([]);
        }

        return $this->has(
            Team::factory()
                ->state(fn (array $attributes, User $user)  => [
                    'name' => $user->name.'\'s Team',
                    'user_id' => $user->id,
                    'personal_team' => true,
                ]),
            'currentTeam'
        );
    }

    public function withPreference(Preference $preference, $value)
    {
        return $this->has(
            UserPreference::factory()
                ->state([
                    'setting' => $preference,
                    'value' => $value,
                ]),
        );
    }
}
