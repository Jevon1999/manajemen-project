<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProjectMemberRequest;
use App\Models\Project;
use App\Models\ProjectMember;
use App\Models\User;
use App\Services\ProjectMemberService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProjectMemberController extends Controller
{
    protected $memberService;
    
    /**
     * Constructor - Inject service
     */
    public function __construct(ProjectMemberService $memberService)
    {
        $this->memberService = $memberService;
    }
    
    /**
     * Check if current user can manage members of a project
     * BATASAN BARU: Hanya leader yang bisa mengelola anggota, admin tidak bisa
     */
    private function canManageProjectMembers($projectId)
    {
        return $this->memberService->canManageMembers($projectId, Auth::id());
    }
    
    /**
     * Display project members management page
     */
    public function index($projectId)
    {
        // Check authorization - hanya leader yang bisa mengelola anggota
        if (!$this->canManageProjectMembers($projectId)) {
            abort(403, 'Hanya team leader yang dapat mengelola anggota project ini.');
        }
        
        $project = Project::findOrFail($projectId);
        
        // Menggunakan service untuk get members dengan statistics
        $data = $this->memberService->getProjectMembers($projectId);
        
        return view('projects.members.index', [
            'project' => $project,
            'members' => $data['members'],
            'statistics' => $data['statistics'],
        ]);
    }
    
    /**
     * Show form to add new member
     */
    public function create($projectId)
    {
        // Check authorization
        if (!$this->canManageProjectMembers($projectId)) {
            abort(403, 'Anda tidak memiliki izin untuk mengelola anggota project ini.');
        }
        
        $project = Project::findOrFail($projectId);
        
        return view('projects.members.create', compact('project'));
    }
    
    /**
     * Search users to add as project members
     */
    public function searchUsers(Request $request, $projectId)
    {
        // Check authorization
        if (!$this->canManageProjectMembers($projectId)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        $query = $request->get('q');
        
        // Get users that are not already members of this project
        $existingMemberIds = ProjectMember::where('project_id', $projectId)
            ->pluck('user_id')
            ->toArray();
        
        $users = User::where(function($q) use ($query) {
                $q->where('full_name', 'LIKE', "%{$query}%")
                  ->orWhere('username', 'LIKE', "%{$query}%")
                  ->orWhere('email', 'LIKE', "%{$query}%");
            })
            ->whereNotIn('user_id', $existingMemberIds)
            ->where('status', 'active')
            ->where('role', 'user') // HANYA user biasa (bukan admin atau leader)
            ->limit(10)
            ->get(['user_id', 'full_name', 'username', 'email', 'role']);

        Log::info('Search Members - Query: ' . $query);
        Log::info('Search Members - Found users count: ' . $users->count());
        Log::info('Search Members - Users roles: ' . $users->pluck('role')->implode(', '));

        return response()->json($users);
    }
    
    /**
     * Add new member to project
     * Menggunakan Form Request untuk validasi dan Service untuk business logic
     */
    public function store(StoreProjectMemberRequest $request, $projectId)
    {
        // Authorization sudah dihandle oleh Form Request
        // Validation sudah dihandle oleh Form Request
        
        try {
            // Menggunakan service untuk add member
            $member = $this->memberService->addMember(
                $projectId,
                $request->user_id,
                $request->role
            );
            
            $project = Project::findOrFail($projectId);
            $roleName = $request->role === 'developer' ? 'Developer' : 'Designer';
            
            // Return JSON untuk AJAX request
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => "Berhasil menambahkan {$member->user->full_name} sebagai {$roleName}.",
                    'member' => $member,
                ]);
            }
            
            // Return redirect untuk form request
            return redirect()->route('projects.members.index', $projectId)
                ->with('success', "Berhasil menambahkan {$member->user->full_name} sebagai {$roleName} ke project {$project->project_name}.");
                
        } catch (\Exception $e) {
            Log::error("Failed to add member: " . $e->getMessage());
            
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 422);
            }
            
            return back()
                ->withErrors(['error' => $e->getMessage()])
                ->withInput();
        }
    }
    
    /**
     * Update member role
     */
    public function update(Request $request, $projectId, $memberId)
    {
        // Check authorization
        if (!$this->canManageProjectMembers($projectId)) {
            abort(403, 'Anda tidak memiliki izin untuk mengelola anggota project ini.');
        }
        
        $request->validate([
            'role' => 'required|in:developer,designer',
        ]);
        
        try {
            // Menggunakan service untuk update role
            $member = $this->memberService->updateMemberRole($memberId, $request->role);
            
            $roleName = $request->role === 'developer' ? 'Developer' : 'Designer';
            
            return response()->json([
                'success' => true,
                'message' => "Role {$member->user->full_name} berhasil diubah menjadi {$roleName}.",
                'member' => $member
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }
    
    /**
     * Remove member from project
     * BATASAN BARU: Hanya leader yang dapat menghapus anggota, admin tidak bisa
     */
    public function destroy($projectId, $memberId)
    {
        // BATASAN: Hanya leader yang bisa menghapus anggota
        if (!$this->canManageProjectMembers($projectId)) {
            abort(403, 'Hanya team leader yang dapat menghapus anggota project ini.');
        }
        
        try {
            $member = ProjectMember::where('member_id', $memberId)
                ->where('project_id', $projectId)
                ->firstOrFail();
                
            $userName = $member->user->full_name;
            
            // Menggunakan service untuk remove member
            $this->memberService->removeMember($memberId);
            
            return response()->json([
                'success' => true,
                'message' => "{$userName} berhasil dikeluarkan dari project."
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }
    
    /**
     * Get available users for a project (API)
     */
    public function getAvailableUsers($projectId)
    {
        // Check authorization
        if (!$this->canManageProjectMembers($projectId)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        // Get users that are not already members of this project
        $existingMemberIds = ProjectMember::where('project_id', $projectId)
            ->pluck('user_id')
            ->toArray();
        
        $users = User::whereNotIn('user_id', $existingMemberIds)
            ->where('status', 'active')
            ->where('role', 'user') // Hanya user biasa, tidak termasuk admin dan leader
            ->get(['user_id', 'full_name', 'username', 'email', 'role']);
            
        return response()->json($users);
    }
}