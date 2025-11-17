<?php

namespace App\Http\Controllers;

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

        // Get all leaders with their project assignments
        $leaders = User::where('role', 'leader')
            ->where('status', 'active')
            ->with(['projectMemberships.project'])
            ->get();

        // Get users who can be promoted to leaders (regular users)
        $promotableUsers = User::where('role', 'user')
            ->where('status', 'active')
            ->get();

        // Get all projects for assignment
        $projects = Project::where('status', 'active')->get();

        return view('admin.leaders.management', compact('leaders', 'promotableUsers', 'projects'));
    }

    /**
     * Promote a user to leader role
     */
    public function promoteToLeader(Request $request)
    {
        if (Auth::user()->role !== 'admin') {
            abort(403, 'Hanya admin yang dapat promote user menjadi leader.');
        }

        $request->validate([
            'user_id' => 'required|exists:users,user_id',
        ]);

        $user = User::findOrFail($request->user_id);
        
        if ($user->role !== 'user') {
            return response()->json(['error' => 'User sudah memiliki role yang lebih tinggi.'], 422);
        }

        $user->update(['role' => 'leader']);

        return response()->json([
            'message' => 'User berhasil dipromote menjadi leader.',
            'user' => $user
        ]);
    }

    /**
     * Remove leader role (demote to user)
     */
    public function removeLeaderRole(Request $request)
    {
        if (Auth::user()->role !== 'admin') {
            abort(403, 'Hanya admin yang dapat remove leader role.');
        }

        $request->validate([
            'user_id' => 'required|exists:users,user_id',
        ]);

        $user = User::findOrFail($request->user_id);
        
        if ($user->role !== 'leader') {
            return response()->json(['error' => 'User bukan leader.'], 422);
        }

        // Check if leader has active project assignments
        $activeAssignments = ProjectMember::where('user_id', $user->user_id)
            ->whereHas('project', function($query) {
                $query->where('status', 'active');
            })
            ->count();

        if ($activeAssignments > 0) {
            return response()->json([
                'error' => 'Leader masih memiliki project assignments yang aktif. Hapus assignments terlebih dahulu.'
            ], 422);
        }

        $user->update(['role' => 'user']);

        return response()->json([
            'message' => 'Leader role berhasil dihapus.',
            'user' => $user
        ]);
    }
    /**
     * Search for leaders (only for admin)
     */
    public function searchLeaders(Request $request)
    {
        if (Auth::user()->role !== 'admin') {
            abort(403, 'Hanya admin yang dapat mencari leader.');
        }

        $query = $request->get('q');
        
        $leaders = User::where('role', 'leader')
            ->where(function($q) use ($query) {
                $q->where('full_name', 'LIKE', "%{$query}%")
                  ->orWhere('username', 'LIKE', "%{$query}%")
                  ->orWhere('email', 'LIKE', "%{$query}%");
            })
            ->where('status', 'active')
            ->limit(10)
            ->get(['user_id', 'full_name', 'username', 'email']);
        
        // Add active project info to each leader
        $leadersWithProjectInfo = $leaders->map(function($leader) {
            $activeProject = $leader->getActiveProject();
            
            return [
                'user_id' => $leader->user_id,
                'full_name' => $leader->full_name,
                'username' => $leader->username,
                'email' => $leader->email,
                'has_active_project' => $activeProject !== null,
                'active_project_name' => $activeProject ? $activeProject->project_name : null,
            ];
        });

        return response()->json($leadersWithProjectInfo);
    }

    /**
     * Assign leader to project as project manager
     */
    public function assignToProject(Request $request)
    {
        if (Auth::user()->role !== 'admin') {
            abort(403, 'Hanya admin yang dapat assign leader ke project.');
        }

        $request->validate([
            'project_id' => 'required|exists:projects,project_id',
            'leader_id' => 'required|exists:users,user_id',
        ]);

        $leader = User::findOrFail($request->leader_id);
        
        if ($leader->role !== 'leader') {
            return response()->json(['error' => 'User yang dipilih bukan leader.'], 422);
        }

        // Check if leader already assigned to this project
        $existingMember = ProjectMember::where('project_id', $request->project_id)
            ->where('user_id', $request->leader_id)
            ->first();

        if ($existingMember) {
            return response()->json(['error' => 'Leader sudah assigned ke project ini.'], 422);
        }

        // Assign leader as project manager
        $projectMember = ProjectMember::create([
            'project_id' => $request->project_id,
            'user_id' => $request->leader_id,
            'role' => 'project_manager',
            'joined_at' => now(),
        ]);

        return response()->json([
            'message' => 'Leader berhasil di-assign sebagai project manager.',
            'member' => $projectMember->load('user')
        ]);
    }

    /**
     * Get leaders available for assignment
     */
    public function getAvailableLeaders(Request $request)
    {
        if (Auth::user()->role !== 'admin') {
            abort(403, 'Hanya admin yang dapat melihat daftar leader.');
        }

        $projectId = $request->get('project_id');
        
        $leaders = User::where('role', 'leader')
            ->where('status', 'active')
            ->whereNotIn('user_id', function($query) use ($projectId) {
                $query->select('user_id')
                    ->from('project_members')
                    ->where('project_id', $projectId);
            })
            ->get(['user_id', 'full_name', 'username', 'email']);

        return response()->json($leaders);
    }
}
