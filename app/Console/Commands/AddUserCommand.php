<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use App\Actions\Fortify\CreateNewUser;
use App\Models\Role;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;
use Laravel\Jetstream\Jetstream;

class AddUserCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:add
                            {--email= : The user email address.}
                            {--password= : The user password.}
                            {--role= : The user\'s role. Default racemanager.}
                            {--name= : The user name to use. Default the email user.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Register a new user';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $email = $this->option('email');
        $name = $this->option('name') ?? $this->getUsernameFrom($email);
        $password = $this->option('password');
        $role = $this->option('role');
            
        if (empty($email) && $this->input->isInteractive()) {
            $email = $this->ask("Please enter the email address for the new user");
        }

        if (empty($role) && $this->input->isInteractive()) {
            $role = $this->choice("Select the user role", Arr::map(Role::cases(), fn($c) => $c->value), Role::GUEST->value );
        }
        else if(empty($role) && $this->input->isInteractive()){
            $role = Role::GUEST->value;
        }
            
        if (empty($password) && $this->input->isInteractive()) {
            $password = $this->secret(__('Please specify a password (your password must be at least :min_length characters long and include a mix of uppercase, lowercase, numbers, and special characters)', ['min_length' => config('auth.password_validation.minimum_length', 12)]));
        }

        $createUserAction = new CreateNewUser();

        try{
            $user = $createUserAction->create([
                'name' => $name,
                'email' => $email,
                'password' => $password,
                'password_confirmation' => $password,
                'terms' => Jetstream::hasTermsAndPrivacyPolicyFeature(),
                'role' => $role,
            ]);
    
            $this->line('');
            $this->line("User <comment>$email</comment> created.");       
            $this->line('');
    
            return self::SUCCESS;
        }
        catch(ValidationException $ex)
        {
            if($ex->validator->errors()->has('email') && 
               $ex->validator->errors()->first('email') === 'The email has already been taken.'){

                $this->line('');
                $this->error("User already existing");
                $this->line('');

                return self::INVALID;
            }
  

            $this->line('');
            $this->error("Validation errors");
            $this->line('');

            foreach ($ex->errors() as $key => $messages) {
                $this->comment($key);

                foreach ($messages as $message) {
                    $this->line("  - {$message}");
                }
                $this->line('');
            }

            return self::FAILURE;
        }

    }

    private function getUsernameFrom($email)
    {
        $et_offset = strpos($email, '@');
        return $et_offset !== false ? substr($email, 0, $et_offset) : $email;
    }
}
