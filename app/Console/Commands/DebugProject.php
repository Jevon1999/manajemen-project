<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Project;

class DebugProject extends Command
{
    protected $signature = 'debug:project {id}';
    protected $description = 'Debug project information';

    public function handle()
    {
        $projectId = $this->argument('id');
        $project = Project::find($projectId);

        if (!$project) {
            $this->error("Project with ID {$projectId} not found");
            return;
        }

        $this->info("Project ID: " . $project->project_id);
        $this->info("Project Name: " . $project->name);
        $this->info("Leader ID: " . $project->leader_id);
        
        $leader = $project->leader;
        if ($leader) {
            $this->info("Leader Name: " . $leader->name);
            $this->info("Leader Role: " . $leader->role);
        }
        
        $this->info("\nProject Members:");
        $members = $project->members()->with('user')->get();
        foreach ($members as $member) {
            $this->info("- User ID: " . $member->user_id . ", Name: " . $member->user->name . ", Role: " . $member->role);
        }
    }
}