<?php

namespace App\Console\Commands;

use App\Models\Flag;
use App\Models\User;
use Illuminate\Console\Command;
use Laravel\Pennant\Feature;

class ActivateFeatureCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'feature:activate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Activate a feature that is controlled by a feature flag';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $flag = $this->choice(
            'The feature to activate?',
            collect(Flag::cases())->map->value->toArray()
        );
        
        $allUsers = $this->choice(
            'Targets?',
            ['All users', 'Single user']
        ) === 'All users';


        if($allUsers){
            Feature::activateForEveryone($flag);

            $this->line("Feature [{$flag}] activated for all users.");

            return self::SUCCESS;
        }


        $userEmail = $this->anticipate('Specify the user email?', function (string $input) {
            return User::where('name', 'like', "%{$input}%")->orWhere('email', 'like', "%{$input}%")->limit(5)->get()->map->email->toArray();
        });

        $user = User::where('email', $userEmail)->firstOrFail();

        Feature::for($user)->activate($flag);

        $this->line("Feature [{$flag}] activated for user [{$user->getKey()} - {$user->email}].");
    }
}
