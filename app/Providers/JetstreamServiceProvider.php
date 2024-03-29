<?php

namespace App\Providers;

use App\Actions\Jetstream\AddTeamMember;
use App\Actions\Jetstream\CreateTeam;
use App\Actions\Jetstream\DeleteTeam;
use App\Actions\Jetstream\DeleteUser;
use App\Actions\Jetstream\InviteTeamMember;
use App\Actions\Jetstream\RemoveTeamMember;
use App\Actions\Jetstream\UpdateTeamName;
use App\Models\Role;
use Illuminate\Support\ServiceProvider;
use Laravel\Jetstream\Jetstream;

class JetstreamServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configurePermissions();

        Jetstream::createTeamsUsing(CreateTeam::class);
        Jetstream::updateTeamNamesUsing(UpdateTeamName::class);
        Jetstream::addTeamMembersUsing(AddTeamMember::class);
        Jetstream::inviteTeamMembersUsing(InviteTeamMember::class);
        Jetstream::removeTeamMembersUsing(RemoveTeamMember::class);
        Jetstream::deleteTeamsUsing(DeleteTeam::class);
        Jetstream::deleteUsersUsing(DeleteUser::class);
    }

    /**
     * Configure the roles and permissions that are available within the application.
     */
    protected function configurePermissions(): void
    {
        Jetstream::defaultApiTokenPermissions(['documents:view', 'collection:view', 'project:view']);

        Jetstream::role(Role::ADMIN->value, 'Administrator', [
            '*',
        ])->description('Can do everything the Focal Point does plus create new teams and users');

        Jetstream::role(Role::MANAGER->value, 'Focal Point', [
            'project:view',
            'project:create',
            'project:update',
            'project:delete',
            'document:view',
            'document:create',
            'document:update',
            'document:delete',
            'import:view',
            'import:create',
            'import:update',
            'import:delete',
            'question:view',
            'question:create',
            'question-feedback:view',
            'question-feedback:create',
            'question-feedback:update',
            'collection:view',
            'collection:create',
            'collection:update',
            'star:view',
            'star:create',
            'star:update',
            'star:delete',
        ])->description('Can do everything the Contributor does plus upload documents, create collections and invite team members');

        Jetstream::role(Role::CONTRIBUTOR->value, 'Contributor', [
            'project:view',
            'project:create',
            'project:update',
            'project:delete',
            'document:view',
            'document:update',
            'question:view',
            'question:create',
            'collection:view',
            'question-feedback:view',
            'star:view',
            'star:create',
            'star:update',
            'star:delete',
        ])->description('Can do everything the Guest does plus edit document titles and summaries.');
        
        Jetstream::role(Role::GUEST->value, 'Guest', [
            'document:view',
            'collection:view',
            'project:view',
            'question:view',
            'star:view',
            'star:create',
            'star:update',
            'star:delete',
        ])->description('Can only view files.');

    }
}
