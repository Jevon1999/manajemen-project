<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\RespondsWithJson;
use App\Http\Resources\ProjectMemberResource;
use App\Http\Resources\ProjectResource;
use App\Models\Project;
use App\Models\ProjectMember;
use App\Models\User;
use Illuminate\Http\Request;

class ProjectApiController extends Controller
{
    use RespondsWithJson;
    public function index(Request $request)
    {
        $query = Project::query()->with(['creator', 'leader'])
            ->withCount('members')
            ->notArchived();

        if ($search = $request->string('q')->toString()) {
            $query->where(function ($q) use ($search) {
                $q->where('project_name', 'like', "%$search%")
                  ->orWhere('description', 'like', "%$search%");
            });
        }

        if ($status = $request->string('status')->toString()) {
            $query->where('status', $status);
        }
        if ($priority = $request->string('priority')->toString()) {
            $query->where('priority', $priority);
        }

        $user = $request->user();
        if (!$user->isAdmin()) {
            $query->where(function($q) use ($user) {
                $q->where('created_by', $user->user_id)
                  ->orWhereIn('project_id', ProjectMember::where('user_id', $user->user_id)->pluck('project_id'));
            });
        }

        $projects = $query->latest('created_at')->paginate($request->integer('per_page', 10));
        $collection = ProjectResource::collection($projects);
        // preserve search param in meta
        $response = $collection->response()->getData(true);
        $response['meta']['q'] = $search;
        return response()->json([
            'success' => true,
            'message' => 'OK',
            'data' => $response['data'] ?? [],
            'links' => $response['links'] ?? null,
            'meta' => $response['meta'] ?? null,
        ]);
    }

    public function store(Request $request)
    {
        // Only admin and leader can create projects
        $this->authorizeManage($request->user());
        
        $data = $request->validate([
            'project_name' => ['required','string','max:100','unique:projects,project_name'],
            'description' => ['nullable','string'],
            'leader_id' => ['required','exists:users,user_id'],
            'deadline' => ['nullable','date','after:today'],
            'start_date' => ['nullable','date'],
            'budget' => ['nullable','numeric','min:0'],
            'status' => ['nullable','in:planning,active,completed,on_hold,cancelled'],
            'priority' => ['nullable','in:low,medium,high'],
            'category' => ['required','in:web_development,mobile_app,desktop_software,data_analysis,marketing,design,research,other'],
        ]);

        // Validate leader_id is actually a leader
        $leader = User::findOrFail($data['leader_id']);
        if (!$leader->isLeader() && !$leader->isAdmin()) {
            return $this->error('Selected user must have leader role', 422);
        }
        
        if ($leader->status !== 'active') {
            return $this->error('Leader must be in active status', 422);
        }

        $data['created_by'] = $request->user()->user_id;
        $data['status'] = $data['status'] ?? 'planning';
        $data['priority'] = $data['priority'] ?? 'medium';
        $data['category'] = $data['category'] ?? 'other';
        
        $project = Project::create($data);

        // Add leader as project member
        ProjectMember::create([
            'project_id' => $project->project_id,
            'user_id' => $data['leader_id'],
            'role' => 'project_manager',
            'joined_at' => now(),
        ]);

        // Create default boards
        $defaultBoards = ['Backlog', 'To Do', 'In Progress', 'Done'];
        foreach ($defaultBoards as $index => $boardName) {
            \App\Models\Board::create([
                'project_id' => $project->project_id,
                'board_name' => $boardName,
                'position' => $index + 1,
            ]);
        }

        // Log activity
        \App\Models\ActivityLog::logActivity(
            'created',
            'project',
            $project->project_id,
            "Created project '{$project->project_name}'"
        );

        return $this->successResource(new ProjectResource($project->load('creator', 'leader')), 'Project created successfully', 201);
    }

    public function show(Request $request, $id)
    {
        $project = Project::with(['creator', 'leader', 'members.user'])->withCount('members')->findOrFail($id);
        $this->authorizeView($request->user(), $project);
        return $this->successResource(new ProjectResource($project), 'OK');
    }

    public function update(Request $request, $id)
    {
        $project = Project::findOrFail($id);
        $this->authorizeManageProject($request->user(), $project);
        $data = $request->validate([
            'project_name' => ['sometimes','string','max:100'],
            'description' => ['nullable','string'],
            'deadline' => ['nullable','date'],
            'status' => ['nullable','in:planning,active,completed,on-hold'],
            'priority' => ['nullable','in:low,medium,high,critical'],
            'is_archived' => ['nullable','boolean'],
        ]);
        $project->update($data);
        return $this->successResource(new ProjectResource($project->refresh()->load(['creator', 'leader'])), 'Project updated');
    }

    public function destroy(Request $request, $id)
    {
        $project = Project::findOrFail($id);
        $this->authorizeManageProject($request->user(), $project);
        $project->delete();
        return $this->success(null, 'Project deleted');
    }

    public function members(Request $request, $projectId)
    {
        $project = Project::findOrFail($projectId);
        $this->authorizeView($request->user(), $project);
        $members = $project->members()->with('user')->paginate(20);
        return $this->successCollection(ProjectMemberResource::collection($members), 'OK');
    }

    public function addMember(Request $request, $projectId)
    {
        $project = Project::findOrFail($projectId);
        
        // Only project leader can add members
        $user = $request->user();
        if (!$user->isAdmin() && $project->leader_id !== $user->user_id) {
            return $this->error('Only project leader can add team members', 403);
        }

        $data = $request->validate([
            'user_id' => ['required','exists:users,user_id'],
            'role' => ['required','in:developer,designer,tester,member'],
        ]);

        // Validate user specialty matches role (for developer/designer)
        $newMember = User::findOrFail($data['user_id']);
        if (in_array($data['role'], ['developer', 'designer'])) {
            if ($newMember->specialty !== $data['role']) {
                return $this->error("User specialty ({$newMember->specialty}) does not match assigned role ({$data['role']})", 422);
            }
        }

        // Check if already a member
        $existingMember = ProjectMember::where('project_id', $project->project_id)
            ->where('user_id', $data['user_id'])
            ->first();

        if ($existingMember) {
            return $this->error('User is already a project member', 422);
        }

        $member = ProjectMember::create([
            'project_id' => $project->project_id,
            'user_id' => $data['user_id'],
            'role' => $data['role'],
            'joined_at' => now(),
        ]);

        // Log activity
        \App\Models\ActivityLog::create([
            'user_id' => $user->user_id,
            'project_id' => $project->project_id,
            'action' => 'added_member',
            'entity_type' => 'project_member',
            'entity_id' => $member->id,
            'description' => "Added {$newMember->full_name} as {$data['role']} to project",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return $this->successResource(
            new ProjectMemberResource($member->load('user')), 
            'Member added successfully', 
            201
        );
    }

    public function removeMember(Request $request, $projectId, $userId)
    {
        $project = Project::findOrFail($projectId);
        $this->authorizeManageProject($request->user(), $project);
        ProjectMember::where('project_id', $projectId)->where('user_id', $userId)->delete();
        return $this->success(null, 'Member removed');
    }

    private function authorizeManage(User $user)
    {
        abort_unless($user->canManageProjects(), 403, 'Unauthorized');
    }

    private function authorizeView(User $user, Project $project)
    {
        if ($user->isAdmin() || $project->created_by === $user->user_id) return;
        $isMember = ProjectMember::where('project_id', $project->project_id)
            ->where('user_id', $user->user_id)->exists();
        abort_unless($isMember, 403, 'Unauthorized');
    }

    private function authorizeManageProject(User $user, Project $project)
    {
        if ($user->isAdmin() || $project->created_by === $user->user_id) return;
        abort(403, 'Unauthorized');
    }
}
