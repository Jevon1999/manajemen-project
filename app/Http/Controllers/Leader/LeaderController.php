<?php

namespace App\Http\Controllers\Leader;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Project;
use App\Models\ProjectMember;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LeaderController extends Controller
{
    /**
     * Show the Team Leader Management page
     */
    public function management()
    {
        if (Auth::user()->role !== 'admin') {
            abort(403, 'Hanya admin yang dapat mengakses halaman ini.');
        }

        $leaders = User::where('role', 'leader')->get();
        $projects = Project::all();
        
        return view('admin.leaders.management', compact('leaders', 'projects'));
    }

    /**
     * Show leader detail
     */
    public function show($leaderId)
    {
        if (Auth::user()->role !== 'admin') {
            abort(403, 'Hanya admin yang dapat mengakses halaman ini.');
        }

        $leader = User::where('role', 'leader')->findOrFail($leaderId);
        $projectMembers = ProjectMember::where('user_id', $leaderId)
            ->with('project')
            ->get();
        
        return view('admin.leaders.show', compact('leader', 'projectMembers'));
    }

    /**
     * Show projects where user is team leader
     */
    public function projects()
    {
        $leaderId = Auth::id();
        
        $projects = Project::whereHas('members', function($query) use ($leaderId) {
            $query->where('user_id', $leaderId)
                  ->where('role', 'project_manager');
        })->with(['members.user'])->get();

        return view('leader.projects.index', compact('projects'));
    }

    /**
     * Show specific project details for leader
     */
    public function showProject($projectId)
    {
        $leaderId = Auth::id();
        
        // Verify access
        $hasAccess = ProjectMember::where('user_id', $leaderId)
            ->where('project_id', $projectId)
            ->where('role', 'project_manager')
            ->exists();

        if (!$hasAccess && Auth::user()->role !== 'admin') {
            abort(403, 'You do not have access to this project.');
        }

        $project = Project::with([
            'members.user',
            'boards.cards.assignments.user'
        ])->findOrFail($projectId);

        return view('leader.projects.show', compact('project'));
    }

    /**
     * Show team management for a specific project
     */
    public function manageTeam($projectId)
    {
        $leaderId = Auth::id();
        
        // Verify access
        $hasAccess = ProjectMember::where('user_id', $leaderId)
            ->where('project_id', $projectId)
            ->where('role', 'project_manager')
            ->exists();

        if (!$hasAccess && Auth::user()->role !== 'admin') {
            abort(403, 'You do not have access to this project.');
        }

        $project = Project::with(['members.user'])->findOrFail($projectId);
        $availableUsers = User::where('role', 'user')
            ->whereNotIn('user_id', $project->members->pluck('user_id'))
            ->get();

        return view('leader.projects.manage-team', compact('project', 'availableUsers'));
    }

    /**
     * Add team member to project
     */
    public function addTeamMember(Request $request, $projectId)
    {
        $leaderId = Auth::id();
        
        // Verify access
        $hasAccess = ProjectMember::where('user_id', $leaderId)
            ->where('project_id', $projectId)
            ->where('role', 'project_manager')
            ->exists();

        if (!$hasAccess && Auth::user()->role !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'user_id' => 'required|exists:users,user_id',
            'role' => 'required|in:developer,designer'
        ]);

        // Check if user is already a member
        $existingMember = ProjectMember::where('project_id', $projectId)
            ->where('user_id', $request->user_id)
            ->exists();

        if ($existingMember) {
            return response()->json(['success' => false, 'message' => 'User is already a project member'], 422);
        }

        ProjectMember::create([
            'project_id' => $projectId,
            'user_id' => $request->user_id,
            'role' => $request->role,
            'joined_at' => now()
        ]);

        return response()->json(['success' => true, 'message' => 'Team member added successfully!']);
    }

    /**
     * Remove team member from project
     */
    public function removeTeamMember($projectId, $userId)
    {
        $leaderId = Auth::id();
        
        // Verify access
        $hasAccess = ProjectMember::where('user_id', $leaderId)
            ->where('project_id', $projectId)
            ->where('role', 'project_manager')
            ->exists();

        if (!$hasAccess && Auth::user()->role !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        // Don't allow removing project manager
        $member = ProjectMember::where('project_id', $projectId)
            ->where('user_id', $userId)
            ->first();

        if (!$member) {
            return response()->json(['success' => false, 'message' => 'Member not found'], 404);
        }

        if ($member->role === 'project_manager') {
            return response()->json(['success' => false, 'message' => 'Cannot remove project manager'], 422);
        }

        $member->delete();

        return response()->json(['success' => true, 'message' => 'Team member removed successfully!']);
    }

    /**
     * Update team member role
     */
    public function updateMemberRole(Request $request, $projectId, $userId)
    {
        $leaderId = Auth::id();
        
        // Verify access
        $hasAccess = ProjectMember::where('user_id', $leaderId)
            ->where('project_id', $projectId)
            ->where('role', 'project_manager')
            ->exists();

        if (!$hasAccess && Auth::user()->role !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'role' => 'required|in:developer,designer'
        ]);

        $member = ProjectMember::where('project_id', $projectId)
            ->where('user_id', $userId)
            ->first();

        if (!$member) {
            return response()->json(['success' => false, 'message' => 'Member not found'], 404);
        }

        if ($member->role === 'project_manager') {
            return response()->json(['success' => false, 'message' => 'Cannot change project manager role'], 422);
        }

        $member->update(['role' => $request->role]);

        return response()->json(['success' => true, 'message' => 'Member role updated successfully!']);
    }

    /**
     * Mark project as completed
     */
    public function completeProject(Request $request, $projectId)
    {
        $leaderId = Auth::id();
        
        // Find project and verify it's assigned to this leader
        $project = Project::findOrFail($projectId);
        
        // Check if user is project manager or admin
        $hasAccess = ProjectMember::where('user_id', $leaderId)
            ->where('project_id', $projectId)
            ->where('role', 'project_manager')
            ->exists();

        if (!$hasAccess && Auth::user()->role !== 'admin') {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses untuk menyelesaikan project ini.');
        }

        // Check if already completed
        if ($project->status === 'completed') {
            return redirect()->back()->with('error', 'Project sudah diselesaikan sebelumnya.');
        }

        // Validate request
        $validated = $request->validate([
            'completion_notes' => 'nullable|string|max:1000',
            'delay_reason' => 'nullable|required_if:is_overdue,true|string|max:500',
            'is_overdue' => 'nullable|boolean'
        ]);

        // Mark as completed using model method
        $project->markAsCompleted(
            $validated['completion_notes'] ?? null,
            $validated['delay_reason'] ?? null
        );

        // Prepare success message
        $message = $project->is_overdue 
            ? "✅ Project berhasil diselesaikan! (Terlambat {$project->delay_days} hari)"
            : '✅ Project berhasil diselesaikan tepat waktu!';

        return redirect()
            ->route('leader.projects.show', $projectId)
            ->with('success', $message);
    }
}