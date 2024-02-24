<?php

namespace App\Console\Commands;

use App\Models\Flag;
use App\Models\User;
use Illuminate\Console\Command;
use Laravel\Pennant\Feature;

class DeactivateFeatureCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'feature:deactivate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Deactivate or forget a feature flag';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $flag = $this->choice(
            'The feature to deactivate?',
            collect(Flag::cases())->map->value->toArray()
        );
        
        $allUsers = $this->choice(
            'Targets?',
            ['All users', 'Single user']
        ) === 'All users';


        if($allUsers){
            Feature::deactivateForEveryone($flag);

            $this->line("Feature [{$flag}] deactivated for all users.");

            return self::SUCCESS;
        }


        $userEmail = $this->anticipate('Specify the user email?', function (string $input) {
            return User::where('name', 'like', "%{$input}%")->orWhere('email', 'like', "%{$input}%")->limit(5)->get()->map->email->toArray();
        });

        $user = User::where('email', $userEmail)->firstOrFail();

        Feature::for($user)->deactivate($flag);

        $this->line("Feature [{$flag}] deactivated for user [{$user->getKey()} - {$user->email}].");
    }
}
