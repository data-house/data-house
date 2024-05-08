<?php

namespace App\Console\Commands;

use App\Models\Role;
use App\Models\Team;
use App\Models\Project;
use Illuminate\Console\Command;
use App\Actions\Project\AddProjectMember;

class LinkTeamToProjectCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'project:link-team {--role= : The role of the team. Default guest.} {--team= : The team} {projects* : The ULID or slug of the projects to link}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Link a team to projects';

    /**
     * Execute the console command.
     */
    public function handle(AddProjectMember $projectMembers)
    {
        $inputProjects = (array) $this->argument('projects');
        
        $team = $this->selectTeam($this->option('team'));

        $role = $this->option('role') ?? Role::GUEST->value;

        $projects = Project::whereIn('ulid', $inputProjects)
            ->orWhereIn('slug', $inputProjects)
            ->get();

        $projects->each(fn($project) => $projectMembers->add($project, $team, $role));        
    }


    protected function selectTeam($team = null): Team
    {
        if($team){
            return Team::findOrFail($team);
        }


        $teamName = $this->anticipate('Specify the team?', function (string $input) {
            return Team::where('name', 'like', "%{$input}%")->limit(5)->get()->map->name->toArray();
        });

        return Team::where('name', $teamName)->firstOrFail();
    }
}
